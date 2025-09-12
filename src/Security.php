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
	public function handleUserSignup(string $user_login, string $user_email, string $key, array $meta): bool
	{
		Log::debug('Handling new user signup for ' . $user_login . ' - activating user and disabling signup email');

		$activationResult = wpmu_activate_signup($key);

		if (is_wp_error($activationResult)) {
			Log::debug('Activation failed: ' . $activationResult->get_error_message());

			return false;
		}

		Log::debug('User ' . $user_login . ' activated successfully');

		return false;
	}

	#[Filter('wpmu_welcome_user_notification')]
	public function handleWelcomeEmail(int $user_id, string $password, array $meta): bool
	{
		$disableWelcomeEmail = isset($_REQUEST['disable_welcome_email']) && $_REQUEST['disable_welcome_email'];

		if ($disableWelcomeEmail) {
			Log::debug('Welcome email disabled by checkbox for user ID: ' . $user_id);

			return false;
		}

		return true;
	}

	#[Filter('update_welcome_user_email')]
	public function customizeWelcomeEmailContent(string $welcome_email, int $user_id, string $password, array $meta): string
	{
		$disableWelcomeEmail = isset($_REQUEST['disable_welcome_email']) && $_REQUEST['disable_welcome_email'];

		if ($disableWelcomeEmail) {
			return $welcome_email;
		}

		$user = get_userdata($user_id);
		if (! $user) {
			return $welcome_email;
		}

		$resetKey = $this->getPasswordResetKey($user->user_login);
		if ('' === $resetKey) {
			Log::debug('Password reset key could not be generated for welcome email.');

			return $welcome_email;
		}

		$resetUrl = add_query_arg(
			[
				'action' => 'rp',
				'key' => $resetKey,
				'login' => rawurlencode($user->user_login),
			],
			wp_login_url()
		);

		return $this->composeEmail($user->user_login, $resetUrl);
	}

	#[Filter('update_welcome_user_subject')]
	public function customizeWelcomeEmailSubject(string $subject): string
	{
		$disableWelcomeEmail = isset($_REQUEST['disable_welcome_email']) && $_REQUEST['disable_welcome_email'];

		if ($disableWelcomeEmail) {
			return $subject;
		}

		$siteName = get_bloginfo('name');

		return sprintf('Welkom bij %s!', $siteName);
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

			return '';
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
