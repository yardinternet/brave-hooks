<?php

declare(strict_types=1);

use Yard\Brave\Hooks\Gutenberg;

afterEach(function () {
	WP_Mock::tearDown();
});

it('removes the Custom HTML block for users without unfiltered_html', function () {
	WP_Mock::userFunction('current_user_can')->with('unfiltered_html')->andReturn(false);

	$result = (new Gutenberg())->restrictHtmlBlock(['core/paragraph', 'core/html', 'core/image']);

	expect($result)->toBe(['core/paragraph', 'core/image']);
});

it('keeps the Custom HTML block for users with unfiltered_html', function () {
	WP_Mock::userFunction('current_user_can')->with('unfiltered_html')->andReturn(true);

	$result = (new Gutenberg())->restrictHtmlBlock(['core/paragraph', 'core/html']);

	expect($result)->toBe(['core/paragraph', 'core/html']);
});
