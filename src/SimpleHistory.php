<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

#[Plugin('wordpress-simple-history/index.php')]
class SimpleHistory
{
	#[Filter('simple_history/show_promo_boxes')]
	public function hidePromoBoxes(): bool
	{
		return false;
	}

	#[Filter('simple_history/db_purge_days_interval')]
	public function setPurgeInterval(): int
	{
		$daysBeforePurge = 90;

		return $daysBeforePurge;
	}
}
