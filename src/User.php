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
}
