<?php

namespace Modules\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\TaxRate;
use Modules\Core\Services\EmailTemplateService;
use Modules\Core\Services\CustomFieldService;
use Modules\Core\Services\SettingsService;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Models\Template;
use Modules\Invoices\Services\InvoiceGroupService;
use Modules\Invoices\Services\TemplateService;
use Modules\Payments\Models\PaymentMethod;
use Modules\Payments\Services\PaymentMethodService;
use Modules\Products\Services\TaxRateService;

/**
 * SettingsController
 *
 * Manages application settings and configuration
 *
 * @legacy-file application/modules/settings/controllers/Settings.php
 */
class SettingsController
{
    protected InvoiceGroupService $invoiceGroupService;
    protected TaxRateService $taxRateService;
    protected EmailTemplateService $emailTemplateService;
    protected PaymentMethodService $paymentMethodService;
    protected CustomFieldService $customFieldService;
    protected SettingsService $settingsService;

    /**
     * Initialize the SettingsController with dependency injection.
     *
     * @param InvoiceGroupService $invoiceGroupService
     * @param TaxRateService $taxRateService
     * @param EmailTemplateService $emailTemplateService
     * @param PaymentMethodService $paymentMethodService
     * @param CustomFieldService $customFieldService
     * @param SettingsService $settingsService
     */
    public function __construct(
        InvoiceGroupService $invoiceGroupService,
        TaxRateService $taxRateService,
        EmailTemplateService $emailTemplateService,
        PaymentMethodService $paymentMethodService,
        CustomFieldService $customFieldService,
        SettingsService $settingsService
    ) {
        $this->invoiceGroupService = $invoiceGroupService;
        $this->taxRateService = $taxRateService;
        $this->emailTemplateService = $emailTemplateService;
        $this->paymentMethodService = $paymentMethodService;
        $this->customFieldService = $customFieldService;
        $this->settingsService = $settingsService;
    }

    /**
     * Display and process settings form.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/settings/controllers/Settings.php
     */
    public function index(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
    {
        // Load payment gateways and number formats from config
        $gateways = config('payment_gateways');
        $number_formats = config('number_formats');

        if ($request->isMethod('post') && $request->input('settings')) {
            $settings = $request->input('settings');
            // Save settings
            foreach ($settings as $key => $value) {
                $passwordKey = $key . '_field_is_password';
                $amountKey = $key . '_field_is_amount';
                if (str_contains($key, 'field_is_password') || str_contains($key, 'field_is_amount')) {
                    continue;
                }
                if (isset($settings[$passwordKey]) && empty($value)) {
                    continue;
                }
                if (isset($settings[$passwordKey]) && $value !== '') {
                    $this->settingsService->save($key, encrypt(mb_trim($value)));
                } elseif (isset($settings[$amountKey])) {
                    $this->settingsService->save($key, (float) $value);
                } else {
                    $this->settingsService->save($key, $value);
                }
                if ($key === 'number_format') {
                    $this->settingsService->save('decimal_point', $number_formats[$value]['decimal_point'] ?? '.');
                    $this->settingsService->save('thousands_separator', $number_formats[$value]['thousands_separator'] ?? ',');
                }
            }
            // Handle invoice logo upload
            if ($request->hasFile('invoice_logo')) {
                $file = $request->file('invoice_logo');
                $filename = $file->store('uploads', 'public');
                $this->settingsService->save('invoice_logo', basename($filename));
            }
            // Handle login logo upload
            if ($request->hasFile('login_logo')) {
                $file = $request->file('login_logo');
                $filename = $file->store('uploads', 'public');
                $this->settingsService->save('login_logo', basename($filename));
            }
            Session::flash('alert_success', trans('settings_successfully_saved'));

            return redirect()->route('settings.index');
        }
        // Load required resources using Eloquent
        $invoice_groups = InvoiceGroup::query()->get();
        $tax_rates = $this->taxRateService->getAll();
        $email_templates = EmailTemplate::query()->get();
        $payment_methods = $this->paymentMethodService->getAllOrdered();
        $templates = Template::query()->get();
        $custom_fields = \Modules\CustomFields\Models\CustomField::query()->get();

        return view('core::settings_index', [
            'gateways' => $gateways,
            'number_formats' => $number_formats,
            'invoice_groups' => $invoice_groups,
            'tax_rates' => $tax_rates,
            'email_templates' => $email_templates,
            'payment_methods' => $payment_methods,
            'templates' => $templates,
            'custom_fields' => $custom_fields,
        ]);
    }

    /**
     * Remove a logo (invoice or login).
     *
     * @param Request $request
     * @param string $type Logo type (invoice or login)
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function removeLogo
     * @legacy-file application/modules/settings/controllers/Settings.php
     */
    public function removeLogo(Request $request, string $type): \Illuminate\Http\RedirectResponse
    {
        $logo = $this->settingsService->get($type . '_logo');
        if ($logo) {
            Storage::disk('public')->delete('uploads/' . $logo);
            $this->settingsService->save($type . '_logo', '');
            Session::flash('alert_success', trans($type . '_logo_removed'));
        }

        return redirect()->route('settings.index');
    }
}
