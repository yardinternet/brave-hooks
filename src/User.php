<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

class User
{
	#[Filter('deleted_user')]
	public function deleteNetworkUser(int $id): void
	{
		if (count(get_blogs_of_user($id)) > 0) {
			return;
		}

		wpmu_delete_user($id);
	}

	#[Filter('network_site_url')]
	public function setLoginUrl(string $url, string $path, string $scheme): string
	{
		if (str_contains($path, 'wp-login.php') === false) {
			return $url;
		}

		return site_url($path, $scheme);
	}
}
