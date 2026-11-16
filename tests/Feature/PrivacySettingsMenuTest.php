<?php

declare(strict_types=1);

use Yard\Brave\Hooks\Authorization;

afterEach(function () {
	unset($GLOBALS['menu']);
});

it('makes the menu that opens Privacy ask for the Privacy cap', function () {
	// Core has already moved Settings to its first reachable submenu, but still asks for manage_options.
	$GLOBALS['menu'] = [80 => ['Settings', 'manage_options', 'options-privacy.php']];

	(new Authorization())->keepPrivacySettingsReachable();

	expect($GLOBALS['menu'][80][1])->toBe('manage_privacy_options');
});

it('leaves the regular Settings menu alone', function () {
	$GLOBALS['menu'] = [80 => ['Settings', 'manage_options', 'options-general.php']];

	(new Authorization())->keepPrivacySettingsReachable();

	expect($GLOBALS['menu'][80][1])->toBe('manage_options');
});
