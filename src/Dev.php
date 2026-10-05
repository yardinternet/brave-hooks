<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use ErrorException;
use Throwable;
use Yard\Hook\Action;
use Yard\Hook\Filter;

class Dev
{
	/**
	 * Acorn turns every notice and warning into an exception, which takes the page down.
	 * Opt in by defining DEV_NON_FATAL_ERROR_LEVELS in config/environments/development.php.
	 *
	 * @see \Roots\Acorn\Bootstrap\HandleExceptions::handleError()
	 */
	#[Filter('acorn/throw_error_exception')]
	public function keepDiagnosticsNonFatal(bool $throw, Throwable $error): bool
	{
		if ('development' !== wp_get_environment_type() || ! defined('DEV_NON_FATAL_ERROR_LEVELS')) {
			return $throw;
		}

		if (! $error instanceof ErrorException) {
			return $throw;
		}

		return (bool) ($error->getSeverity() & DEV_NON_FATAL_ERROR_LEVELS) ? false : $throw;
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
