<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yard\Hook\Filter;

#[Plugin('advanced-custom-fields-pro/acf.php')]
class ACF
{
	#[Filter('acf/settings/enable_post_types')]
	public function enablePostTypes(): bool
	{
		return false;
	}

	#[Filter('acf/fields/google_map/api')]
	public function my_acf_google_map_api(array $api): array
	{
		$api['key'] = env('GOOGLE_MAPS_API_KEY', '');

		return $api;
	}

	#[Filter('acf/settings/load_json')]
	public function loadJson(array $paths): array
	{
		$paths[] = get_template_directory() . '/acf-json';

		return $paths;
	}

	#[Filter('acf/settings/save_json')]
	public function saveJson(string $path): string
	{
		return get_template_directory() . '/acf-json';
	}

	/**
	 * ACF WYSIWYG fields inside a repeater sometimes lose their TinyMCE
	 * content on load (visual tab empty, text tab correct). Minified
	 * production assets load fast enough to trigger a race between the
	 * repeater row render and TinyMCE's init, so we force a clean
	 * re-init and resync the content once ACF is ready.
	 *
	 * @see https://github.com/WordPress/gutenberg/issues/74627
	 */
	#[Action('admin_footer')]
	public function fixWysiwygRepeaterTinymceRace(): void
	{
		wp_print_inline_script_tag(<<<'JS'
			document.addEventListener('DOMContentLoaded', function () {
				if (typeof acf === 'undefined') {
					return;
				}

				acf.addAction('ready', function () {
					document.querySelectorAll('.acf-field-wysiwyg textarea.wp-editor-area').forEach(function (textarea) {
						if (typeof tinymce === 'undefined' || ! tinymce.get(textarea.id)) {
							return;
						}

						const id = textarea.id;
						const raw = textarea.value;

						tinymce.execCommand('mceRemoveEditor', false, id);
						tinymce.execCommand('mceAddEditor', false, id);

						const editor = tinymce.get(id);
						if (editor) {
							editor.on('init', function () {
								editor.setContent(raw);
							});
						}
					});
				});
			});
		JS);
	}
}
