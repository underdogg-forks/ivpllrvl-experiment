<?php

namespace Modules\Invoices\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Core\Models\CustomValue;
use Modules\Core\Services\CustomFieldService;
use Modules\Core\Services\CustomValueService;
use Modules\Core\Services\UserService;
use Modules\Core\Support\PdfHelper;
use Modules\Core\Support\TranslationHelper;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceAmountService;
use Modules\Invoices\Services\InvoiceItemService;
use Modules\Invoices\Services\InvoiceService;
use Modules\Invoices\Services\InvoiceTaxRateService;
use Modules\Payments\Services\PaymentMethodService;
use Modules\Products\Services\TaxRateService;
use Modules\Products\Services\UnitService;
use Modules\Projects\Services\TaskService;
use Sumex;

/**
 * InvoicesController.
 *
 * Handles invoice viewing, status filtering, PDF generation, and management operations
 */
class InvoicesController
{
    public function __construct(
        protected UserService $userService,
        protected InvoiceService $invoiceService,
        protected InvoiceItemService $invoiceItemService,
        protected InvoiceTaxRateService $invoiceTaxRateService,
        protected CustomFieldService $customFieldService,
        protected CustomValueService $customValueService,
        protected TaxRateService $taxRateService,
        protected UnitService $unitService,
        protected PaymentMethodService $paymentMethodService,
        protected TaskService $taskService
    ) {}

    /**
     * Redirect to all invoices status view.
     *
     * @return RedirectResponse
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 29
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('invoices.status', ['status' => 'all']);
    }

    /**
     * Display invoices filtered by status with pagination.
     *
     * @param string $status Invoice status filter (all, draft, sent, viewed, paid, overdue)
     * @param int    $page   Page number for pagination
     *
     * @return View
     *
     * @legacy-function status
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 38
     */
    public function status(string $status = 'all', int $page = 0): View
    {
        // Get paginated invoices with relationships from service
        $invoices = $this->invoiceService->getAllWithRelations(['client', 'user'], $status, 15);

        $data = [
            'invoices'           => $invoices,
            'status'             => $status,
            'filter_display'     => true,
            'filter_placeholder' => TranslationHelper::trans('filter_invoices'),
            'filter_method'      => 'filter_invoices',
            'invoice_statuses'   => app(InvoiceService::class)->getStatuses(),
        ];

        return view('invoices::index', $data);
    }

    /**
     * Display archived invoices.
     *
     * @return View
     *
     * @legacy-function archive
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 77
     */
    public function archive(): View
    {
        $invoiceArray = Invoice::getArchives(0);

        $data = [
            'filter_display'     => true,
            'filter_placeholder' => TranslationHelper::trans('filter_archives'),
            'filter_method'      => 'filter_archives',
            'invoices_archive'   => $invoiceArray,
        ];

        return view('invoices::archive', $data);
    }

    /**
     * Download an archived invoice PDF file.
     *
     * @param string $invoice Filename of the invoice to download
     *
     * @return Response
     *
     * @legacy-function download
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 92
     */
    public function download(string $invoice): Response
    {
        $safeBaseDir = realpath(config('uploads.archive_folder'));

        $fileName = urldecode(basename($invoice));
        $filePath = realpath($safeBaseDir . DIRECTORY_SEPARATOR . $fileName);

        // Security: Prevent directory traversal
        if ($filePath === false || ! str_starts_with($filePath, $safeBaseDir)) {
            logger()->error('Invalid file access attempt: ' . $fileName);
            abort(404);
        }

        if ( ! file_exists($filePath)) {
            logger()->error('File not found while downloading: ' . $filePath);
            abort(404);
        }

        return response()->download($filePath);
    }

    /**
     * Display detailed invoice view with items, taxes, and custom fields.
     *
     * @param int $invoiceId Invoice ID to view
     *
     * @return View
     *
     * @legacy-function view
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 120
     */
    public function view(int $invoiceId): View
    {
        $invoice = $this->invoiceService->findWithRelationsOrFail(
            $invoiceId,
            ['client', 'user', 'invoiceGroup', 'items', 'taxRates', 'payments']
        );

        // Get custom fields and values
        $customFields = $this->customFieldService->getByTable('ip_invoice_custom');
        $customValues = [];

        foreach ($customFields as $customField) {
            if (in_array($customField->custom_field_type, CustomValue::customValueFields())) {
                $values                                      = $this->customValueService->getByFieldId($customField->custom_field_id);
                $customValues[$customField->custom_field_id] = $values;
            }
        }

        // Check for payment custom fields
        $paymentCfExist = $this->customFieldService->existsForTable('ip_payment_custom') ? 'yes' : 'no';

        // Get items
        $items = $this->invoiceItemService->getItemsByInvoiceId($invoiceId);

        // Check if user change is allowed (more than one admin user)
        $changeUser = $this->userService->hasMultipleActiveAdmins();

        $data = [
            'invoice'           => $invoice,
            'items'             => $items,
            'invoice_id'        => $invoiceId,
            'change_user'       => $changeUser,
            'tax_rates'         => $this->taxRateService->getAll(),
            'invoice_tax_rates' => $this->invoiceTaxRateService->getTaxRatesByInvoiceId($invoiceId),
            'units'             => $this->unitService->getAll(),
            'payment_methods'   => $this->paymentMethodService->getAllOrdered(),
            'custom_fields'     => $customFields,
            'custom_values'     => $customValues,
            'custom_js_vars'    => [
                'currency_symbol'           => config('settings.currency_symbol'),
                'currency_symbol_placement' => config('settings.currency_symbol_placement'),
                'decimal_point'             => config('settings.decimal_point'),
            ],
            'invoice_statuses'   => $this->invoiceService->getStatuses(),
            'payment_cf_exist'   => $paymentCfExist,
            'legacy_calculation' => config('legacy_calculation'),
        ];

        $viewTemplate = $invoice->sumex_id ? 'invoices::view_sumex' : 'invoices::view';

        return view($viewTemplate, $data);
    }

    /**
     * Delete an invoice (only drafts or if deletion is enabled).
     *
     * @param int $invoiceId Invoice ID to delete
     *
     * @return RedirectResponse
     *
     * @legacy-function delete
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 228
     */
    public function delete(int $invoiceId): RedirectResponse
    {
        $invoice       = $this->invoiceService->findOrFail($invoiceId);
        $invoiceStatus = $invoice->invoice_status_id;

        if ($invoiceStatus == 1 || config('settings.enable_invoice_deletion') === true) {
            // If invoice refers to tasks, mark them back to 'Complete'
            $this->taskService->updateByInvoiceId($invoiceId, ['task_status' => 3]); // 3 = Complete

            // Delete the invoice
            $this->invoiceService->deleteInvoice($invoiceId);
        } else {
            session()->flash('alert_error', TranslationHelper::trans('invoice_deletion_forbidden'));
        }

        return redirect()->route('invoices.index');
    }

    /**
     * Generate and stream/download invoice PDF.
     *
     * @param int         $invoiceId       Invoice ID
     * @param bool        $stream          Whether to stream (true) or download (false)
     * @param string|null $invoiceTemplate Optional template name
     *
     * @return Response
     *
     * @legacy-function generate_pdf
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 254
     */
    public function generatePdf(int $invoiceId, bool $stream = true, ?string $invoiceTemplate = null): Response
    {
        if (config('settings.mark_invoices_sent_pdf') == 1) {
            $invoiceService = app(InvoiceService::class);
            $invoiceService->generateInvoiceNumberIfApplicable($invoiceId);
            $invoiceService->markSent($invoiceId);
        }

        // Generate PDF using helper
        $pdfContent = PdfHelper::generate_invoice_pdf($invoiceId, $stream, $invoiceTemplate, null);

        if ($stream) {
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="invoice-' . $invoiceId . '.pdf"');
        }

        return response()->download($pdfContent);
    }

    /**
     * Generate XML invoice file (e-invoicing).
     *
     * @param int $invoiceId Invoice ID
     *
     * @return Response
     *
     * @legacy-function generate_xml
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 266
     */
    public function generateXml(int $invoiceId): Response
    {
        $invoice = $this->invoiceService->findOrFail($invoiceId);
        $items   = $this->invoiceItemService->getItemsByInvoiceId($invoiceId);

        // Check e-invoice usage
        $einvoice = get_einvoice_usage($invoice, $items, false);
        if ( ! $einvoice->user) {
            abort(404);
        }

        // Generate XML file
        $xmlId     = $einvoice->name;
        $options   = [];
        $generator = $xmlId;
        $path      = app_path('helpers/XMLconfigs/');

        if ($xmlId && file_exists($path . $xmlId . '.php')) {
            include $path . $xmlId . '.php';
            $embedXml  = $xml_setting['embedXML'] ?? false;
            $XMLname   = $xml_setting['XMLname'] ?? 'invoice';
            $options   = $xml_setting['options'] ?? [];
            $generator = $xml_setting['generator'] ?? $generator;
        }

        $filename = TranslationHelper::trans('invoice') . '_' . str_replace(['\\', '/'], '_', $invoice->invoice_number);
        $xmlPath  = generate_xml_invoice_file($invoice, $items, $generator, $filename, $options);

        $content = file_get_contents($xmlPath);
        unlink($xmlPath);

        return response($content)->header('Content-Type', 'text/xml');
    }

    /**
     * Generate SUMEX PDF for Swiss medical billing.
     *
     * @param int $invoiceId Invoice ID
     *
     * @return Response
     *
     * @legacy-function generate_sumex_pdf
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 301
     */
    public function generateSumexPdf(int $invoiceId): Response
    {
        $pdfContent = generate_invoice_sumex($invoiceId);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="sumex-invoice-' . $invoiceId . '.pdf"');
    }

    /**
     * Generate SUMEX copy PDF.
     *
     * @param int $invoiceId Invoice ID
     *
     * @return Response
     *
     * @legacy-function generate_sumex_copy
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 308
     */
    public function generateSumexCopy(int $invoiceId): Response
    {
        $invoice = $this->invoiceService->findOrFail($invoiceId);
        $items   = $this->invoiceItemService->getItemsByInvoiceId($invoiceId);

        $sumex = new Sumex([
            'invoice' => $invoice,
            'items'   => $items,
            'options' => [
                'copy'   => '1',
                'storno' => '0',
            ],
        ]);

        return response($sumex->pdf())
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Delete invoice tax rate and recalculate invoice amounts.
     *
     * @param int $invoiceId        Invoice ID
     * @param int $invoiceTaxRateId Tax rate ID to delete
     *
     * @return RedirectResponse
     *
     * @legacy-function delete_invoice_tax
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 324
     */
    public function deleteInvoiceTax(int $invoiceId, int $invoiceTaxRateId): RedirectResponse
    {
        $this->invoiceTaxRateService->delete($invoiceTaxRateId);

        // Recalculate invoice amounts
        $invoiceAmountService = app(InvoiceAmountService::class);
        $globalDiscount       = ['item' => $invoiceAmountService->getGlobalDiscount($invoiceId)];
        $invoiceAmountService->calculate($invoiceId, $globalDiscount);

        return redirect()->route('invoices.view', ['invoiceId' => $invoiceId]);
    }

    /**
     * Recalculate all invoices in the system.
     *
     * @return RedirectResponse
     *
     * @legacy-function recalculate_all_invoices
     *
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     *
     * @legacy-line 337
     */
    public function recalculateAllInvoices(): RedirectResponse
    {
        $invoiceIds = Invoice::query()->select('invoice_id')->get();

        foreach ($invoiceIds as $invoice) {
            $invoiceAmountService = app(InvoiceAmountService::class);
            $globalDiscount       = ['item' => $invoiceAmountService->getGlobalDiscount($invoice->invoice_id)];
            $invoiceAmountService->calculate($invoice->invoice_id, $globalDiscount);
        }

        session()->flash('alert_success', TranslationHelper::trans('all_invoices_recalculated'));

        return redirect()->route('invoices.index');
    }
}
