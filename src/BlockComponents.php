<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Illuminate\Support\Facades\Blade;
use WP_Block_Type_Registry;
use Yard\Brave\Hooks\Components\BlockComponent;
use Yard\Hook\Action;

class BlockComponents
{
	/**
	 * Make every dynamic block useable as <x-block-{namespace}-{name}>:
	 * <x-block-theme-card>, <x-block-core-search>, <x-block-yard-icon>
	 *
	 * Static save.js blocks are skipped because render_block() can't rebuild their saved markup from attributes.
	 * Blocks relying on block context won't work: slots render before their parent block
	 */
	#[Action('init', PHP_INT_MAX)]
	public function registerBlockComponents(): void
	{
		foreach (WP_Block_Type_Registry::get_instance()->get_all_registered() as $blockType) {
			if (! $blockType->is_dynamic()) {
				continue;
			}

			$alias = 'block-' . str_replace('/', '-', $blockType->name);

			BlockComponent::$aliases[$alias] = $blockType->name;
			Blade::component(BlockComponent::class, $alias);
		}
	}
}
