<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Brave\Hooks\Traits\ParentPage;
use Yard\Hook\Filter;

#[Plugin('wp-seopress-pro/seopress-pro.php')]
class SEOPress
{
	use ParentPage;

	/**
	 * @param array<int, array<int, mixed>> $breadcrumbs
	 *
	 * @return array<int, array<int, mixed>>
	 */
	#[Filter('seopress_pro_breadcrumbs_crumbs')]
	public function addBreadcrumb(array $breadcrumbs): array
	{
		$postId = get_the_ID();

		if (false === $postId || is_search()) {
			return $breadcrumbs;
		}

		$parentIds = $this->getParentPagesIds($postId);
		if (empty($parentIds)) {
			return $breadcrumbs;
		}

		$parentBreadcrumbs = array_map(
			fn (int $id): array => [get_the_title($id), get_the_permalink($id)],
			array_reverse($parentIds)
		);
		array_splice(
			$breadcrumbs,
			1,
			0,
			$parentBreadcrumbs
		);

		return $breadcrumbs;
	}

	#[Filter('seopress_capability', 10000)]
	public function dashboardOptions(string $cap, ?string $context = null): string
	{
		if ('menu' === $context || 'bot' === $context) {
			$cap = 'edit_posts';
		}

		return $cap;
	}

	/**
	 * Default meta templates when no template is set in SEOPress.
	 *
	 * Archives and taxonomies are always noindex and nofollow and taxonomies hide the SEO metabox, overriding SEOPress settings.
	 *
	 * @return array<string, mixed>
	 */
	#[Filter('option_seopress_titles_option_name')]
	public function defaultMetaTemplates(mixed $options): array
	{
		$options = is_array($options) ? $options : [];
		$postTypes = get_post_types(['public' => true, '_builtin' => false]);
		$taxonomies = get_taxonomies(['public' => true]);

		$options = $this->applyTemplates(
			$options,
			'seopress_titles_single_titles',
			$postTypes,
			[
				'title' => '%%post_title%% %%sep%% %%sitetitle%%',
				'description' => '%%post_excerpt%%',
			]
		);

		$options = $this->applyTemplates(
			$options,
			'seopress_titles_archive_titles',
			$postTypes,
			['title' => '%%cpt_plural%% %%sep%% %%sitetitle%%'],
			['noindex' => '1', 'nofollow' => '1']
		);

		$options = $this->applyTemplates(
			$options,
			'seopress_titles_tax_titles',
			$taxonomies,
			[
				'title' => '%%term_title%% %%sep%% %%sitetitle%%',
				'description' => '%%term_description%%',
			],
			['noindex' => '1', 'nofollow' => '1', 'enable' => '1']
		);

		return $options;
	}

	/**
	 * @param array<string, mixed> $options
	 * @param array<string> $objects
	 * @param array<string, string> $defaults
	 * @param array<string, string> $forced
	 *
	 * @return array<string, mixed>
	 */
	private function applyTemplates(array $options, string $group, array $objects, array $defaults, array $forced = []): array
	{
		foreach ($objects as $object) {
			$settings = $options[$group][$object] ?? [];
			$settings = $this->fillDefaults($settings, $defaults);
			$options[$group][$object] = $this->forceValues($settings, $forced);
		}

		return $options;
	}

	/**
	 * @param array<string, mixed> $settings
	 * @param array<string, string> $defaults
	 *
	 * @return array<string, mixed>
	 */
	private function fillDefaults(array $settings, array $defaults): array
	{
		foreach ($defaults as $key => $value) {
			if (empty($settings[$key])) {
				$settings[$key] = $value;
			}
		}

		return $settings;
	}

	/**
	 * @param array<string, mixed> $settings
	 * @param array<string, string> $forced
	 *
	 * @return array<string, mixed>
	 */
	private function forceValues(array $settings, array $forced): array
	{
		foreach ($forced as $key => $value) {
			$settings[$key] = $value;
		}

		return $settings;
	}
}
