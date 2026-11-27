<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;

class Scripts
{
	#[Action('wp_footer')]
	public function addSiteImproveScript(): void
	{
		$siteImproveId = config('scripts.site_improve.id', null);
		if (! $siteImproveId) {
			return;
		}

		wp_print_script_tag([
			'src' => sprintf('https://siteimproveanalytics.com/js/siteanalyze_%s.js', $siteImproveId),
		]);
	}

	#[Action('wp_footer')]
	public function enqueueClarityScript(): void
	{
		$clarityId = config('scripts.clarity.id', null);
		if (! $clarityId) {
			return;
		}

		$clarityId = wp_json_encode((string) $clarityId);

		$scriptContent = <<<EOT
			(function(c,l,a,r,i,t,y){
				c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
				t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
				y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
			})(window, document, "clarity", "script", {$clarityId});
		EOT;

		wp_print_inline_script_tag($scriptContent);
	}

	#[Action('wp_footer')]
	public function enqueueSilktide(): void
	{
		$silktideId = config('scripts.silktide.id', null);
		if (! $silktideId) {
			return;
		}

		wp_print_script_tag([
			'src' => sprintf('https://analytics.silktide.com/%s.js', $silktideId),
			'async' => true,
		]);
		wp_print_inline_script_tag(
			"(function (s,i,l,k,y) {
				s[i] = s[i] || y;
				s[l] = s[l] || [];
				s[k] = function(e, p) { p = p || {}; p.event = e; s[l].push(p); };
				s[k]('page_load');
  			})(window, 'stConfig', 'stEvents', 'silktide', {});"
		);
	}

	#[Action('wp_footer')]
	public function addMonsidoScript(): void
	{
		$monsidoId = config('scripts.monsido.id', null);
		if (! $monsidoId) {
			return;
		}

		wp_print_inline_script_tag(<<<'JS'
			window._monsido = window._monsido || {
				token: "{$monsidoId}",
				statistics: {
					enabled: true,
					cookieLessTracking: true,
					documentTracking: {
						enabled: true,
						documentCls: "monsido_download",
						documentIgnoreCls: "monsido_ignore_download",
						documentExt: [".docx", ".pdf"],
					},
				},
			};
			JS);

		wp_print_script_tag([
			'src' => 'https://app-script.monsido.com/v2/monsido-script.js',
			'type' => 'text/plain',
			'data-cookieyes' => 'cookieyes-analytics',
			'async' => true,
		]);
	}

	#[Action('wp_footer')]
	public function addGoogleAnalyticsScript(): void
	{
		$gaId = config('scripts.google_analytics.id', null);
		if (! $gaId) {
			return;
		}

		$gaIdJson = wp_json_encode((string) $gaId);
		wp_print_inline_script_tag(<<<JS
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', {$gaIdJson});
			JS);

		wp_print_script_tag([
			'src' => "https://www.googletagmanager.com/gtag/js?id={$gaId}",
			'type' => 'text/plain',
			'data-cookieyes' => 'cookieyes-analytics',
			'async' => true,
		]);
	}
}
