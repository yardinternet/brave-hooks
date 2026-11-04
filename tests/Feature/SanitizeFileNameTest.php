<?php

declare(strict_types=1);

use Yard\Brave\Hooks\GravityForms;

beforeEach(function () {
	$this->gravityForms = new GravityForms();
});

it('romanises non-ascii file names so the upload URL stays reachable', function (string $fileName, string $expected) {
	expect($this->gravityForms->transliterateFileNameToAscii($fileName))->toBe($expected);
})->with([
	'arabic letters' => ['نورا7.jpg', 'nwra7.jpg'],
	'arabic-indic numerals' => ['١٢٣٤.png', '1234.png'],
	'android screenshot' => ['Screenshot_٢٠٢٤٠٥١٢.png', 'Screenshot_20240512.png'],
	'arabic name with spaces' => ['محمد عبد الله.pdf', 'mhmd-bd-allh.pdf'],
	'devanagari' => ['नमस्ते१२३.jpg', 'namaste123.jpg'],
	'accented latin' => ['café.jpg', 'cafe.jpg'],
	'plain ascii is untouched' => ['normal-file_1.JPG', 'normal-file_1.JPG'],
]);

it('does not add a trailing dot to names without an extension', function () {
	expect($this->gravityForms->transliterateFileNameToAscii('silence'))->toBe('silence')
		->and($this->gravityForms->transliterateFileNameToAscii('single-event-blocks'))->toBe('single-event-blocks');
});

it('keeps dots inside the name', function (string $fileName, string $expected) {
	expect($this->gravityForms->transliterateFileNameToAscii($fileName))->toBe($expected);
})->with([
	// FAZ Cookie Manager loads its banner from templates/<version>/template.json.
	'version slug' => ['6.2.0', '6.2.0'],
	'dotted upload name' => ['jaarverslag.v2.pdf', 'jaarverslag.v2.pdf'],
]);

it('does not leave consecutive or edge dots in the name', function () {
	expect($this->gravityForms->transliterateFileNameToAscii('...jpg'))->not->toContain('..')
		->and($this->gravityForms->transliterateFileNameToAscii('a..b.jpg'))->toBe('a.b.jpg');
});

it('keeps the file name safe for the filesystem', function () {
	$fileName = $this->gravityForms->transliterateFileNameToAscii('../../etc/passwd');

	expect($fileName)->not->toContain('/')
		->and($fileName)->not->toContain('..');
});
