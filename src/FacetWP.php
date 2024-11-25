<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Illuminate\Http\Request;
use Yard\Hook\Action;
use Yard\Hook\Filter;

class FacetWP
{
	#[Filter('facetwp_is_main_query')]
	public function isMainQuery(): bool
	{
		return false;
	}

	#[Filter('facetwp_load_a11y')]
	public function loadA11y(): bool
	{
		return true;
	}

	#[Filter('facetwp_render_output')]
	public function setHighlightSearchTerm(array $output): array
	{
		if (! class_exists('\SearchWP\Highlighter') || empty($_GET['_zoeken'])) {
			return $output;
		}

		$highlighter = new \SearchWP\Highlighter();
		$needle = sanitize_text_field($_GET['_zoeken']);

		if ($highlighter instanceof \SearchWP\Highlighter && ! empty($needle)) {
			$output['template'] = $highlighter->apply($output['template'], $needle);
		}

		return $output;
	}

	#[Filter('facetwp_facets')]
	public function addFacets(array $facets): array
	{
		$config = config('facetwp.facets');

		return [...$facets, ...$config];
	}

	#[Filter('facetwp_templates')]
	public function addTemplates(array $templates): array
	{
		$config = config('facetwp.templates');

		return [...$templates, ...$config];
	}

	#[Action('template_redirect')]
	public function handleRedirect(): void
	{
		if (! is_search()) {
			return;
		}

		$request = Request::capture();
		if (((null !== $request->get('s'))) && (null === ($request->get($this->searchParameter())))) {
			$url = $request->url() . '/?' . http_build_query(
				array_merge(
					$request->all(),
					[
						$this->searchParameter() => $request->get('s'),
						's' => $request->get('s'),
					]
				)
			);
			wp_redirect($url);
			exit;
		}
	}

	private function searchParameter(): string
	{
		return $this->searchPrefix() . 'zoeken';
	}

	private function searchPrefix(): string
	{
		$settings = json_decode(get_option('facetwp_settings', ''), true);

		return $settings['settings']['prefix'] ?? '';
	}
}
