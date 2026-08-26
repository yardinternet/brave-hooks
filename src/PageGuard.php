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
	 * @return array<array<string, string>|string>
	 */
	#[Filter('yard::brave-owc/internal-data/internal-information')]
	public function addContentOwner(array $internalInformation, int $postId): array
	{
		if (! class_exists(ReviewItem::class)) {
			return $internalInformation;
		}

		$post = get_post($postId);

		if (! $post instanceof \WP_Post) {
			return $internalInformation;
		}

		$contentOwner = (new ReviewItem($post))->contentOwner();

		if (! $contentOwner) {
			return $internalInformation;
		}

		$email = $contentOwner->email();
		$name = $contentOwner->name();
		$phoneNumber = method_exists($contentOwner, 'phone') ? $contentOwner->phone() : '';

		$contentOwnerInfo = 'Inhoudseigenaar: ' . ($email ?
			sprintf(
				'<a href="%s">%s</a>',
				esc_url('mailto:' . $email),
				esc_html($name)
			)
				: esc_html($name));

		if ('' !== $phoneNumber) {
			$phoneInfo = sprintf(
				'<a href="%s">%s</a>',
				esc_url('tel:' . $phoneNumber),
				esc_html($phoneNumber)
			);
			$contentOwnerInfo .= sprintf(' (%s)', $phoneInfo);
		}

		$internalInformation[] = [
			'internal_information_title' => __('Inhoudscontrole', 'sage'),
			'internal_information_content' => $contentOwnerInfo,
		];

		return $internalInformation;
	}
}
