<?php

namespace Modules\Core\Controllers;

use AllowDynamicProperties;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\TaxRate;
use Modules\Core\Services\EmailTemplateService;
use Modules\Core\Services\CustomFieldService;
use Modules\Invoices\app\Models\InvoiceGroup;
use Modules\Invoices\Models\Template;
use Modules\Invoices\Services\InvoiceGroupService;
use Modules\Invoices\Services\TemplateService;
use Modules\Payments\app\Models\PaymentMethod;
use Modules\Payments\Services\PaymentMethodService;
use Modules\Products\Services\TaxRateService;

#[AllowDynamicProperties]
class SettingsController extends AdminController
{
    /**
     * InvoiceGroup service instance.
     *
     * @var InvoiceGroupService
     */
    protected InvoiceGroupService $invoiceGroupService;

    /**
     * TaxRate service instance.
     *
     * @var TaxRateService
     */
    protected TaxRateService $taxRateService;

    /**
     * EmailTemplate service instance.
     *
     * @var EmailTemplateService
     */
    protected EmailTemplateService $emailTemplateService;

    /**
     * PaymentMethod service instance.
     *
     * @var PaymentMethodService
     */
    protected PaymentMethodService $paymentMethodService;

    /**
     * CustomField service instance.
     *
     * @var CustomFieldService
     */
    protected CustomFieldService $customFieldService;

    /**
     * SettingsController constructor.
     */
    public function __construct(
        InvoiceGroupService $invoiceGroupService,
        TaxRateService $taxRateService,
        EmailTemplateService $emailTemplateService,
        PaymentMethodService $paymentMethodService,
        CustomFieldService $customFieldService
    ) {
        parent::__construct();
        $this->invoiceGroupService  = $invoiceGroupService;
        $this->taxRateService       = $taxRateService;
        $this->emailTemplateService = $emailTemplateService;
        $this->paymentMethodService = $paymentMethodService;
        $this->customFieldService   = $customFieldService;
    }

    /**
     * @originalName index
     *
     * @originalFile SettingsController.php
     */
    public function index(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
    {
        // Load payment gateways and number formats from config
        $gateways        = config('payment_gateways');
        $number_formats  = config('number_formats');
        $settingsService = new SettingsService();

        if ($request->isMethod('post') && $request->input('settings')) {
            $settings = $request->input('settings');
            // Save settings
            foreach ($settings as $key => $value) {
                $passwordKey = $key . '_field_is_password';
                $amountKey   = $key . '_field_is_amount';
                if (str_contains($key, 'field_is_password') || str_contains($key, 'field_is_amount')) {
                    continue;
                }
                if (isset($settings[$passwordKey]) && empty($value)) {
                    continue;
                }
                if (isset($settings[$passwordKey]) && $value !== '') {
                    $settingsService->save($key, encrypt(mb_trim($value)));
                } elseif (isset($settings[$amountKey])) {
                    $settingsService->save($key, (float) $value);
                } else {
                    $settingsService->save($key, $value);
                }
                if ($key === 'number_format') {
                    $settingsService->save('decimal_point', $number_formats[$value]['decimal_point'] ?? '.');
                    $settingsService->save('thousands_separator', $number_formats[$value]['thousands_separator'] ?? ',');
                }
            }
            // Handle invoice logo upload
            if ($request->hasFile('invoice_logo')) {
                $file     = $request->file('invoice_logo');
                $filename = $file->store('uploads', 'public');
                $settingsService->save('invoice_logo', basename($filename));
            }
            // Handle login logo upload
            if ($request->hasFile('login_logo')) {
                $file     = $request->file('login_logo');
                $filename = $file->store('uploads', 'public');
                $settingsService->save('login_logo', basename($filename));
            }
            Session::flash('alert_success', trans('settings_successfully_saved'));

            return redirect()->route('settings.index');
        }
        // Load required resources using Eloquent
        $invoice_groups  = InvoiceGroup::all();
        $tax_rates       = $this->taxRateService->getAll();
        $email_templates = EmailTemplate::all();
        $payment_methods = $this->paymentMethodService->getAllOrdered();
        $templates       = Template::all();
        $custom_fields   = \Modules\CustomFields\Models\CustomField::all();

        return view('settings.index', [
            'gateways'        => $gateways,
            'number_formats'  => $number_formats,
            'invoice_groups'  => $invoice_groups,
            'tax_rates'       => $tax_rates,
            'email_templates' => $email_templates,
            'payment_methods' => $payment_methods,
            'templates'       => $templates,
            'custom_fields'   => $custom_fields,
        ]);
    }

    /**
     * @originalName removeLogo
     *
     * @originalFile SettingsController.php
     */
    public function removeLogo(Request $request, string $type): \Illuminate\Http\RedirectResponse
    {
        $settingsService = new SettingsService();
        $logo            = $settingsService->get($type . '_logo');
        if ($logo) {
            Storage::disk('public')->delete('uploads/' . $logo);
            $settingsService->save($type . '_logo', '');
            Session::flash('alert_success', trans($type . '_logo_removed'));
        }

        return redirect()->route('settings.index');
    }
}
