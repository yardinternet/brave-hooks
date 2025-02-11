<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;

class Elasticsearch
{
	#[Action('init')]
	public function elasticSearchRoute(): void
	{
		add_rewrite_rule('^zoeken/?$', 'index.php?s=$matches[1]', 'top');
	}

	#[Action('template_redirect')]
	public function redirectDefaultSearch(): void
	{
		if (! is_search() || is_admin() || strlen($_GET['q'] ?? '') > 0) {
			return;
		}

		$searchQuery = sanitize_text_field(get_query_var('s'));

		// Wrap the search query in quotes
		$searchQuery = '"' . str_replace(['\\', '"'], '', $searchQuery) . '"';

		wp_safe_redirect(home_url('/zoeken?q=' . urlencode($searchQuery)));
		exit;
	}
}
