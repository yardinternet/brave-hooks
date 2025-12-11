<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Illuminate\Http\Request;
use SearchWP\Highlighter;
use Yard\Hook\Action;
use Yard\Hook\Filter;

#[Plugin('facetwp/index.php')]
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

		$highlighter = new Highlighter();
		$needle = sanitize_text_field($_GET['_zoeken']);

		if ($highlighter instanceof Highlighter && ! empty($needle)) {
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

	#[Filter('facetwp_gmaps_api_key')]
	public function setGoogleMapsApiKey(): string
	{
		return env('GOOGLE_MAPS_API_KEY', '');
	}

	#[Filter('facetwp_proximity_autocomplete_options')]
	public function setProximityAutocompleteOptions(array $options): array
	{
		$options['region'] = 'nl';

		return $options;
	}

	#[Filter('gettext_fwp-front')]
	public function translatePagerLabels(string $translation, string $text): string
	{
		return match ($text) {
			'Go to page' => 'Ga naar pagina',
			'Go to next page' => 'Ga naar de volgende pagina',
			'Go to previous page' => 'Ga naar de vorige pagina',
			default => $translation,
		};
	}

	/**
	 * A11y: Change pager wrapper from <div> to <ul>
	 */
	#[Filter('facetwp_facet_html')]
	public function changePagerWrapperTag(string $html, array $params): string
	{
		if (isset($params['facet']['type']) && 'pager' === $params['facet']['type'] && 'numbers' === $params['facet']['pager_type']) {
			$html = str_replace('<div class="facetwp-pager"', '<nav aria-label="Paginering"><ul class="facetwp-pager list-none pl-0 mb-0"', $html);
			$html = str_replace('</div>', '</ul></nav>', $html);
		}

		return $html;
	}

	/**
	 * A11y: Change the pager links
	 */
	#[Filter('facetwp_facet_pager_link')]
	public function changePagerLinks(string $html, array $params): string
	{
		// Wrap links with <li>
		$html = str_replace(['<a', '/a>'], ['<li><a', '/a></li>'], $html);

		// Modify dots to be non-interactive <span>
		if ('dots' === $params['extra_class']) {
			$html = str_replace('facetwp-page ', 'facetwp-page-', $html); // Disable facetwp_load_a11y changes
			$html = str_replace(['<a', '/a>'], ['<span aria-hidden="true"', '/span>'], $html);
		}

		return $html;
	}
}
