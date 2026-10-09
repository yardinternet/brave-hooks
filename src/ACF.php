<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yard\Hook\Filter;

#[Plugin('advanced-custom-fields-pro/acf.php')]
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

	#[Filter('acf/settings/load_json')]
	public function loadJson(array $paths): array
	{
		$paths[] = get_template_directory() . '/acf-json';

		return $paths;
	}

	#[Filter('acf/settings/save_json')]
	public function saveJson(string $path): string
	{
		return get_template_directory() . '/acf-json';
	}

	/**
	 * Duplicate Post copies meta straight into the database, past ACF, so the
	 * relations a copy inherits never reach the other side.
	 */
	#[Action('duplicate_post_after_duplicated', 20)]
	public function syncBidirectionalRelationsOfDuplicate(int $postId): void
	{
		collect(get_field_objects($postId, false) ?: [])
			->filter(fn (array $field): bool => ! empty($field['bidirectional']) && ! empty($field['value']))
			->each(function (array $field) use ($postId): void {
				delete_field($field['key'], $postId);
				update_field($field['key'], $field['value'], $postId);
			});
	}
}
