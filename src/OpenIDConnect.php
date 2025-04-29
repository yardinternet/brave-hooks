<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

class OpenIDConnect
{
	#[Filter('site_url')]
	public function setCorrectRedirectUrl(string $url): string
	{
		//https://github.com/oidc-wp/openid-connect-generic/pull/341
		return str_replace('/wp/openid-connect-authorize', '/openid-connect-authorize', $url);
	}

	#[Filter('openid-connect-generic-alter-user-claim')]
	public function alterUserClaim(array $claim): array
	{
		if (isset($claim['emails']) && wp_is_numeric_array($claim['emails'])) {
			$claim['email'] = $claim['emails'][0];
		}

		return $claim;
	}

	#[Filter('openid-connect-generic-auth-url')]
	public function setAuthUrl(string $url): string
	{
		return add_query_arg(
			'prompt',
			'login',
			$url
		);
	}
}
