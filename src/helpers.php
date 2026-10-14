<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

if (! function_exists(__NAMESPACE__ . '\config_array')) {
	/**
	 * Get a config value as an array, falling back to an empty array when the value is not an array.
	 *
	 * @return array<mixed>
	 */
	function config_array(string $key): array
	{
		$value = config($key, []);

		return is_array($value) ? $value : [];
	}
}
