<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;
use Yard\PageGuard\Models\ReviewItem;

#[Plugin('yard-page-guard/yard-page-guard.php')]
class PageGuard
{
	/**
	 * @param array<string> $internalInformation
	 *
	 * @return array<string>
	 */
	#[Filter('yard::brave-owc/internal-data/internal-information')]
	public function addContentOwner(array $internalInformation, int $postId): array
	{
		$post = get_post($postId);

		if (! $post instanceof \WP_Post) {
			return $internalInformation;
		}

		$contentOwner = (new ReviewItem($post))->contentOwner();

		if (! $contentOwner) {
			return $internalInformation;
		}

		$contenOwnerInfo = sprintf(
			'%s <a href="mailto:%s">%s</a>',
			'Inhoudseigenaar: ',
			esc_attr($contentOwner->email()),
			esc_html($contentOwner->name())
		);

		if ('' !== $contentOwner->phone()) {
			$phone = sprintf(
				'<a href = "tel:%s">%s</a>',
				$contentOwner->phone(),
				esc_attr($contentOwner->phone())
			);
			$contenOwnerInfo .= sprintf(' (%s)', $phone);
		}

		if (! $contentOwner) {
			return $internalInformation;
		}

		$internalInformation[] = [
			'internal_information_title' => __('Inhoudscontrole', 'sage'),
			'internal_information_content' => $contenOwnerInfo,
		];

		return $internalInformation;
	}
}
