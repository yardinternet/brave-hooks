<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks\Registration;

use ReflectionClass;
use ReflectionException;
use Yard\Brave\Hooks\Plugin;

class Config
{
	/**
	 * @param array<int, class-string> $classNames
	 */
	public function __construct(public array $classNames = [])
	{
	}

	/**
	 * @param array<string, mixed> $config
	 */
	public static function from(array $config): self
	{
		return new self(
			$config,
		);
	}

	/**
	 * @return array<class-string>
	 */
	public function classNames(): array
	{
		return collect($this->classNames)
			->filter(fn ($className) => $this->classNameIsValid($className))
			->filter(fn ($className) => $this->isPluginActive($className))
			->toArray();
	}

	public function classNameIsValid(mixed $className): bool
	{
		if (! is_string($className)) {
			return false;
		}

		return class_exists($className);
	}

	/**
	 * @throws ReflectionException
	 */
	public function isPluginActive(string $className): bool
	{
		$reflectionClass = new ReflectionClass($className);
		$attributes = $reflectionClass->getAttributes(Plugin::class);

		if (count($attributes) === 0) {
			return true;
		};

		foreach ($attributes as $attribute) {
			$plugin = $attribute->newInstance();

			return $plugin->isActive();
		}

		return false;
	}
}
