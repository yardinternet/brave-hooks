<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yoast\WP\Duplicate_Post\Permissions_Helper;

#[Plugin('duplicate-post/duplicate-post.php')]
class DuplicatePost
{
	// This is a workaround for the Duplicate Post plugin to save revisions for the rewrite and republish copy
	// Can be removed when this issue is fixed: https://github.com/Yoast/duplicate-post/issues/404
	#[Action('load-post.php')]
	public function saveRevisionForRewriteAndRepublishCopy(): void
	{
		if (! class_exists(Permissions_Helper::class)) {
			return;
		}

		$postID = intval($_GET['post']);
		$post = get_post($postID);
		if (null === $post || ! $post instanceof \WP_Post) {
			return;
		}

		if (! (new Permissions_Helper())->is_rewrite_and_republish_copy($post)) {
			return;
		}

		$revisionData = wp_get_latest_revision_id_and_total_count($post->ID);

		if (is_wp_error($revisionData) || 0 < $revisionData['count']) {
			return;
		}

		wp_save_post_revision($post->ID);
	}
}
