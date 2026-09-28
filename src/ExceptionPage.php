<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Throwable;
use Yard\Hook\Action;

class ExceptionPage
{
	/**
	 * Ignition's page carries inline scripts without a nonce, so the CSP that was sent before the
	 * exception blocks them and leaves a blank page. Acorn registers its exception handler while
	 * loading the theme, hence the late priority.
	 */
	#[Action('after_setup_theme', PHP_INT_MAX)]
	public function dropCspHeader(): void
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
