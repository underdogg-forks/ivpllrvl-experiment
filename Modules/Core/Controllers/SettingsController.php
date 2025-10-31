<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\Setting;

class SettingsController
{
    /** @legacy-file application/modules/settings/controllers/Settings.php */
    public function index(): \Illuminate\View\View
    {
        $settings = Setting::query()->get()->pluck('setting_value', 'setting_key')->toArray();

        return view('core::settings_index', ['settings' => $settings]);
    }

    public function save()
    {
        if (request()->isMethod('post')) {
            // Define allowed setting keys for security
            $allowedSettings = [
                'company_name', 'company_address', 'company_city', 'company_state',
                'company_zip', 'company_country', 'company_phone', 'company_fax',
                'company_email', 'company_web', 'invoice_logo', 'pdf_watermark',
                'default_language', 'default_date_format', 'default_currency',
                'default_invoice_group', 'default_invoice_terms', 'default_quote_terms',
                'default_payment_method', 'default_invoice_template', 'default_quote_template',
                'invoices_due_after', 'quotes_expire_after', 'disable_read_only',
                'enable_invoice_deletion', 'cron_key', 'merchant_enabled',
                'merchant_driver', 'merchant_api_username', 'merchant_api_password',
                'merchant_api_signature', 'merchant_test_mode', 'email_send_method',
                'email_smtp_server', 'email_smtp_port', 'email_smtp_username',
                'email_smtp_password', 'email_smtp_encryption', 'email_from_address',
                'email_from_name', 'bcc_mails_to_admin', 'automatic_email_on_recur',
                'sumex_enable', 'sumex_canton', 'sumex_slp', 'tax_rate_decimal_places',
                'einvoicing', 'einvoicing_api_key', 'einvoicing_api_secret',
            ];

            $settings = request()->only($allowedSettings);

            foreach ($settings as $key => $value) {
                Setting::query()->updateOrCreate(
                    ['setting_key' => $key],
                    ['setting_value' => $value]
                );
            }

            return redirect()->route('settings.index')->with('alert_success', trans('settings_successfully_saved'));
        }

        return redirect()->route('settings.index');
    }
}
