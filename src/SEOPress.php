<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Brave\Hooks\Traits\ParentPage;
use Yard\Hook\Filter;

#[Plugin('wp-seopress-pro/seopress-pro.php')]
class SEOPress
{
	use ParentPage;

	/**
	 * @param array<int, array<int, mixed>> $breadcrumbs
	 *
	 * @return array<int, array<int, mixed>>
	 */
	#[Filter('seopress_pro_breadcrumbs_crumbs')]
	public function addBreadcrumb(array $breadcrumbs): array
	{
		$postId = get_the_ID();

		if (false === $postId || is_search()) {
			return $breadcrumbs;
		}

		$parentIds = $this->getParentPagesIds($postId);
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

	#[Filter('seopress_capability', 10000)]
	public function dashboardOptions(string $cap, ?string $context = null): string
	{
		if ('menu' === $context || 'bot' === $context) {
			$cap = 'edit_posts';
		}

		return $cap;
	}
}
