<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Illuminate\Support\Facades\Log;
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

	#[Filter('wpmu_signup_user_notification')]
	public function newUserCreation(string $userLogin, string $userEmail, string $key): void
	{
		$activationResult = wpmu_activate_signup($key);

		if (is_wp_error($activationResult)) {
			Log::debug($activationResult->get_error_message());

			return;
		}

		$siteName = get_bloginfo('name');
		$resetKey = $this->getPasswordResetKey($userLogin);

		if ('' === $resetKey) {
			Log::debug('Password reset key could not be generated.');

			return;
		}

		$resetUrl = wp_login_url("?action=rp&key=$resetKey&login=$userLogin");

		$subject = sprintf(
			'Welkom bij %s, %s!',
			$siteName,
			$userLogin
		);

		$message = $this->composeEmail($userLogin, $resetUrl);

		wp_mail($userEmail, $subject, $message);
	}

	#[Filter('wpmu_welcome_user_notification')]
	public function disableWelcomeEmail(): bool
	{
		return false;
	}

	private function getPasswordResetKey(string $userLogin): string
	{
		$user = get_user_by('login', $userLogin);

		if (! $user instanceof \WP_User) {
			Log::debug('No user was found for login: ' . $userLogin);

			return '';
		}

		$resetKey = get_password_reset_key($user);

		if (is_wp_error($resetKey)) {
			Log::debug($resetKey->get_error_message());
		}

		return $resetKey;
	}

	private function composeEmail(string $userLogin, string $resetUrl): string
	{
		return <<<EOT
			Welkom $userLogin,

			Je nieuwe account is succesvol geactiveerd.

			Ga naar: $resetUrl
			Om een nieuw wachtwoord aan te maken en in te loggen.

			Vanwege veiligheid is de bovenstaande link maar een korte tijd geldig.

			Bedankt!
			EOT;
	}
}
