<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Throwable;
use Yard\Hook\Action;

class ErrorHandling
{
	public const NON_FATAL_LEVELS = E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING;

	/**
	 * Acorn turns notices and warnings into exceptions and sends deprecations to a log channel
	 * that is null by default. Returning false hands these levels back to PHP, so WordPress
	 * displays and logs them like it does before Acorn boots.
	 *
	 * @see \Roots\Acorn\Bootstrap\HandleExceptions::handleError()
	 */
	#[Action('after_setup_theme', PHP_INT_MAX)]
	public function keepDiagnosticsNonFatal(): void
	{
		if ('development' !== wp_get_environment_type()) {
			return;
		}

		$previous = set_error_handler(null);

		if (! is_callable($previous)) {
			return;
		}

		set_error_handler(static fn (int $level, string $message, string $file = '', int $line = 0): mixed => ($level & self::NON_FATAL_LEVELS) ? false : $previous($level, $message, $file, $line));
	}

	/**
	 * Ignition's page carries inline scripts without a nonce, so a CSP leaves it blank.
	 */
	#[Action('after_setup_theme', PHP_INT_MAX)]
	public function dropCspOnExceptionPage(): void
	{
		if ('development' !== wp_get_environment_type()) {
			return;
		}

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
}
