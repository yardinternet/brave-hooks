<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Yard\Brave\Hooks\Security;

// The WP_Mock test bootstrap does not boot a Laravel application, so the
// framework `app()` helper (used by spatie's csp_nonce()) is not loaded.
// Provide a minimal, container-backed shim that mirrors its resolution.
if (! function_exists('app')) {
	function app(?string $abstract = null, array $parameters = [])
	{
		if (null === $abstract) {
			return Container::getInstance();
		}

		return Container::getInstance()->make($abstract, $parameters);
	}
}

beforeEach(function () {
	$container = new Container();
	$container->instance('csp-nonce', 'test-nonce-value');
	Container::setInstance($container);
});

afterEach(function () {
	Container::setInstance(null);
});

it('delegates the CSP header entirely to nutshell middleware', function () {
	// In the Acorn 5 only major, nutshell's WordPress middleware always owns
	// the CSP header. brave-hooks no longer sends it itself, so the manual
	// send_headers fallback and its version gate must be gone.
	expect(method_exists(Security::class, 'sendCspHeader'))->toBeFalse();
	expect(method_exists(Security::class, 'nutshellHandlesCspViaMiddleware'))->toBeFalse();
});

it('adds the CSP nonce to script attributes', function () {
	$security = new Security();

	$result = $security->addScriptNonce(['id' => 'example']);

	expect($result)->toHaveKey('nonce')
		->and($result['nonce'])->toBe('test-nonce-value')
		->and($result['id'])->toBe('example');
});
