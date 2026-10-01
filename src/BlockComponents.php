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
	 * Make every registered block useable as <x-block-{namespace}-{name}>:
	 * <x-block-theme-card>, <x-block-core-search>, <x-block-yard-icon>
	 */
	#[Action('init', PHP_INT_MAX)]
	public function registerBlockComponents(): void
	{
		foreach (array_keys(WP_Block_Type_Registry::get_instance()->get_all_registered()) as $blockName) {
			$alias = 'block-' . str_replace('/', '-', $blockName);

			BlockComponent::$aliases[$alias] = $blockName;
			Blade::component(BlockComponent::class, $alias);
		}
	}
}
