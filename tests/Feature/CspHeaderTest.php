<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Yard\Brave\Hooks\Security;

it('defers CSP to nutshell middleware when yard/nutshell >= 2.0.0 is installed', function () {
	$reflection = new ReflectionMethod(Security::class, 'nutshellHandlesCspViaMiddleware');
	$reflection->setAccessible(true);

	$expected = InstalledVersions::isInstalled('yard/nutshell')
		&& version_compare((string) InstalledVersions::getVersion('yard/nutshell'), '2.0.0', '>=');

	expect($reflection->invoke(new Security()))->toBe($expected);
});
