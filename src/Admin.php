<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yard\Hook\Filter;

class Admin
{
	#[Filter('get_user_option_admin_color')]
	public function forceModernColorScheme(): string
	{
		return 'modern';
	}

	#[Action('admin_init')]
	public function removeColorSchemePicker(): void
	{
		remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
	}
}
