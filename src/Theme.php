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

	/**
	 * Disable WordPress from changing smilies (also known as smileys) into emojis.
	 *
	 * @see https://developer.wordpress.org/reference/functions/convert_smilies/
	 */
	#[Action('wp_loaded')]
	public function removeConvertSmiliesFilter(): void
	{
		remove_filter('the_content', 'convert_smilies', 20);
	}

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

		if (isset($_COOKIE['a11y-toolbar-contrast']) && 'true' === $_COOKIE['a11y-toolbar-contrast']) {
			$classes[] = 'a11y-toolbar--contrast';
		}

		if (isset($_COOKIE['a11y-toolbar-text-size']) && 'true' === $_COOKIE['a11y-toolbar-text-size']) {
			$classes[] = 'a11y-toolbar--text-size';
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

	/**
	 * Adds sr-only span to target="_blank" links and replaces <strong> and <em> tags with <span> for accessibility reasons.
	 */
	#[Filter('the_content')]
	public function filterAccessibilityProblematicHtmlTags(string $content): string
	{
		if (empty($content)) {
			return $content;
		}

		$doc = new DOMDocument();
		libxml_use_internal_errors(true);

		// Wrap content in a dummy <div> for fragment safety
		$html = '<div>' . $content . '</div>';

		// Set the encoding to UTF-8 to prevent encoding issues
		$html = '<?xml encoding="UTF-8">' . $html;

		if (! $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
			libxml_clear_errors();

			return $content; // Return original HTML if loading fails
		}
		libxml_clear_errors();

		// Add sr-only span to target="_blank" links
		$links = $doc->getElementsByTagName('a');
		foreach ($links as $link) {
			if (! $link->hasAttribute('target') || $link->getAttribute('target') !== '_blank') {
				continue; // Skip links without target="_blank"
			}
			$existingSpan = $link->getElementsByTagName('span');
			foreach ($existingSpan as $span) {
				if ($span->getAttribute('class') === 'sr-only') {
					continue 2; // Skip if an sr-only span already exists
				}
			}

			try {
				$srOnlySpan = $doc->createElement('span', ' (opent in nieuw tabblad)');
			} catch (\DOMException $e) {
				continue;
			}
			$srOnlySpan->setAttribute('class', 'sr-only');
			$link->appendChild($srOnlySpan);
		}

		// Helper to replace tags with <span class="...">
		$replaceTagWithSpan = function (string $tag, string $class) use ($doc) {
			$nodes = [];
			$elements = $doc->getElementsByTagName($tag);
			foreach ($elements as $el) {
				$nodes[] = $el;
			}
			foreach ($nodes as $el) {
				$span = $doc->createElement('span');
				$span->setAttribute('class', $class);
				while ($el->firstChild) {
					$span->appendChild($el->firstChild);
				}
				$el->parentNode->replaceChild($span, $el);
			}
		};

		$replaceTagWithSpan('strong', 'brave-hooks-strong');
		$replaceTagWithSpan('em', 'brave-hooks-em');

		$newContent = $this->removeOuterDiv($doc);

		return '' === $newContent ? $content : $newContent;
	}

	/**
	 * Remove the outer div that was added for fragment safety.
	 */
	private function removeOuterDiv(DOMDocument $doc): string
	{
		$body = $doc->getElementsByTagName('div')->item(0);

		if (null === $body) {
			return '';
		}

		$newContent = '';

		foreach ($body->childNodes as $child) {
			$newContent .= $doc->saveHTML($child) ?: '';
		}

		return $newContent;
	}
}
