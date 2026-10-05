<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks\Components;

use Closure;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use WP_Block_Type;
use WP_Block_Type_Registry;

class BlockComponent extends Component
{
	/**
	 * @var array<string, string>
	 */
	public static array $aliases = [];

	/**
	 * Override resolveView for performance and safety.
	 *
	 * Laravel compiles strings returned from render() as Blade source, which is ~8x slower and could execute Blade syntax (like {{ }} ) found in block content.
	 *
	 * Block HTML is already final, so return it as HtmlString to output it as-is.
	 */
	public function resolveView(): Closure
	{
		$render = $this->render();

		return fn (array $data): HtmlString => new HtmlString($render($data));
	}

	public function render(): Closure
	{
		return function (array $data): string {
			$blockName = self::$aliases[$this->componentName] ?? null;
			$slot = (string) $data['slot'];

			if (null === $blockName) {
				return $slot;
			}

			return render_block([
				'blockName' => $blockName,
				'attrs' => array_filter(
					[...$this->defaultVariationAttributes($blockName), ...$this->attributesToBlockAttributes()],
					fn ($value) => '' !== $value, // Drop empty values
				),
				'innerBlocks' => [],
				'innerHTML' => $slot,
				'innerContent' => [$slot],
			]);
		};
	}

	/**
	 * @return array<string, mixed>
	 */
	private function attributesToBlockAttributes(): array
	{
		return collect($this->attributes->all())
			->mapWithKeys(fn ($value, string $key) => ['class' === $key ? 'className' : Str::camel($key) => $value])
			->all();
	}

	/**
	 * Apply the default variation attributes
	 *
	 * @return array<string, mixed>
	 */
	private function defaultVariationAttributes(string $blockName): array
	{
		$blockType = WP_Block_Type_Registry::get_instance()->get_registered($blockName);

		if (! $blockType instanceof WP_Block_Type) {
			return [];
		}

		foreach ($blockType->get_variations() as $variation) {
			if ($variation['isDefault'] ?? false) {
				return $variation['attributes'] ?? [];
			}
		}

		return [];
	}
}
