<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Illuminate\Http\Request;
use SearchWP\Highlighter;
use Yard\Brave\Hooks\Traits\ParentPage;
use Yard\Hook\Action;
use Yard\Hook\Filter;

#[Plugin('facetwp/index.php')]
class FacetWP
{
	use ParentPage;

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

		if (! empty($needle) && isset($output['template']) && is_string($output['template'])) {
			$output['template'] = $this->highlightTemplateText($output['template'], $highlighter, $needle);
		}

		return $output;
	}

	// Only highlight the text nodes, not the HTML tags
	private function highlightTemplateText(string $template, Highlighter $highlighter, string $needle): string
	{
		// Example: "This is a <strong>test</strong> string." => ["This is a ", "<strong>", "test", "</strong>", " string."]
		$parts = preg_split('/(<[^>]+>)/', $template, -1, PREG_SPLIT_DELIM_CAPTURE);

		if (! is_array($parts)) {
			return $template;
		}

		foreach ($parts as $index => $part) {
			if (! is_string($part)) {
				continue;
			}

			if ('' === trim($part) || str_starts_with($part, '<')) {
				continue;
			}

			$parts[$index] = $highlighter->apply($part, $needle);
		}

		return implode('', $parts);
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

	#[Action('facetwp_scripts')]
	public function addCollapseButtonHtml(): void
	{
		FWP()->display->json['expand'] = '<button class="facetwp-button-collapse-expand" aria-expanded="false"><i class="fa-regular fa-plus" aria-hidden="true"></i><span class="sr-only">Klap uit</span></button>';
		FWP()->display->json['collapse'] = '<button class="facetwp-button-collapse-collapse" aria-expanded="true"><i class="fa-regular fa-minus" aria-hidden="true"></i><span class="sr-only">Klap in</span></button>';
	}

	private function collidingPageSlugs(): array
	{
		$slugs = [];

		foreach (get_post_types([], 'names') as $postType) {
			$slug = static::resolveParentPageSlug($postType);

			if ($slug && get_page_by_path($slug)) {
				$slugs[$slug] = true;
			}
		}

		return array_keys($slugs);
	}

	#[Action('init', 999)]
	public function registerPagingRewrite(): void
	{
		foreach ($this->collidingPageSlugs() as $slug) {
			add_rewrite_rule(
				'^'.preg_quote($slug, '/').'/page/?([0-9]{1,})/?$',
				'index.php?pagename='.$slug.'&paged=$matches[1]',
				'top'
			);
		}
	}

	private function templatePaginationNotJs(?string $templateName): bool
	{
		return in_array(
			$templateName,
			config('facetwp.no_js_pagination_templates', []),
			true
		);
	}

	#[Filter('facetwp_render_params')]
	public function bridgePagedQueryVar(array $params): array
	{
		$templatePaginationNotJs = $this->templatePaginationNotJs($params['template'] ?? null);

		if ($templatePaginationNotJs) {
			$paged = (int) get_query_var('paged');

			if (0 < $paged) {
				$params['paged'] = $paged;
			}
		}

		return $params;
	}

	#[Filter('facetwp_shortcode_html')]
	public function renderNoJsPager(string $html, array $atts): string
	{
		$templatePaginationNotJs = $this->templatePaginationNotJs(FWP()->facet->template['name'] ?? null);

		if (! $templatePaginationNotJs || 'pagination' !== ($atts['facet'] ?? null)) {
			return $html;
		}

		$pagerArgs = FWP()->facet->pager_args ?? [];
		$pagerFacet = FWP()->helper->get_facet_by_name('pagination') ?: [];

		$pageOneUrl = strtok(esc_url(get_pagenum_link(1)), '?');

		$pageLinks = paginate_links([
			'base' => trailingslashit($pageOneUrl) . '%_%',
			'format' => 'page/%#%/',
			'current' => (int) ($pagerArgs['page'] ?? 1),
			'total' => (int) ($pagerArgs['total_pages'] ?? 1),
			'prev_text' => $pagerFacet['prev_label'] ?? '',
			'next_text' => $pagerFacet['next_label'] ?? '',
			'type' => 'array',
		]);

		if (empty($pageLinks)) {
			return '';
		}

		$pageLinks = array_map(function ($link) {
			$link = preg_replace_callback('/href="([^"]*)"/', function ($matches) {
				return 'href="'.strtok($matches[1], '?').'"';
			}, $link);

			$link = str_replace('page-numbers', 'facetwp-page', $link);

			return str_replace('facetwp-page current', 'facetwp-page active', $link);
		}, $pageLinks);

		return '<nav aria-label="Paginering"><ul class="facetwp-pager"><li>'.implode('</li><li>', $pageLinks).'</li></ul></nav>';
	}

	#[Action('facetwp_scripts')]
	public function restoreJsPaginationFunction(): void
	{
		if (! $this->templatePaginationNotJs(FWP()->facet->template['name'] ?? null)) {
			return;
		}

		wp_print_inline_script_tag('
			document.addEventListener("click", function (event) {
				var link = event.target.closest("a.facetwp-page");

				if (!link) {
					return;
				}

				event.preventDefault();

				var url = new URL(link.href, window.location.origin);
				var matches = url.pathname.match(/\/page\/([0-9]+)/);
				var paged = matches ? matches[1] : null;

				if (paged) {
					FWP.paged = parseInt(paged, 10);
					FWP.soft_refresh = true;
					FWP.refresh();
				}
			});
		');
	}
}
