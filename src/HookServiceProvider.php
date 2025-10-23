<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Yard\Hook\Registrar;

class HookServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		$package
			->name('brave-hooks')
			->hasConfigFile('hooks');
	}

	public function packageRegistered(): void
	{
		$this->app->bind(Registrar::class, function () {
			$config = Config::from(config('hooks'));

			return new Registrar($config->classNames());
		});
	}

	public function packageBooted(): void
	{
		app(Registrar::class)->registerHooks();
	}
}
