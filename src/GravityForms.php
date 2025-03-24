<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yard\Hook\Filter;

class GravityForms
{
	#[Filter('gform_disable_auto_update')]
	public function disableAutoUpdate(): bool
	{
		return true;
	}

	#[Filter('gform_confirmation_anchor')]
	public function confirmationAnchor(): bool
	{
		return true;
	}

	#[Filter('gform_require_login_pre_download')]
	public function requireLoginPreDownload(): bool
	{
		return true;
	}

	#[Filter('gform_form_args')]
	public function showFormsUsingAjax(array $form): array
	{
		$form['ajax'] = true;

		return $form;
	}

	#[Filter('gform_form_theme_slug')]
	public function useDefaultTheme(): string
	{
		if (\is_admin()) {
			return '';
		}

		return 'gravity-theme';
	}

	#[Filter('gform_phone_formats')]
	public function nlPhoneFormat(array $phoneFormats): array
	{
		$phoneFormats['standard'] = [
			'label' => 'NL',
			'mask' => false,
			'regex' => '/^((\+|00(\s|\s?\-\s?)?)31(\s|\s?\-\s?)?(\(0\)[\-\s]?)?|0)[1-9]((\s|\s?\-\s?)?[0-9])((\s|\s?-\s?)?[0-9])((\s|\s?-\s?)?[0-9])\s?[0-9]\s?[0-9]\s?[0-9]\s?[0-9]\s?[0-9]$/',
			'instruction' => '(###) ###-####',
		];

		return $phoneFormats;
	}

	/**
	 * Add necessary disclaimer if reCAPTCHA v3 plugin is activated and is not disabled for the current form
	 */
	#[Filter('gform_get_form_filter')]
	public function addRecaptchaDisclaimer(string $formString, array $form): string
	{
		if (! class_exists('Gravity_Forms\Gravity_Forms_RECAPTCHA\GF_RECAPTCHA')) {
			return $formString;
		}

		if (isset($form['gravityformsrecaptcha']['disable-recaptchav3']) && '1' === $form['gravityformsrecaptcha']['disable-recaptchav3']) {
			return $formString;
		}

		$startTag = '</form>';
		$disclaimer = '<p class="gform-recaptcha-disclaimer text-xs !leading-snug text-gray-500">Dit formulier is beveiligd met reCAPTCHA. Het <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer nofollow" class="text-inherit">privacybeleid<span class="sr-only">(opent in nieuw tabblad)</span></a> en de <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer nofollow" class="text-inherit">servicevoorwaarden<span class="sr-only">(opent in nieuw tabblad)</span></a> van Google zijn van toepassing.</p>';

		return str_replace($startTag, $startTag . $disclaimer, $formString);
	}

	/**
	 * A11y: add role="alert" to validation message
	 */
	#[Filter('gform_form_validation_errors_markup')]
	public function addRoleAlertToValidation(string $markup): string
	{
		$startTag = '<div class="gform_validation_errors"';

		return str_replace($startTag, $startTag . ' role="alert"', $markup);
	}

	/**
	 * A11y: add role="alert" to confirmation message
	 */
	#[Filter('gform_confirmation')]
	public function addRoleAlertToConfirmation(string|array $confirmation): string|array
	{
		if (is_string($confirmation) && str_contains($confirmation, "class='gform_confirmation_wrapper")) {
			$class = "class='gform_confirmation_wrapper";
			$confirmation = str_replace($class, "role='alert' " . $class, $confirmation);
		}

		return $confirmation;
	}

	/**
	 * A11y: change default required field message to something more descriptive.
	 */
	#[Filter('gform_field_validation')]
	public function changeRequiredFieldValidationMessage(array $result, string|array $value, array $form, \GF_Field $field): array
	{
		$label = isset($field['label']) && is_string($field['label']) ? $field['label'] : '';
		$result['message'] = str_replace(
			'Dit veld is vereist',
			sprintf('Het verplichte veld "%s" is niet ingevuld', $label),
			$result['message'] ?? sprintf('Het verplichte veld "%s" is niet ingevuld', $label)
		);

		return $result;
	}

	/**
	 * Fixes the GF merge tags for notifications.
	 */
	#[Action('admin_head')]
	public function fixMergeTags(): void
	{
		if (empty($_GET['subview']) || 'notification' !== $_GET['subview']) {
			return;
		}

		wp_print_inline_script_tag(
			"
			window.addEventListener('DOMContentLoaded', function() {
				const eventSelect = document.querySelectorAll('[name=\"_gform_setting_event\"');

				if (!eventSelect.length) {
					return;
				}

				eventSelect[0].setAttribute('id', 'event');
			});"
		);
	}

	#[Action('gform_after_save_form')]
	public function defaultSettings(array $form): void
	{
		if (! class_exists('GFAPI')) {
			return;
		}

		$form = $this->setActive($form);
		$form = $this->enableHoneypot($form);
		$form = $this->setRetainEntriesDays($form);
		$form = $this->preventIPSaving($form);
		$form = $this->enableValidationSummary($form);
		$form = $this->enableAutocomplete($form);

		\GFAPI::update_form($form);
	}

	/**
	 * Determine if form is active by form meta.
	 * Without passing the is_active flag the form will be set to inactive by GF.
	 */
	protected function setActive(array $form): array
	{
		$formMeta = \GFAPI::get_form($form['id']);
		$isActive = $formMeta['is_active'] ?? '1'; // Defaults to active, you don't want to deactivate activated forms.
		$status = '1' === $isActive ? '1' : '0';
		$form['is_active'] = $status;

		return $form;
	}

	/**
	 * Honeypot enabled by default.
	 */
	protected function enableHoneypot(array $form): array
	{
		$honeyPotEnabled = $form['enableHoneypot'] ?? false;

		if ($honeyPotEnabled) {
			return $form;
		}

		$form['enableHoneypot'] = true;

		return $form;
	}

	/**
	 * Retain entries days limitation.
	 */
	protected function setRetainEntriesDays(array $form): array
	{
		$retainEntriesDays = $form['personalData']['retention']['retain_entries_days'] ?? false;
		$limitEntriesDaysPolicy = $form['personalData']['retention']['policy'] ?? false;

		if (is_numeric($retainEntriesDays) && is_string($limitEntriesDaysPolicy)) {
			return $form;
		}

		$form['personalData']['retention']['retain_entries_days'] = env('GF_RETAIN_ENTRIES_DAYS', 10);
		$form['personalData']['retention']['policy'] = 'delete';

		return $form;
	}

	/**
	 * Prevent IP to be saved.
	 */
	protected function preventIPSaving(array $form): array
	{
		$isIpAddressPrevented = $form['personalData']['preventIP'] ?? false;

		if ($isIpAddressPrevented) {
			return $form;
		}

		$form['personalData']['preventIP'] = true;

		return $form;
	}

	/**
	 * A11y: force validation summary.
	 */
	protected function enableValidationSummary(array $form): array
	{
		$validationSummaryEnabled = $form['validationSummary'] ?? false;

		if (! $validationSummaryEnabled) {
			$form['validationSummary'] = true;
		}

		return $form;
	}

	/**
	 * A11y: force enable autocomplete for some field types.
	 */
	protected function enableAutocomplete(array $form): array
	{
		$enableForFieldTypes = ['name', 'email', 'phone', 'address'];

		foreach ($form['fields'] as $index => $field) {
			if (in_array($field['type'], $enableForFieldTypes)) {
				$form['fields'][$index]['enableAutocomplete'] = true;
			}
		}

		return $form;
	}


}
