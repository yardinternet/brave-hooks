<?php

declare(strict_types=1);

namespace Yard\BraveHooks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getQuote()
 * @method static string getPostContent()
 */
class BraveHooks extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'BraveHooks';
    }
}
