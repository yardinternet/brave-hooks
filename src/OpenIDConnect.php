<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

class OpenIDConnect
{
	#[Filter('allow_password_reset')]
	public function disablePasswordReset(bool $allowPasswordReset, int $userId): bool
	{
		if ($this->isOpenIDUser($userId)) {
			$allowPasswordReset = false;
		}

		return $allowPasswordReset;
	}

	#[Filter('show_password_fields')]
	public function hidePasswordFields(bool $show, \WP_User $user): bool
	{
		if ($this->isOpenIDUser($user->ID)) {
			$show = false;
		}

		return $show;
	}

	#[Filter('wp_authenticate_user')]
	public function disableLogin(\WP_User|\WP_Error $user, string $password): \WP_User|\WP_Error
	{
		if (is_a($user, \WP_User::class) && $this->isOpenIDUser($user->ID)) {
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

	protected function isOpenIDUser(int $userId): bool
	{
		return (bool) get_user_meta($userId, 'openid-connect-generic-subject-identity', true);
	}
}
