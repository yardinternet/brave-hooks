<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

class Imagify
{
	#[Filter('imagify_auto_optimize_attachment')]
	public function doNotOptimizePDF(bool $optimize, int $attachmentID): bool
	{
		if (false === $optimize) {
			return false;
		}

		$mimeType = get_post_mime_type($attachmentID);

		return 'application/pdf' !== $mimeType;
	}
}
