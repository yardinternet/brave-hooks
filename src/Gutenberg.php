<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

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
}
