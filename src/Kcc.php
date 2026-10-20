<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Filter;

#[Plugin('yard-kcc/yard-kcc.php')]
class Kcc
{
	/**
	 * Render the KCC listing through the theme's kcc-loop view. The view check lives inside the
	 * template so it runs on render, since FacetWP can persist this template to its settings.
	 */
	#[Filter('yard::kcc/facetwp-listing-template')]
	public function listingTemplate(): string
	{
		return <<<'TEMPLATE'
<?php if (view()->exists('blocks.FacetWP.loops.kcc-loop')) : ?>
	<?php echo view('blocks.FacetWP.loops.kcc-loop'); ?>
<?php else : ?>
	<p>Template <code>blocks.FacetWP.loops.kcc-loop</code> kan niet gevonden worden.</p>
<?php endif; ?>
TEMPLATE;
	}
}
