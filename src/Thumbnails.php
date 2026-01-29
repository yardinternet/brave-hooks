<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;

class Thumbnails
{
	#[Action('init')]
	public function allowThumbnailSizes(): void
	{
		$allowed = ['thumbnail', 'medium', 'medium_large', 'large'];

		foreach (get_intermediate_image_sizes() as $size) {
			if (! in_array($size, $allowed)) {
				remove_image_size($size);
			}
		}
	}
}
