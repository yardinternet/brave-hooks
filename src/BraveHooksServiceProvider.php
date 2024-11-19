<?php

declare(strict_types=1);

namespace Yard\BraveHooks;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Yard\BraveHooks\Console\BraveHooksCommand;

class BraveHooksServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('brave-hooks')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(BraveHooksCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('BraveHooks', fn () => new BraveHooks($this->app));
    }

    public function packageBooted(): void
    {
        $this->app->make('BraveHooks');
    }
}
