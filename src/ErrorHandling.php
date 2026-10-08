<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use ErrorException;
use Throwable;
use Yard\Hook\Action;

class ErrorHandling
{
	private const NON_FATAL_LEVELS = E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING;
	private const FATAL_LEVELS = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE;

	/**
	 * Acorn installs its error and exception handlers while the theme loads, so they exist by now.
	 */
	#[Action('after_setup_theme', PHP_INT_MAX)]
	public function wrapAcornHandlers(): void
	{
		if ('development' !== wp_get_environment_type()) {
			return;
		}

		$this->passDiagnosticsToPhp();
		$this->dropCspOnExceptionPage();
		$this->showFatalsWithoutAcorn();
	}

	/**
	 * Acorn throws notices and warnings and swallows deprecations; send them to PHP, or all to Ignition with ERROR_ALWAYS_IGNITION.
	 */
	private function passDiagnosticsToPhp(): void
	{
		if (! config('app.debug')) {
			return;
		}

		$previous = set_error_handler(null);

		if (! is_callable($previous)) {
			return;
		}

		$alwaysIgnition = (bool) env('ERROR_ALWAYS_IGNITION', false);

		set_error_handler(static function (int $level, string $message, string $file = '', int $line = 0) use ($previous, $alwaysIgnition): mixed {
			if (0 === ($level & self::NON_FATAL_LEVELS)) {
				return $previous($level, $message, $file, $line);
			}

			if ($alwaysIgnition && 0 !== (error_reporting() & $level)) {
				throw new ErrorException($message, 0, $level, $file, $line);
			}

			return false;
		});
	}

	/**
	 * Ignition's page carries inline scripts without a nonce, so a CSP leaves it blank.
	 */
	private function dropCspOnExceptionPage(): void
	{
		$previous = set_exception_handler(null);

		if (! is_callable($previous)) {
			return;
		}

		set_exception_handler(static function (Throwable $throwable) use ($previous): void {
			if (! headers_sent()) {
				header_remove('Content-Security-Policy');
			}

			$previous($throwable);
		});
	}

	/**
	 * Acorn only catches fatals while app.debug is on; without it they end as an empty 500.
	 */
	private function showFatalsWithoutAcorn(): void
	{
		if (config('app.debug')) {
			return;
		}

		register_shutdown_function(static function (): void {
			$error = error_get_last();

			if (null === $error || 0 === ($error['type'] & self::FATAL_LEVELS)) {
				return;
			}

			$handler = set_exception_handler(null);

			if (is_callable($handler)) {
				$handler(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
			}
		});
	}
}
