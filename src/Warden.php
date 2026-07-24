<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

#[Plugin('yard-warden/yard-warden.php')]
class Warden
{
	/**
	 * @param array<string> $codes
	 *
	 * @return array<string>
	 */
	#[Filter('yard::warden/login/leaky-error-codes')]
	public function addLeakyErrorCodes(array $codes): array
	{
		$codes[] = 'password_login_disabled';

		return $codes;
	}
}
