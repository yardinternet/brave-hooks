<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Spatie\Csp\PolicyFactory;
use Yard\Hook\Action;
use Yard\Hook\Filter;

class Security
{
	#[Action('send_headers')]
	public function sendHeaders(): void
	{
		// Force client-side TLS (Transport Layer Security) redirection.
		header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');

		// Disable content sniffing, since it's an attack vector.
		header('X-Content-Type-Options: nosniff');

		// Prevent clickjacking
		header('X-Frame-Options: SAMEORIGIN');

		// Set a strict Referrer Policy to mitigate information leakage.
		header('Referrer-Policy: strict-origin-when-cross-origin');

		// Disable unused device permissions
		header('Permissions-Policy: accelerometer=(),autoplay=(self),camera=(),display-capture=(),encrypted-media=(),fullscreen=(*),geolocation=(),gyroscope=(),magnetometer=(),microphone=(),midi=(),payment=(),picture-in-picture=(),publickey-credentials-get=(),screen-wake-lock=(),sync-xhr=(self),usb=(),xr-spatial-tracking=()');
	}

	#[Action('send_headers')]
	public function sendScpHeader(): void
	{
		if (! config('csp.enabled')) {
			return;
		}

		$policy = PolicyFactory::create(config('csp.policy'));

		header(sprintf('%s: %s', $policy->prepareHeader(), $policy->__toString()), true);
	}

	#[Filter('wp_script_attributes')]
	#[Filter('wp_inline_script_attributes')]
	public function addScriptNonce(array $attributes): array
	{
		$attributes['nonce'] = csp_nonce();

		return $attributes;
	}
}
