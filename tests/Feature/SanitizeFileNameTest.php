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

it('keeps the file name safe for the filesystem', function () {
	$fileName = $this->gravityForms->transliterateFileNameToAscii('../../etc/passwd');

	expect($fileName)->not->toContain('/')
		->and($fileName)->not->toContain('..');
});
