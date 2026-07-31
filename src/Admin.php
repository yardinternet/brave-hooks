<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Illuminate\Support\Facades\Log;
use Yard\Hook\Action;
use Yard\Hook\Filter;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class Admin
{
	#[Filter('get_user_option_admin_color')]
	public function forceModernColorScheme(): string
	{
		return 'modern';
	}

	#[Action('admin_init')]
	public function removeColorSchemePicker(): void
	{
		remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
	}

	#[Action('activate_relay/relay.php')]
	public function onRelayActivate(bool $networkWide): void
	{
		$monitor_api_key = env('SITES_MONITOR_API_KEY');
		if (!$monitor_api_key) {
			Log::debug('Unable to register sites to monitor, SITES_MONITOR_API_KEY not set.');
			return;
		}

		$api_key = relay_get_api_key();
		if (!$api_key) {
			$api_key = relay_generate_api_key();
		}

		$client = new GuzzleClient([
 			'connect_timeout' => 5,
 			'timeout' => 10,
 		]);
		$headers = [
			'X-Sites-Monitor-Key' => $monitor_api_key
		];
		$sites_monitor_base_url = env('SITES_MONITOR_URL', 'https://sites.yard.nl');
		$url = get_option('home') . '/';

		try {
			$response = json_decode(
				$client
				->request(
					'GET',
					$sites_monitor_base_url . '/api/v1/websites',
					[ 'headers' => $headers ]
				)
				->getBody()
				->getContents()
			);
		} catch (GuzzleException $e) {
			Log::debug('Failed to retrieve sites from monitor: ' . $e->getMessage());
			return;
		}

		$response = array_column($response, 'id', 'url');

		if (isset($response[$url])) {
			Log::debug('Site is already registered in sites monitor.');
			return;
		}

		if (is_multisite()) {
			$name = get_network_option(null, 'site_name');
		} else {
			$name = get_option('blogname');
		}

		$body = [
			'name' => $name,
			'url' => $url,
			'api_key' => $api_key,
		];

		try {
			$client->request(
				'POST',
				$sites_monitor_base_url . '/api/v1/websites',
				[
					'json' => $body,
					'headers' => $headers,
				]
			)
			->getBody()
			->getContents()
		} catch (GuzzleException $e) {
			Log::debug('Failed to add site to sites monitor: ' . $e->getMessage());
			return;
		}

		Log::debug('Relay activated');
	}

	#[Action('deactivate_relay/relay.php')]
	public function onRelayDeactivate(bool $networkWide): void
	{
		Log::debug('Relay deactivated');
	}
}
