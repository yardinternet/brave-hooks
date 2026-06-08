<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Brave\Hooks\Traits\ParentPage;
use Yard\Hook\Action;
use Yard\Hook\Filter;

#[Plugin('wp-seopress-pro/seopress-pro.php')]
class SEOPress
{
	use ParentPage;

	#[Filter('seopress_pro_breadcrumbs_crumbs')]
	public function addBreadcrumb(array $breadcrumbs): array
	{
		if (! $this->hasParentPage(get_the_ID()) || is_search()) {
			return $breadcrumbs;
		}

		$parentIds = $this->getParentPagesIds(get_the_ID());
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

	#[Filter('seopress_capability')]
	public function dashboardOptions(string $cap, string $context): string
	{
		if (! is_admin()) {
			return $cap;
		}

		// Lower the main SEO menu cap from manage_options to edit_redirections so roles without manage_options can access it.
		if ('menu' === $context && 'manage_options' === $cap) {
			return 'edit_redirections';
		}

		return $cap;
	}

	/**
	 * Removes the SEOPress Dashboard and License submenu pages for users without manage_options.
	 *
	 * Both pages use seopress_capability('manage_options', 'menu') which our filter lowers to
	 * edit_redirections, making them visible to restricted users. They serve no purpose for those
	 * users, so we remove them from the menu here.
	 */
	#[Action('admin_menu', 99)]
	public function removeRestrictedMenuPages(): void
	{
		if (current_user_can('manage_options')) {
			return;
		}

		remove_submenu_page('seopress-option', 'seopress-option');
		remove_submenu_page('seopress-option', 'seopress-license');
	}

	/**
	 * Maps the edit_posts capability of the seopress_bot post type to edit_broken_links.
	 *
	 * seopress_bot uses capability_type => 'post', so WordPress checks edit_posts when a user
	 * navigates to edit.php?post_type=seopress_bot. Non-admin users with only edit_broken_links
	 * do not have edit_posts and are blocked. Remapping to edit_broken_links aligns the broken-links
	 * list access with the redirections list access level.
	 */
	#[Filter('register_post_type_args')]
	public function makeBotPostTypeAccessible(array $args, string $postType): array
	{
		if ('seopress_bot' !== $postType) {
			return $args;
		}

		$args['capabilities']['edit_posts'] = 'edit_broken_links';

		return $args;
	}

	/**
	 * Registers the seopress_404 post type on init for users without manage_options.
	 *
	 * SEOPress registers this post type on init for manage_options users, but falls back to admin_init
	 * for others (redirections.php). WordPress validates admin pages such as edit.php?post_type=seopress_404
	 * before admin_init fires, so for those users the post type does not exist yet and WordPress blocks
	 * access to the page. Registering unconditionally on init matches the timing SEOPress uses for
	 * privileged users; post_type_exists() prevents double registration.
	 */
	#[Action('init', 10)]
	public function registerRedirectionsPostType(): void
	{
		if (! is_admin() || current_user_can('manage_options')) {
			return;
		}

		if (post_type_exists('seopress_404') || ! function_exists('seopress_404_fn')) {
			return;
		}

		seopress_404_fn();
	}
}
