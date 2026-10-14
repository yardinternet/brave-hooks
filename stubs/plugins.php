<?php

declare(strict_types=1);

/**
 * Minimal stubs for third-party plugins that have no stubs package, used by PHPStan only.
 */

namespace SearchWP {
	class Highlighter
	{
		/**
		 * @param string|string[] $needle
		 */
		public static function apply(string $haystack, string|array $needle): string
		{
		}
	}
}

namespace {
	class FacetWP_Display
	{
		/** @var array<string, mixed> */
		public $json = [];
	}

	class FacetWP
	{
		/** @var FacetWP_Display */
		public $display;
	}

	function FWP(): FacetWP
	{
	}

	class Rudr_Simple_WP_Crosspost
	{
		/**
		 * @param array<string, mixed> $blog
		 */
		public static function get_blog_id(array $blog): string
		{
		}

		public static function is_crossposted(int $post_id, string $blog_id): bool
		{
		}

		public static function remove_crossposted_data(int $post_id, string $blog_id): void
		{
		}

		/**
		 * @param array<string, mixed> $blog
		 *
		 * @return array<string, mixed>|false
		 */
		public static function maybe_crosspost_image(int $attachment_id, array $blog): array|false
		{
		}

		/**
		 * @param array<string, mixed> $blog
		 */
		public static function is_blog_wordpress_com(array $blog): bool
		{
		}
	}
}
