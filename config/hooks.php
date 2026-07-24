<?php

declare(strict_types=1);

return [
	'acf' => Yard\Brave\Hooks\ACF::class,
	'admin' => Yard\Brave\Hooks\Admin::class,
	'authorization' => Yard\Brave\Hooks\Authorization::class,
	'duplicate-post' => Yard\Brave\Hooks\DuplicatePost::class,
	'elasticsearch' => Yard\Brave\Hooks\Elasticsearch::class,
	'facetwp' => Yard\Brave\Hooks\FacetWP::class,
	'gravityforms' => Yard\Brave\Hooks\GravityForms::class,
	'gutenberg' => Yard\Brave\Hooks\Gutenberg::class,
	'imagify' => Yard\Brave\Hooks\Imagify::class,
	'onelogin' => Yard\Brave\Hooks\OneloginSSO::class,
	'openid-connect' => Yard\Brave\Hooks\OpenIDConnect::class,
	'searchwp' => Yard\Brave\Hooks\SearchWP::class,
	'security' => Yard\Brave\Hooks\Security::class,
	'seopress' => Yard\Brave\Hooks\SEOPress::class,
	'user' => Yard\Brave\Hooks\User::class,
	'warden' => Yard\Brave\Hooks\Warden::class,
];
