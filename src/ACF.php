<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

class ACF
{
	#[Filter('acf/settings/enable_post_types')]
	public function enablePostTypes(): bool
	{
		return false;
	}

	#[Filter('acf/fields/google_map/api')]
	public function my_acf_google_map_api(array $api): array
	{
		$api['key'] = env('GOOGLE_MAPS_API_KEY', '');

		return $api;
	}
}
