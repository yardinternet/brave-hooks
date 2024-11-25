<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use DOMDocument;
use Yard\Brave\Hooks\Traits\ParentPage;
use Yard\Hook\Action;
use Yard\Hook\Filter;

class Theme
{
	use ParentPage;

	#[Filter('excerpt_more')]
	public function excerptMore(): string
	{
		return '...';
	}

	#[Filter('excerpt_length')]
	public function excerptLength(): int
	{
		return 20;
	}

	#[Action('init')]
	public function enableExcerptForPage(): void
	{
		add_post_type_support('page', 'excerpt');
	}

	#[Action('body_class')]
	public function bodyClass(array $classes): array
	{
		if (get_page_template_slug()) {
			$classes[] = basename(get_page_template_slug(), '.blade.php');
		}

		if (has_nav_menu('top_bar_navigation')) {
			$classes[] = 'has-top-bar';
		}

		return $classes;
	}

	#[Filter('admin_body_class')]
	public function adminBodyClass(string $classes): string
	{
		if (get_page_template_slug()) {
			$classes .= ' ' . basename(get_page_template_slug(), '.blade.php');
		}

		return $classes;
	}

	#[Filter('nav_menu_css_class')]
	public function addActiveMenuClass(array $classes, \WP_Post $item): array
	{
		if (! is_singular() || 'page' !== $item->object) {
			return $classes;
		}

		$parentIds = $this->getParentIds(get_the_ID());

		if (count($parentIds) === 0) {
			return $classes;
		}

		if (in_array($item->object_id, $parentIds)) {
			$classes = [...$classes, 'current-menu-item', 'active'];
		}

		return $classes;
	}

	#[Action('init')]
	public function registerFeaturedImageFocalPoint(): void
	{
		register_post_meta('', 'featured_image_focal_point', [
			'type' => 'object',
			'description' => __('Focuspunt van de uitgelichte afbeelding', 'sage'),
			'single' => true,
			'show_in_rest' => [
				'schema' => [
					'type' => 'object',
					'properties' => [
						'x' => [
							'type' => 'number',
							'default' => 0.5,
						],
						'y' => [
							'type' => 'number',
							'default' => 0.5,
						],
					],
				],
			],
		]);
	}

	#[Action('post_thumbnail_html')]
	public function addFocalPointToFeaturedImageHtml(string $html, int $postID): string
	{
		if (strlen($html) === 0) {
			return $html;
		}

		$focalPoint = get_post_meta($postID, 'featured_image_focal_point', true);

		if (! is_array($focalPoint) || ! isset($focalPoint['x']) || ! isset($focalPoint['y'])) {
			return $html;
		}

		$objectPosition = sprintf('object-position: %d%% %d%%;', $focalPoint['x'] * 100, $focalPoint['y'] * 100);

		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		if (! $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
			libxml_clear_errors();

			return $html; // Return original HTML if loading fails
		}
		libxml_clear_errors();

		$images = $doc->getElementsByTagName('img');

		foreach ($images as $img) {
			$style = $img->getAttribute('style');
			$img->setAttribute('style', $style . ' ' . $objectPosition);
		}

		return $doc->saveHTML();
	}
}
