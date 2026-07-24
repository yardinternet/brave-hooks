<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use WP_User;
use Yard\Hook\Action;
use Yard\Hook\Filter;

#[Plugin('onelogin-saml-sso/onelogin_saml.php')]
class OneloginSSO
{
	/**
	 * @param array<string, array<int, string>> $attrs
	 */
	#[Action('onelogin_saml_attrs')]
	public function setOneloginUserMeta(array $attrs, WP_User $user, int $userId, bool $newUser): void
	{
		update_user_meta($userId, 'onelogin_saml_sso_user', true);
	}

	#[Filter('allow_password_reset')]
	public function disablePasswordReset(bool $allowPasswordReset, int $userId): bool
	{
		if ($this->isOneloginSSOUser($userId)) {
			$allowPasswordReset = false;
		}

		return $allowPasswordReset;
	}

	#[Filter('show_password_fields')]
	public function hidePasswordFields(bool $show, \WP_User $user): bool
	{
		if ($this->isOneloginSSOUser($user->ID)) {
			$show = false;
		}

		return $show;
	}

	#[Filter('wp_authenticate_user')]
	public function disableLogin(\WP_User|\WP_Error $user, string $password): \WP_User|\WP_Error
	{
		if (is_a($user, \WP_User::class) && $this->isOneloginSSOUser($user->ID)) {
			$user = new \WP_Error('password_login_disabled', __('Inloggen met wachtwoord is niet toegestaan voor deze gebruiker'));
		}

		return $user;
	}

	protected function isOneloginSSOUser(int $userId): bool
	{
		return (bool) get_user_meta($userId, 'onelogin_saml_sso_user', true);
	}
}
