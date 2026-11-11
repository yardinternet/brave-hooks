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

	#[Action('admin_enqueue_scripts')]
	public function showMenuSeparatorLines(): void
	{
		wp_add_inline_style('admin-menu', '#adminmenu li.wp-menu-separator { height: 1px; margin: 8px 10px; background: rgba(240, 246, 252, .15); }');
	}

	#[Filter('login_display_language_dropdown')]
	public function disableLoginLanguageDropdown(): bool
	{
		return false;
	}

	/**
	 * Disable note mails to the post author
	 */
	#[Filter('pre_option_wp_notes_notify')]
	public function disableNoteNotifications(): string
	{
		return '0';
	}
}
