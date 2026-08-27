<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;
use Rudr_Simple_WP_Crosspost;
use WP_Post;

#[Plugin('rudr-simple-wp-crosspost/rudr-simple-wp-crosspost.php')]
class SimpleWPCrosspost
{
	#[Filter('acf/load_field/name=publication_source')]
	public function populatePublicationSourceField(array $field): array
	{
		$blogName = get_bloginfo('name');

		$field['choices'] = [];
		$field['choices'][$blogName] = $blogName;
		$field['default_value'] = $blogName;

		return $field;
	}

	#[Filter('rudr_swc_pre_crosspost_post_data', 10)]
	public function resyncModifiedFeaturedImage(array $data, array $blog, WP_Post $post, string $action): array
	{
		$thumbnailId = get_post_thumbnail_id($post->ID);

		if (! $thumbnailId) {
			return $data;
		}

		$blogId = Rudr_Simple_WP_Crosspost::get_blog_id($blog);
		$currentSourceUrl = wp_get_attachment_url($thumbnailId);

		if (! $currentSourceUrl) {
			return $data;
		}

		$trackingKey = '_crosspost_source_url_' . $blogId;
		$storedSourceUrl = get_post_meta($thumbnailId, $trackingKey, true);
		$wasCrossposted = Rudr_Simple_WP_Crosspost::is_crossposted($thumbnailId, $blogId);

		if ('create' !== $action && $wasCrossposted && (! $storedSourceUrl || $storedSourceUrl !== $currentSourceUrl)) {
			Rudr_Simple_WP_Crosspost::remove_crossposted_data($thumbnailId, $blogId);
			delete_post_meta($thumbnailId, '_crsspst_to_img_' . $blogId);

			$newImage = Rudr_Simple_WP_Crosspost::maybe_crosspost_image($thumbnailId, $blog);

			if (is_array($newImage) && $imageId = absint($newImage['id'] ?? 0)) {
				$key = Rudr_Simple_WP_Crosspost::is_blog_wordpress_com($blog) ? 'featured_image' : 'featured_media';
				$data[$key] = $imageId;
			}
		}

		update_post_meta($thumbnailId, $trackingKey, $currentSourceUrl);

		return $data;
	}
}
