<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use ReflectionClass;
use ReflectionException;

class Config
{
	/**
	 * @param array<array-key, mixed> $classNames
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
		$classNames = [];

		foreach ($this->classNames as $key => $className) {
			if ($this->classNameIsValid($className) && $this->isPluginActive($className)) {
				$classNames[$key] = $className;
			}
		}

		return $classNames;
	}

	/**
	 * @phpstan-assert-if-true class-string $className
	 */
	public function classNameIsValid(mixed $className): bool
	{
		if (! is_string($className)) {
			return false;
		}

		return class_exists($className);
	}

	/**
	 * @param class-string $className
	 *
	 * @throws ReflectionException
	 */
	public function isPluginActive(string $className): bool
	{
		$reflectionClass = new ReflectionClass($className);
		$attributes = $reflectionClass->getAttributes(Plugin::class);

		if (count($attributes) === 0) {
			return true;
		}

		return $attributes[0]->newInstance()->isActive();
	}
}
