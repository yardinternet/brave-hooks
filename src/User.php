<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yard\Hook\Filter;

class User
{
	public const DELETE_NETWORK_USER_CRON_HOOK = 'delete_network_user';

	#[Action('remove_user_from_blog')]
	public function scheduleDeleteNetworkUser(int $userID): void
	{
		\wp_schedule_single_event(time() + 5, self::DELETE_NETWORK_USER_CRON_HOOK, [$userID]);
	}

	#[Action('remove_user_from_blog')]
	public function handleRemoveUserFromBlog(int $userId, int $blogId): void
	{
		if (! is_admin() || get_current_blog_id() !== $blogId) {
			return;
		}

		$userPosts = get_posts([
			'author' => $userId,
			'post_type' => 'any',
			'posts_per_page' => 1,
			'fields' => 'ids',
		]);

		if ($userPosts) {
			wp_safe_redirect(admin_url('users.php?action=remove&user=' . $userId));
			exit;
		}
	}

	#[Action('all_admin_notices')]
	public function showReassignPostsNotice(): void
	{
		if (! $this->shouldDisplay()) {
			return;
		}

		$userId = (int) sanitize_text_field($_GET['user']);
		$userPosts = get_posts([
			'author' => $userId,
			'post_type' => 'any',
			'posts_per_page' => 1,
			'fields' => 'ids',
		]);

		if ($userPosts && empty($_POST['reassign_user'])) {
			$users = get_users(['exclude' => [$userId]]);
			$this->renderReassignUserSelect($users, $userId);
		}
	}

	#[Action('admin_init')]
	public function handleAdminInit(): void
	{
		if (! $this->shouldDisplay()) {
			return;
		}

		if (! isset($_POST['submit']) || empty($_POST['reassign_user'])) {
			return;
		}

		$userId = (int) sanitize_text_field($_GET['user']);
		if (get_user($userId) === false) {
			return;
		}

		$reassignUserId = (int) sanitize_text_field($_POST['reassign_user']);
		if (get_user($reassignUserId) === false) {
			return;
		}

		remove_user_from_blog($userId, get_current_blog_id(), $reassignUserId);

		wp_redirect(admin_url('users.php?message=removed'));
		exit;
	}

	#[Filter(self::DELETE_NETWORK_USER_CRON_HOOK)]
	public function deleteNetworkUser(int $id): void
	{
		if (get_user($id) === false) {
			return;
		}

		if (count(get_blogs_of_user($id)) > 0) {
			return;
		}

		if (! function_exists('wpmu_delete_user')) {
			require_once ABSPATH . '/wp-admin/includes/ms.php';
		}

		\wpmu_delete_user($id);
	}

	#[Filter('network_site_url')]
	public function setLoginUrl(string $url, string $path, ?string $scheme): string
	{
		if (str_contains($path, 'wp-login.php') === false) {
			return $url;
		}

		return site_url($path, $scheme);
	}

	protected function shouldDisplay(): bool
	{
		if (! isset($_GET['action'], $_GET['user'])) {
			return false;
		}

		if ('remove' !== $_GET['action']) {
			return false;
		}

		if (! current_user_can('remove_user', (int) $_GET['user'])) {
			return false;
		}

		return true;
	}

	protected function renderReassignUserSelect(array $users, int $userId): void
	{
		echo '<div class="notice notice-warning">
				<h2>' . esc_html__('Reassign Posts', 'sage') . '</h2>
    			<p>' . esc_html__('Select a user to reassign posts to (optional):', 'sage') . '</p>';
		wp_dropdown_users([
			'exclude' => [$userId],
			'name' => 'reassign_user',
			'show_option_none' => esc_html__('-- No reassignment --', 'sage'),
			'option_none_value' => -1,
			'selected' => -1,
		]);
		echo '<br><br></div>';
	}
}
