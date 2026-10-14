<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

#[Plugin('weglot/weglot.php')]
class Weglot
{
	/**
	 * Treat all feeds as XML. Weglot's XML detection fails on some feeds (e.g. the namespaced
	 * Samenwerkende Catalogi feed), so it parses them as HTML and lowercases all tag names.
	 */
	#[Filter('weglot_type_treat_page')]
	public function treatFeedsAsXml(string $type): string
	{
		return is_feed() ? 'xml' : $type;
	}
}
