<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

class ACF
{
	#[Filter('acf/settings/enable_post_types')]
	public function enablePostTypes(): bool
	{
		return false;
	}
}
