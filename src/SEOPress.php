<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Brave\Hooks\Traits\ParentPage;
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
		if ('menu' == $context || 'bot' == $context) {
			$cap = 'edit_posts';
		}

		return $cap;
	}
}
