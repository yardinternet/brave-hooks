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

		$searchTerm = sanitize_text_field(get_query_var('s'));

		$this->redirect($this->wrapInQuotes($searchTerm));
	}

	#[Action('template_redirect')]
	public function wrapSearchParameterInQuotes(): void
	{
		if (! is_search() || is_admin()) {
			return;
		}

		if (str_starts_with($_GET['q'], '\"') && str_ends_with($_GET['q'], '\"')) {
			return;
		}

		$searchTerm = sanitize_text_field($_GET['q']);

		$this->redirect($this->wrapInQuotes($searchTerm));
	}

	private function wrapInQuotes(string $searchQuery): string
	{
		return '"' . str_replace(['\\', '"'], '', $searchQuery) . '"';
	}

	private function redirect(string $searchQuery): void
	{
		wp_safe_redirect(home_url('/zoeken?q=' . urlencode($searchQuery)));
		exit;
	}
}
