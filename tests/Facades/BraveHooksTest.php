<?php

declare(strict_types=1);

use Yard\BraveHooks\Facades\BraveHooks;

it('can retrieve a random inspirational quote', function () {
    $quote = BraveHooks::getQuote();

    expect($quote)->tobe('For every Sage there is an Acorn.');
});
