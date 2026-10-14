<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yard\Hook\Filter;

class Authorization
{
	/**
	 * Matches a localized count such as "(1,234)", "(1&nbsp;234)" or "(1’234)".
	 * Any thousands separator is accepted, as long as the count starts and ends with a digit.
	 * No `u` flag on purpose: `\d` must stay ASCII-only, since `(int)` can't cast other Unicode digits.
	 */
	private const VIEW_COUNT_PATTERN = '/\((\d(?:[^()<>]*\d)?)\)/';

	/**
	 * @param array<int, string> $caps
	 *
	 * @return array<int, string>
	 */
	#[Filter('map_meta_cap', 1)]
	public function allowUnfilteredHtml(array $caps, string $cap, int $userId): array
	{
		if (! is_multisite()) {
			return $caps;
		}

		if ('unfiltered_html' === $cap && (user_can($userId, 'yard_unfiltered_html_multisite'))) {
			$caps = ['unfiltered_html'];
		}

		return $caps;
	}

	/**
	 * @param array<int, string> $caps
	 *
	 * @return array<int, string>
	 */
	#[Filter('map_meta_cap', 1)]
	public function editPrivacyPolicy(array $caps, string $cap, int $userId): array
	{
		if (! is_user_logged_in()) {
			return $caps;
		}

		if ('manage_privacy_options' === $cap && (user_can($userId, 'yard_edit_privacy_policy'))) {
			$manageCap = is_multisite() ? 'manage_network' : 'manage_options';
			$caps = array_diff($caps, [ $manageCap ]);
		}

		return $caps;
	}

	/**
	 * @param array<int, string> $caps
	 * @param array<int, mixed> $args
	 *
	 * @return array<int, string>
	 */
	#[Filter('map_meta_cap', 1)]
	public function addCapabilityForEditingAttachments(array $caps, string $cap, int $userId, array $args): array
	{
		if ('edit_post' !== $cap || ! isset($args[0])) {
			return $caps;
		}

		$post = get_post($args[0]);

		if (! $post instanceof \WP_Post || 'attachment' !== $post->post_type) {
			return $caps;
		}

		if ((int) $post->post_author !== $userId) {
			return $caps;
		}

		return ['yard_edit_attachments'];
	}

	/**
	 * @param array<int, string> $caps
	 * @param array<int, mixed> $args
	 *
	 * @return array<int, string>
	 */
	#[Filter('map_meta_cap', 1)]
	public function addCapabilityForDeletingAttachments(array $caps, string $cap, int $userId, array $args): array
	{
		if ('delete_post' !== $cap || ! isset($args[0])) {
			return $caps;
		}

		$post = get_post($args[0]);

		if (! $post instanceof \WP_Post || 'attachment' !== $post->post_type) {
			return $caps;
		}

		if ((int) $post->post_author !== $userId) {
			return $caps;
		}

		return ['yard_delete_attachments'];
	}

	#[Filter('show_admin_bar')]
	public function hideAdminBar(bool $show): bool
	{
		if (current_user_can('administrator')) {
			return $show;
		}

		if (current_user_can('yard_hide_admin_bar')) {
			return false;
		}

		return $show;
	}

	#[Filter('login_redirect')]
	public function filterLoginRedirect(string $redirectTo, string $requestedRedirectTo, \WP_User|\WP_Error $user): string
	{
		if (is_wp_error($user) || user_can($user, 'administrator')) {
			return $redirectTo;
		}

		if (user_can($user, 'yard_redirect_home_after_login')) {
			return home_url();
		}

		return $redirectTo;
	}

	#[Action('admin_init')]
	public function preventAdminAccess(): void
	{
		if (current_user_can('administrator')) {
			return;
		}

		if (current_user_can('yard_prevent_admin_access')) {
			wp_logout();
			wp_redirect(admin_url());
			exit;
		}
	}

	#[Action('pre_get_users')]
	public function hideAdminsFromNonAdmins(\WP_User_Query $args): void
	{
		if (current_user_can('administrator')) {
			return;
		}

		if (! \is_admin()) {
			return;
		}

		if (! function_exists('get_current_screen')) {
			return;
		}

		if (get_current_screen()?->id !== 'users') {
			return;
		}

		$args->query_vars['role__not_in'] = 'administrator';
	}

	/**
	 * @param array<string, string> $views
	 *
	 * @return array<string, string>
	 */
	#[Filter('views_users')]
	public function hideAdminCountFromNonAdmins(array $views): array
	{
		if (current_user_can('administrator') || ! isset($views['administrator'], $views['all'])) {
			return $views;
		}

		$administratorView = $views['administrator'];

		unset($views['administrator']);

		$adminCount = $this->parseViewCount($administratorView);
		$allUsersCount = $this->parseViewCount($views['all']);

		if (null === $adminCount || null === $allUsersCount) {
			return $views;
		}

		$newAllUsersCount = max(0, $allUsersCount - $adminCount);

		$views['all'] = preg_replace(self::VIEW_COUNT_PATTERN, '(' . number_format_i18n($newAllUsersCount) . ')', $views['all'], 1) ?? $views['all'];

		return $views;
	}

	private function parseViewCount(string $view): ?int
	{
		if (1 !== preg_match(self::VIEW_COUNT_PATTERN, $view, $matches)) {
			return null;
		}

		$digits = preg_replace('/\D/', '', html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

		return null === $digits || '' === $digits ? null : (int) $digits;
	}
}
