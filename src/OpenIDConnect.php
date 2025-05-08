<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

class OpenIDConnect
{
	#[Filter('allow_password_reset')]
	public function disablePasswordReset(bool $allowPasswordReset, int $userId): bool
	{
		if (get_user_meta($userId, 'openid-connect-generic-subject-identity', true)) {
			$allowPasswordReset = false;
		}

		return $allowPasswordReset;
	}

	#[Filter('wp_authenticate_user')]
	public function disableLogin(\WP_User|\WP_Error $user, string $password): \WP_User|\WP_Error
	{
		if (is_a($user, \WP_User::class) && get_user_meta($user->ID, 'openid-connect-generic-subject-identity', true)) {
			$user = new \WP_Error('password_login_disabled', __('Inloggen met wachtwoord is niet toegestaan voor deze gebruiker'));
		}

		return $user;
	}

	#[Filter('site_url')]
	public function setCorrectRedirectUrl(string $url): string
	{
		//https://github.com/oidc-wp/openid-connect-generic/pull/341
		return str_replace('/wp/openid-connect-authorize', '/openid-connect-authorize', $url);
	}

	#[Filter('openid-connect-generic-client-redirect-to')]
	public function setRedirectTo(): string
	{
		$redirectTo = home_url();
		$referer = wp_get_referer();
		if (false !== $referer) {
			$redirectTo = $referer;
		}

		return $redirectTo;
	}
}
