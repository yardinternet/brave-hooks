<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

#[Plugin('searchwp/index.php')]
class SearchWP
{
	#[Filter('searchwp\native\short_circuit')]
	public function shortCircuit(): bool
	{
		return true;
	}

	#[Filter('searchwp\rest')]
	public function disableRest(): bool
	{
		return false;
	}

	#[Filter('searchwp\swp_query\args')]
	public function changePostsPerPage(array $args): array
	{
		// Change FacetWP default maximum of 200 results to unlimited
		if (isset($args['facetwp'])) {
			$args['posts_per_page'] = -1;
		}

		return $args;
	}
}
