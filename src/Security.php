<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yard\Hook\Filter;

class Security
{
	#[Action('send_headers')]
	public function sendHeaders(): void
	{
		// Set a strict Referrer Policy to mitigate information leakage.
		header('Referrer-Policy: strict-origin-when-cross-origin');

		// Disable unused device permissions
		header('Permissions-Policy: accelerometer=(),autoplay=(self),camera=(),display-capture=(),encrypted-media=(),fullscreen=(*),geolocation=(),gyroscope=(),magnetometer=(),microphone=(),midi=(),payment=(),picture-in-picture=(),publickey-credentials-get=(),screen-wake-lock=(),sync-xhr=(self),usb=(),xr-spatial-tracking=()');
	}

	/**
	 * @param array<string, string|bool> $attributes
	 *
	 * @return array<string, string|bool>
	 */
	#[Filter('wp_script_attributes')]
	#[Filter('wp_inline_script_attributes')]
	public function addScriptNonce(array $attributes): array
	{
		$attributes['nonce'] = csp_nonce();

		return $attributes;
	}

	#[Filter('acorn/powered_by')]
	public function removeAcornPoweredBy(): bool
	{
		return false;
	}
}
