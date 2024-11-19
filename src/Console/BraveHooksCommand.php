<?php

declare(strict_types=1);

namespace Yard\BraveHooks\Console;

use Illuminate\Console\Command;
use Yard\BraveHooks\Facades\BraveHooks;

class BraveHooksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bravehooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'My custom Acorn command.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info(
            BraveHooks::getQuote()
        );
    }
}
