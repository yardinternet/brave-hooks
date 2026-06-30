<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

#[Plugin('yard-gutenberg/yard-gutenberg.php')]
class Gutenberg
{
	#[Filter('yard::gutenberg/allowed-blocks')]
	public function registerYardGutenbergBlocks(): array
	{
		return config('gutenberg.allowedBlocks', []);
	}

	#[Filter('yard::gutenberg/allowed-core-blocks')]
	public function registerCoreBlocks(array $initialAllowedBlocks): array
	{
		$additionalBlocks = collect(config('gutenberg.allowedCoreBlocks', []));

		$excludeBlocks = collect(config('gutenberg.excludedCoreBlocks', []));

		return collect($initialAllowedBlocks)
			->merge($additionalBlocks)
			->reject(fn ($block) => $excludeBlocks->contains($block))
			->values()
			->all();
	}

	#[Filter('yard::gutenberg/allowed-blocks-whitelisted-prefixes')]
	public function registerBlocksWhitelistedPrefixes(array $initialWhitelistedPrefixes): array
	{
		$allowedBlocksWhitelistedPrefixes = config('gutenberg.allowedBlocksWhitelistedPrefixes', []);

		return array_merge($initialWhitelistedPrefixes, $allowedBlocksWhitelistedPrefixes);
	}

	/**
	 * Restrict blocks for post types through the gutenberg.php config file.
	 */
	#[Filter('allowed_block_types_all')]
	public function restrictBlocksForPostTypes(bool|array $allowedBlockTypes, \WP_Block_Editor_Context $editorContext): bool|array
	{
		$postType = $editorContext?->post?->post_type;

		if (! $postType) {
			return $allowedBlockTypes;
		}

		$restriction = config("gutenberg.postTypeBlockRestrictions.{$postType}", []);

		if (! is_array($restriction) || [] === $restriction) {
			return $allowedBlockTypes;
		}

		$blockSet = isset($restriction['blockSet']) ? trim((string) $restriction['blockSet']) : null;

		if (! is_string($blockSet) || '' === $blockSet) {
			return $allowedBlockTypes;
		}

		$baseBlocks = config("gutenberg.blockSets.{$blockSet}", []);

		if (! is_array($baseBlocks)) {
			return $allowedBlockTypes;
		}

		$add = isset($restriction['add']) && is_array($restriction['add']) ? $restriction['add'] : [];
		$remove = isset($restriction['remove']) && is_array($restriction['remove']) ? $restriction['remove'] : [];

		$finalAllowedBlocks = array_values(array_unique(
			array_diff([...$baseBlocks, ...$add], $remove)
		));

		if ([] === $finalAllowedBlocks) {
			return $allowedBlockTypes;
		}

		// If previous filters have already restricted blocks via an array, intersect with our allowed set
		// so we don't re-allow blocks they intentionally disallowed.
		if (is_array($allowedBlockTypes)) {
			$intersected = array_values(array_intersect($allowedBlockTypes, $finalAllowedBlocks));

			return [] !== $intersected ? $intersected : $allowedBlockTypes;
		}

		return $allowedBlockTypes;
	}

	/**
	 * Adds wp-block-group-column-count-<columnCount> class to wp-block-group grid for more styling options.
	 *
	 * @param array<string, mixed> $block
	 */
	#[Filter('render_block_core/group')]
	public function addGroupColumnCountClass(string $blockContent, array $block): string
	{
		$columnCount = $block['attrs']['layout']['columnCount'] ?? null;
		$columnCount = filter_var($columnCount, FILTER_VALIDATE_INT);

		if (false === $columnCount || 1 > $columnCount) {
			return $blockContent;
		}

		$className = sprintf('wp-block-group-column-count-%d', $columnCount);

		if (str_contains($blockContent, $className)) {
			return $blockContent;
		}

		$processor = new \WP_HTML_Tag_Processor($blockContent);

		if (! $processor->next_tag(['class_name' => 'wp-block-group'])) {
			return $blockContent;
		}

		$processor->add_class($className);

		return $processor->get_updated_html() ?: $blockContent;
	}

	/**
	 * Adds a descriptive title to embed iframes for accessibility - "YouTube video: ..."
	 * Embeds that render no iframe (Twitter, Instagram, ...) are skipped.
	 */
	#[Filter('render_block_core/embed')]
	public function addEmbedIframeTitle(string $blockContent, array $block): string
	{
		$providerSlug = $block['attrs']['providerNameSlug'] ?? '';

		if (! is_string($providerSlug) || '' === $providerSlug) {
			return $blockContent;
		}

		$label = $this->embedProviderLabel($providerSlug);

		$type = $block['attrs']['type'] ?? '';
		if (is_string($type) && in_array($type, ['video', 'audio'], true)) {
			$label = sprintf('%s %s', $label, $type);
		}

		$processor = new \WP_HTML_Tag_Processor($blockContent);

		$updated = false;
		while ($processor->next_tag(['tag_name' => 'iframe'])) {
			$existingTitle = $processor->get_attribute('title');
			$existingTitle = is_string($existingTitle) ? trim($existingTitle) : '';

			// Skip if label is already applied
			if ('' !== $existingTitle && str_starts_with($existingTitle, $label)) {
				continue;
			}

			$title = '' !== $existingTitle
				? sprintf('%s: %s', $label, $existingTitle)
				: $label;

			$processor->set_attribute('title', $title);
			$updated = true;
		}

		if (! $updated) {
			return $blockContent;
		}

		return $processor->get_updated_html() ?: $blockContent;
	}

	private function embedProviderLabel(string $slug): string
	{
		$names = [
			'youtube' => 'YouTube',
			'vimeo' => 'Vimeo',
			'dailymotion' => 'Dailymotion',
			'ted' => 'TED',
			'videopress' => 'VideoPress',
			'wordpress-tv' => 'WordPress.tv',
			'soundcloud' => 'SoundCloud',
			'spotify' => 'Spotify',
			'flickr' => 'Flickr',
			'animoto' => 'Animoto',
			'kickstarter' => 'Kickstarter',
		];

		return $names[$slug] ?? ucwords(str_replace('-', ' ', $slug));
	}
}
