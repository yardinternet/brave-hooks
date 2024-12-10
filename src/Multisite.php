<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

class Multisite
{
	/**
	 * Ensure that home URL does not contain the /wp subdirectory.
	 */
	#[Filter('option_home')]
	public function fixHomeURL(string $value): string
	{
		if (! is_multisite()) {
			return $value;
		}

		if (str_ends_with($value, '/wp')) {
			$value = substr($value, 0, -3);
		}

		return $value;
	}

	/**
	 * Ensure that site URL contains the /wp subdirectory.
	 */
	#[Filter('option_siteurl')]
	public function fixSiteURL(string $url): string
	{
		if (! is_multisite()) {
			return $url;
		}

		if (! str_ends_with($url, '/wp') && (is_main_site() || is_subdomain_install())) {
			$url .= '/wp';
		}

		return $url;
	}

	/**
	 * Ensure that the network site URL contains the /wp subdirectory.
	 */
	#[Filter('network_site_url')]
	public function fixNetworkSiteURL(string $url, string $path, string $scheme): string
	{
		if (! is_multisite()) {
			return $url;
		}

		$path = ltrim($path, '/');
		$url = substr($url, 0, strlen($url) - strlen($path));

		if (! str_ends_with($url, 'wp/')) {
			$url .= 'wp/';
		}

		return $url . $path;
	}
}
