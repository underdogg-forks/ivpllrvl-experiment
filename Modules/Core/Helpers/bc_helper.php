<?php

/**
 * Backward Compatibility Helper.
 *
 * This file provides procedural function wrappers that call static methods
 * from helper classes in Modules/Core/Support/. This maintains backward
 * compatibility with existing CodeIgniter code that expects procedural
 * helper functions while moving towards a more modern, object-oriented approach.
 *
 * All helper logic is now in static class methods, and these functions
 * simply delegate to those methods.
 *
 * This file is autoloaded via composer.json to ensure all helper
 * functions are available throughout the application.
 */

use Modules\Core\Support\ClientHelper;
use Modules\Core\Support\CountryHelper;
use Modules\Core\Support\CustomValuesHelper;
use Modules\Core\Support\DateHelper;
use Modules\Core\Support\DiacriticsHelper;
use Modules\Core\Support\DropzoneHelper;
use Modules\Core\Support\EchoHelper;
use Modules\Core\Support\EInvoiceHelper;
use Modules\Core\Support\InvoiceHelper;
use Modules\Core\Support\JsonErrorHelper;
use Modules\Core\Support\MailerHelper;
use Modules\Core\Support\MpdfHelper;
use Modules\Core\Support\NumberHelper;
use Modules\Core\Support\OrphanHelper;
use Modules\Core\Support\PagerHelper;
use Modules\Core\Support\PaymentsHelper;
use Modules\Core\Support\PdfHelper;
use Modules\Core\Support\RedirectHelper;
use Modules\Core\Support\SettingsHelper;
use Modules\Core\Support\TemplateHelper;
use Modules\Core\Support\TranslationHelper;
use Modules\Core\Support\UserHelper;

// ============================================================================
// Client Helper Functions
// ============================================================================

if ( ! function_exists('format_client')) {
    function format_client($client, $show_title = true): string
    {
        return ClientHelper::format_client($client, $show_title);
    }
}

if ( ! function_exists('format_gender')) {
    function format_gender($gender)
    {
        return ClientHelper::format_gender($gender);
    }
}

// ============================================================================
// Country Helper Functions
// ============================================================================

if ( ! function_exists('get_country_list')) {
    function get_country_list(string $cldr)
    {
        return CountryHelper::get_country_list($cldr);
    }
}

if ( ! function_exists('get_country_name')) {
    function get_country_name($cldr, $countrycode)
    {
        return CountryHelper::get_country_name($cldr, $countrycode);
    }
}

// ============================================================================
// Custom Values Helper Functions
// ============================================================================

if ( ! function_exists('format_date')) {
    function format_date($txt)
    {
        return CustomValuesHelper::format_date($txt);
    }
}

if ( ! function_exists('format_text')) {
    function format_text($txt)
    {
        return CustomValuesHelper::format_text($txt);
    }
}

if ( ! function_exists('format_singlechoice')) {
    function format_singlechoice($txt)
    {
        return CustomValuesHelper::format_singlechoice($txt);
    }
}

if ( ! function_exists('format_multiplechoice')) {
    function format_multiplechoice($txt): string
    {
        return CustomValuesHelper::format_multiplechoice($txt);
    }
}

if ( ! function_exists('format_boolean')) {
    function format_boolean($txt)
    {
        return CustomValuesHelper::format_boolean($txt);
    }
}

if ( ! function_exists('format_avs')) {
    function format_avs($txt)
    {
        return CustomValuesHelper::format_avs($txt);
    }
}

if ( ! function_exists('format_fallback')) {
    function format_fallback($txt)
    {
        return CustomValuesHelper::format_fallback($txt);
    }
}

if ( ! function_exists('print_field')) {
    function print_field($module, $custom_field, array $cv, $class_top = '', $class_bottom = 'controls', $class_label = '', $class_group = 'form-group'): void
    {
        CustomValuesHelper::print_field($module, $custom_field, $cv, $class_top, $class_bottom, $class_label, $class_group);
    }
}

// ============================================================================
// Date Helper Functions
// ============================================================================

if ( ! function_exists('date_formats')) {
    function date_formats(): array
    {
        return DateHelper::dateFormats();
    }
}

if ( ! function_exists('date_from_mysql')) {
    function date_from_mysql($date, $ignore_post_check = false)
    {
        return DateHelper::dateFromMysql($date, $ignore_post_check);
    }
}

if ( ! function_exists('date_from_timestamp')) {
    function date_from_timestamp($timestamp): string
    {
        return DateHelper::dateFromTimestamp($timestamp);
    }
}

if ( ! function_exists('date_to_mysql')) {
    function date_to_mysql($date)
    {
        return DateHelper::dateToMysql($date);
    }
}

if ( ! function_exists('is_date')) {
    function is_date($date): bool
    {
        return DateHelper::isDate($date);
    }
}

if ( ! function_exists('date_format_setting')) {
    function date_format_setting()
    {
        return DateHelper::dateFormatSetting();
    }
}

if ( ! function_exists('date_format_datepicker')) {
    function date_format_datepicker()
    {
        return DateHelper::dateFormatDatepicker();
    }
}

if ( ! function_exists('increment_user_date')) {
    function increment_user_date($date, string $increment): string
    {
        return DateHelper::incrementUserDate($date, $increment);
    }
}

if ( ! function_exists('increment_date')) {
    function increment_date($date, string $increment): string
    {
        return DateHelper::incrementDate($date, $increment);
    }
}

// ============================================================================
// Diacritics Helper Functions
// ============================================================================

if ( ! function_exists('diacritics_seems_utf8')) {
    function diacritics_seems_utf8($str): bool
    {
        return DiacriticsHelper::diacritics_seems_utf8($str);
    }
}

if ( ! function_exists('diacritics_remove_accents')) {
    function diacritics_remove_accents($string)
    {
        return DiacriticsHelper::diacritics_remove_accents($string);
    }
}

if ( ! function_exists('diacritics_remove_diacritics')) {
    function diacritics_remove_diacritics($text): string
    {
        return DiacriticsHelper::diacritics_remove_diacritics($text);
    }
}

// ============================================================================
// Dropzone Helper Functions
// ============================================================================

if ( ! function_exists('_dropzone_html')) {
    function _dropzone_html($read_only = true): void
    {
        DropzoneHelper::_dropzone_html($read_only);
    }
}

if ( ! function_exists('_dropzone_script')) {
    function _dropzone_script($url_key = null, $client_id = 1, $site_url = '', $acceptedExts = null): void
    {
        DropzoneHelper::_dropzone_script($url_key, $client_id, $site_url, $acceptedExts);
    }
}

// ============================================================================
// Echo Helper Functions
// ============================================================================

if ( ! function_exists('htmlsc')) {
    function htmlsc($output): ?string
    {
        return EchoHelper::htmlsc($output);
    }
}

if ( ! function_exists('_htmlsc')) {
    function _htmlsc($output)
    {
        EchoHelper::_htmlsc($output);
    }
}

if ( ! function_exists('_htmle')) {
    function _htmle($output)
    {
        EchoHelper::_htmle($output);
    }
}

if ( ! function_exists('_trans')) {
    function _trans($line, $id = '', $default = null): void
    {
        EchoHelper::_trans($line, $id, $default);
    }
}

if ( ! function_exists('_auto_link')) {
    function _auto_link($str, $type = 'both', $popup = false): void
    {
        EchoHelper::_auto_link($str, $type, $popup);
    }
}

if ( ! function_exists('_csrf_field')) {
    function _csrf_field(): void
    {
        EchoHelper::_csrf_field();
    }
}

if ( ! function_exists('_theme_asset')) {
    function _theme_asset($asset): void
    {
        EchoHelper::_theme_asset($asset);
    }
}

if ( ! function_exists('_core_asset')) {
    function _core_asset($asset): void
    {
        EchoHelper::_core_asset($asset);
    }
}

// ============================================================================
// E-Invoice Helper Functions
// ============================================================================

if ( ! function_exists('generate_xml_invoice_file')) {
    function generate_xml_invoice_file($invoice, $items, string $xml_lib, string $filename, $options): string
    {
        return EInvoiceHelper::generate_xml_invoice_file($invoice, $items, $xml_lib, $filename, $options);
    }
}

if ( ! function_exists('include_rdf')) {
    function include_rdf(string $embedXml, string $urn = 'factur-x'): string
    {
        return EInvoiceHelper::include_rdf($embedXml, $urn);
    }
}

if ( ! function_exists('get_xml_template_files')) {
    function get_xml_template_files(): array
    {
        return EInvoiceHelper::get_xml_template_files();
    }
}

if ( ! function_exists('get_xml_full_name')) {
    function get_xml_full_name(string $xml_id)
    {
        return EInvoiceHelper::get_xml_full_name($xml_id);
    }
}

if ( ! function_exists('get_admin_active_users')) {
    function get_admin_active_users($user_id = ''): array
    {
        return EInvoiceHelper::get_admin_active_users($user_id);
    }
}

if ( ! function_exists('get_req_fields_einvoice')) {
    function get_req_fields_einvoice($client = null, $user_id = ''): object
    {
        return EInvoiceHelper::get_req_fields_einvoice($client, $user_id);
    }
}

if ( ! function_exists('get_einvoice_usage')) {
    function get_einvoice_usage($invoice, array $items, $full = true): object
    {
        return EInvoiceHelper::get_einvoice_usage($invoice, $items, $full);
    }
}

if ( ! function_exists('get_items_tax_usages')) {
    function get_items_tax_usages($items): array
    {
        return EInvoiceHelper::get_items_tax_usages($items);
    }
}

if ( ! function_exists('items_tax_usages_bad')) {
    function items_tax_usages_bad($items): mixed
    {
        return EInvoiceHelper::items_tax_usages_bad($items);
    }
}

// ============================================================================
// Invoice Helper Functions
// ============================================================================

if ( ! function_exists('invoice_logo')) {
    function invoice_logo(): string
    {
        return InvoiceHelper::invoice_logo();
    }
}

if ( ! function_exists('invoice_logo_pdf')) {
    function invoice_logo_pdf(): string
    {
        return InvoiceHelper::invoice_logo_pdf();
    }
}

if ( ! function_exists('invoice_genCodeline')) {
    function invoice_genCodeline(string $slipType, $amount, $rnumb, $subNumb): string
    {
        return InvoiceHelper::invoice_genCodeline($slipType, $amount, $rnumb, $subNumb);
    }
}

if ( ! function_exists('invoice_recMod10')) {
    function invoice_recMod10($in): int
    {
        return InvoiceHelper::invoice_recMod10($in);
    }
}

if ( ! function_exists('invoice_qrcode')) {
    function invoice_qrcode($invoice_id): string
    {
        return InvoiceHelper::invoice_qrcode($invoice_id);
    }
}

// ============================================================================
// JSON Error Helper Functions
// ============================================================================

if ( ! function_exists('json_errors')) {
    function json_errors(): array
    {
        return JsonErrorHelper::json_errors();
    }
}

// ============================================================================
// Mailer Helper Functions
// ============================================================================

if ( ! function_exists('mailer_configured')) {
    function mailer_configured(): bool
    {
        return MailerHelper::mailer_configured();
    }
}

if ( ! function_exists('email_quote_status')) {
    function email_quote_status(string $quote_id, $status)
    {
        return MailerHelper::email_quote_status($quote_id, $status);
    }
}

if ( ! function_exists('validate_email_address')) {
    function validate_email_address(string $email): bool
    {
        return MailerHelper::validate_email_address($email);
    }
}

if ( ! function_exists('check_mail_errors')) {
    function check_mail_errors(array $errors = [], $redirect = ''): void
    {
        MailerHelper::check_mail_errors($errors, $redirect);
    }
}

if ( ! function_exists('email_invoice')) {
    function email_invoice(
        string $invoice_id,
        $invoice_template,
        array $from,
        $to,
        $subject,
        $body,
        $cc = null,
        $bcc = null,
        $attachments = null
    ): bool {
        return MailerHelper::email_invoice($invoice_id, $invoice_template, $from, $to, $subject, $body, $cc, $bcc, $attachments);
    }
}

if ( ! function_exists('email_quote')) {
    function email_quote(
        string $quote_id,
        $quote_template,
        array $from,
        $to,
        $subject,
        $body,
        $cc = null,
        $bcc = null,
        $attachments = null
    ): bool {
        return MailerHelper::email_quote($quote_id, $quote_template, $from, $to, $subject, $body, $cc, $bcc, $attachments);
    }
}

// ============================================================================
// mPDF Helper Functions
// ============================================================================

if ( ! function_exists('pdf_create')) {
    function pdf_create(
        $html,
        $filename,
        $stream,
        $password = null,
        $isInvoice = false,
        $pdfFormat = '',
        $pdfOrientation = ''
    ) {
        return MpdfHelper::pdf_create($html, $filename, $stream, $password, $isInvoice, $pdfFormat, $pdfOrientation);
    }
}

// ============================================================================
// Number Helper Functions
// ============================================================================

if ( ! function_exists('format_currency')) {
    function format_currency($amount): string
    {
        return NumberHelper::format_currency($amount);
    }
}

if ( ! function_exists('format_amount')) {
    function format_amount($amount = null)
    {
        return NumberHelper::format_amount($amount);
    }
}

if ( ! function_exists('format_quantity')) {
    function format_quantity($amount = null)
    {
        return NumberHelper::format_quantity($amount);
    }
}

if ( ! function_exists('standardize_amount')) {
    function standardize_amount($amount)
    {
        return NumberHelper::standardize_amount($amount);
    }
}

// ============================================================================
// Orphan Helper Functions
// ============================================================================

if ( ! function_exists('delete_orphans')) {
    function delete_orphans(): void
    {
        OrphanHelper::delete_orphans();
    }
}

// ============================================================================
// Pager Helper Functions
// ============================================================================

if ( ! function_exists('pager')) {
    function pager(string $base_url, $model): string
    {
        return PagerHelper::pager($base_url, $model);
    }
}

// ============================================================================
// Payments Helper Functions
// ============================================================================

if ( ! function_exists('get_currencies')) {
    function get_currencies(): array
    {
        return PaymentsHelper::get_currencies();
    }
}

// ============================================================================
// PDF Helper Functions
// ============================================================================

if ( ! function_exists('discount_global_print_in_pdf')) {
    function discount_global_print_in_pdf($obj, $show_item_discounts, string $is = 'invoice'): void
    {
        PdfHelper::discount_global_print_in_pdf($obj, $show_item_discounts, $is);
    }
}

if ( ! function_exists('generate_invoice_pdf')) {
    function generate_invoice_pdf($invoice_id, $stream = true, $invoice_template = null, $is_guest = null)
    {
        return PdfHelper::generate_invoice_pdf($invoice_id, $stream, $invoice_template, $is_guest);
    }
}

if ( ! function_exists('generate_invoice_sumex')) {
    function generate_invoice_sumex($invoice_id, $stream = true, $invoice_template = null, $client = false)
    {
        return PdfHelper::generate_invoice_sumex($invoice_id, $stream, $invoice_template, $client);
    }
}

if ( ! function_exists('generate_quote_pdf')) {
    function generate_quote_pdf($quote_id, $stream = true, $quote_template = null)
    {
        return PdfHelper::generate_quote_pdf($quote_id, $stream, $quote_template);
    }
}

// ============================================================================
// Redirect Helper Functions
// ============================================================================

if ( ! function_exists('redirect_to')) {
    function redirect_to($fallback_url_string, $redirect = true)
    {
        return RedirectHelper::redirect_to($fallback_url_string, $redirect);
    }
}

if ( ! function_exists('redirect_to_set')) {
    function redirect_to_set(): void
    {
        RedirectHelper::redirect_to_set();
    }
}

// ============================================================================
// Settings Helper Functions
// ============================================================================

if ( ! function_exists('get_setting')) {
    function get_setting($setting_key, $default = '', $escape = false)
    {
        return SettingsHelper::getSetting($setting_key, $default, $escape);
    }
}

if ( ! function_exists('get_gateway_settings')) {
    function get_gateway_settings($gateway)
    {
        return SettingsHelper::getGatewaySettings($gateway);
    }
}

if ( ! function_exists('check_select')) {
    function check_select($value1, $value2 = null, $operator = '==', $checked = false): void
    {
        SettingsHelper::checkSelect($value1, $value2, $operator, $checked);
    }
}

// ============================================================================
// Template Helper Functions
// ============================================================================

if ( ! function_exists('parse_template')) {
    function parse_template($object, $body)
    {
        return TemplateHelper::parse_template($object, $body);
    }
}

if ( ! function_exists('get_invoice_status')) {
    function get_invoice_status($id)
    {
        return TemplateHelper::get_invoice_status($id);
    }
}

if ( ! function_exists('select_pdf_invoice_template')) {
    function select_pdf_invoice_template($invoice)
    {
        return TemplateHelper::select_pdf_invoice_template($invoice);
    }
}

if ( ! function_exists('select_email_invoice_template')) {
    function select_email_invoice_template($invoice)
    {
        return TemplateHelper::select_email_invoice_template($invoice);
    }
}

// ============================================================================
// Translation Helper Functions
// ============================================================================

if ( ! function_exists('trans')) {
    function trans($line, ?string $id = '', $default = null)
    {
        return TranslationHelper::trans($line, $id, $default);
    }
}

if ( ! function_exists('set_language')) {
    function set_language($language): void
    {
        TranslationHelper::setLanguage($language);
    }
}

if ( ! function_exists('get_available_languages')) {
    function get_available_languages()
    {
        return TranslationHelper::getAvailableLanguages();
    }
}

// ============================================================================
// User Helper Functions
// ============================================================================

if ( ! function_exists('format_user')) {
    function format_user($user): string
    {
        return UserHelper::format_user($user);
    }
}
