<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use ErrorException;
use Throwable;
use Yard\Hook\Filter;

class ErrorHandling
{
	public const NON_FATAL_LEVELS = E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING;

	/**
	 * Acorn turns every notice and warning into an exception, which takes the page down.
	 *
	 * @see \Roots\Acorn\Bootstrap\HandleExceptions::handleError()
	 */
	#[Filter('acorn/throw_error_exception')]
	public function keepDiagnosticsNonFatal(bool $throw, Throwable $error): bool
	{
		if ('development' !== wp_get_environment_type() || ! $error instanceof ErrorException) {
			return $throw;
		}

		return 0 === ($error->getSeverity() & self::NON_FATAL_LEVELS) ? $throw : false;
	}
}
