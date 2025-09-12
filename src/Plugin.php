<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Plugin
{
	public function __construct(
		public string $plugin,
	) {
	}

	public function isActive(): bool
	{
		if (! function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		return is_plugin_active($this->plugin);
	}
}
