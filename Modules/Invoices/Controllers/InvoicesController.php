<?php

declare(strict_types=1);

namespace Modules\Invoices\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\Item;
use Modules\Invoices\Models\InvoiceTaxRate;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Products\Models\TaxRate;
use Modules\Products\Models\Unit;
use Modules\Payments\Models\PaymentMethod;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomValue;
use Modules\Core\Models\InvoiceCustom;
use Modules\Crm\Models\Task;

/**
 * InvoicesController
 * 
 * Handles invoice viewing, status filtering, PDF generation, and management operations
 */
class InvoicesController
{
    /**
     * Redirect to all invoices status view
     * 
     * @return RedirectResponse
     * 
     * @legacy-function index
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 29
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('invoices.status', ['status' => 'all']);
    }
    
    /**
     * Display invoices filtered by status with pagination
     * 
     * @param string $status Invoice status filter (all, draft, sent, viewed, paid, overdue)
     * @param int $page Page number for pagination
     * @return View
     * 
     * @legacy-function status
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 38
     */
    public function status(string $status = 'all', int $page = 0): View
    {
        // Build query based on status
        $query = Invoice::with(['client', 'user']);
        
        match ($status) {
            'draft' => $query->draft(),
            'sent' => $query->sent(),
            'viewed' => $query->viewed(),
            'paid' => $query->paid(),
            'overdue' => $query->overdue(),
            default => $query
        };
        
        // Paginate results
        $invoices = $query->paginate(15);
        
        $data = [
            'invoices' => $invoices,
            'status' => $status,
            'filter_display' => true,
            'filter_placeholder' => trans('filter_invoices'),
            'filter_method' => 'filter_invoices',
            'invoice_statuses' => Invoice::statuses(),
        ];
        
        return view('invoices::index', $data);
    }
    
    /**
     * Display archived invoices
     * 
     * @return View
     * 
     * @legacy-function archive
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 77
     */
    public function archive(): View
    {
        $invoiceArray = Invoice::getArchives(0);
        
        $data = [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_archives'),
            'filter_method' => 'filter_archives',
            'invoices_archive' => $invoiceArray,
        ];
        
        return view('invoices::archive', $data);
    }
    
    /**
     * Download an archived invoice PDF file
     * 
     * @param string $invoice Filename of the invoice to download
     * @return Response
     * 
     * @legacy-function download
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 92
     */
    public function download(string $invoice): Response
    {
        $safeBaseDir = realpath(config('uploads.archive_folder'));
        
        $fileName = urldecode(basename($invoice));
        $filePath = realpath($safeBaseDir . DIRECTORY_SEPARATOR . $fileName);
        
        // Security: Prevent directory traversal
        if ($filePath === false || !str_starts_with($filePath, $safeBaseDir)) {
            logger()->error('Invalid file access attempt: ' . $fileName);
            abort(404);
        }
        
        if (!file_exists($filePath)) {
            logger()->error('File not found while downloading: ' . $filePath);
            abort(404);
        }
        
        return response()->download($filePath);
    }
    
    /**
     * Display detailed invoice view with items, taxes, and custom fields
     * 
     * @param int $invoiceId Invoice ID to view
     * @return View
     * 
     * @legacy-function view
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 120
     */
    public function view(int $invoiceId): View
    {
        $invoice = Invoice::with(['client', 'user', 'invoiceGroup', 'items', 'taxRates', 'payments'])
            ->findOrFail($invoiceId);
        
        // Get custom fields and values
        $fields = InvoiceCustom::query()->where('invoice_id', $invoiceId)->get();
        $customFields = CustomField::query()->where('custom_field_table', 'ip_invoice_custom')->get();
        $customValues = [];
        
        foreach ($customFields as $customField) {
            if (in_array($customField->custom_field_type, CustomValue::customValueFields())) {
                $values = CustomValue::query()->where('custom_field_id', $customField->custom_field_id)->get();
                $customValues[$customField->custom_field_id] = $values;
            }
        }
        
        // Check for payment custom fields
        $paymentCfExist = CustomField::query()->where('custom_field_table', 'ip_payment_custom')->exists() ? 'yes' : 'no';
        
        // Get items
        $items = Item::query()->where('invoice_id', $invoiceId)->orderBy('item_order')->get();
        
        // Check if user change is allowed (more than one admin user)
        $changeUser = \DB::table('ip_users')
            ->where('user_type', 1)
            ->where('user_active', 1)
            ->count() > 1;
        
        $data = [
            'invoice' => $invoice,
            'items' => $items,
            'invoice_id' => $invoiceId,
            'change_user' => $changeUser,
            'tax_rates' => TaxRate::query()->all(),
            'invoice_tax_rates' => InvoiceTaxRate::query()->where('invoice_id', $invoiceId)->get(),
            'units' => Unit::query()->all(),
            'payment_methods' => PaymentMethod::query()->all(),
            'custom_fields' => $customFields,
            'custom_values' => $customValues,
            'custom_js_vars' => [
                'currency_symbol' => config('settings.currency_symbol'),
                'currency_symbol_placement' => config('settings.currency_symbol_placement'),
                'decimal_point' => config('settings.decimal_point'),
            ],
            'invoice_statuses' => Invoice::statuses(),
            'payment_cf_exist' => $paymentCfExist,
            'legacy_calculation' => config('legacy_calculation'),
        ];
        
        $viewTemplate = $invoice->sumex_id ? 'invoices::view_sumex' : 'invoices::view';
        
        return view($viewTemplate, $data);
    }
    
    /**
     * Delete an invoice (only drafts or if deletion is enabled)
     * 
     * @param int $invoiceId Invoice ID to delete
     * @return RedirectResponse
     * 
     * @legacy-function delete
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 228
     */
    public function delete(int $invoiceId): RedirectResponse
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);
        $invoiceStatus = $invoice->invoice_status_id;
        
        if ($invoiceStatus == 1 || config('settings.enable_invoice_deletion') === true) {
            // If invoice refers to tasks, mark them back to 'Complete'
            Task::query()->where('invoice_id', $invoiceId)
                ->update(['task_status' => 3]); // 3 = Complete
            
            // Delete the invoice
            Invoice::deleteInvoice($invoiceId);
        } else {
            session()->flash('alert_error', trans('invoice_deletion_forbidden'));
        }
        
        return redirect()->route('invoices.index');
    }
    
    /**
     * Generate and stream/download invoice PDF
     * 
     * @param int $invoiceId Invoice ID
     * @param bool $stream Whether to stream (true) or download (false)
     * @param string|null $invoiceTemplate Optional template name
     * @return Response
     * 
     * @legacy-function generate_pdf
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 254
     */
    public function generatePdf(int $invoiceId, bool $stream = true, ?string $invoiceTemplate = null): Response
    {
        if (config('settings.mark_invoices_sent_pdf') == 1) {
            Invoice::generateInvoiceNumberIfApplicable($invoiceId);
            Invoice::markSent($invoiceId);
        }
        
        // Generate PDF using helper
        $pdfContent = generate_invoice_pdf($invoiceId, $stream, $invoiceTemplate, null);
        
        if ($stream) {
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="invoice-' . $invoiceId . '.pdf"');
        } else {
            return response()->download($pdfContent);
        }
    }
    
    /**
     * Generate XML invoice file (e-invoicing)
     * 
     * @param int $invoiceId Invoice ID
     * @return Response
     * 
     * @legacy-function generate_xml
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 266
     */
    public function generateXml(int $invoiceId): Response
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);
        $items = Item::query()->where('invoice_id', $invoiceId)->get();
        
        // Check e-invoice usage
        $einvoice = get_einvoice_usage($invoice, $items, false);
        if (!$einvoice->user) {
            abort(404);
        }
        
        // Generate XML file
        $xmlId = $einvoice->name;
        $options = [];
        $generator = $xmlId;
        $path = app_path('helpers/XMLconfigs/');
        
        if ($xmlId && file_exists($path . $xmlId . '.php')) {
            include $path . $xmlId . '.php';
            $embedXml = $xml_setting['embedXML'] ?? false;
            $XMLname = $xml_setting['XMLname'] ?? 'invoice';
            $options = $xml_setting['options'] ?? [];
            $generator = $xml_setting['generator'] ?? $generator;
        }
        
        $filename = trans('invoice') . '_' . str_replace(['\\', '/'], '_', $invoice->invoice_number);
        $xmlPath = generate_xml_invoice_file($invoice, $items, $generator, $filename, $options);
        
        $content = file_get_contents($xmlPath);
        unlink($xmlPath);
        
        return response($content)->header('Content-Type', 'text/xml');
    }
    
    /**
     * Generate SUMEX PDF for Swiss medical billing
     * 
     * @param int $invoiceId Invoice ID
     * @return Response
     * 
     * @legacy-function generate_sumex_pdf
     * @legacy-file application/modules/invoices/controllers/Invoices.php
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
     * Generate SUMEX copy PDF
     * 
     * @param int $invoiceId Invoice ID
     * @return Response
     * 
     * @legacy-function generate_sumex_copy
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 308
     */
    public function generateSumexCopy(int $invoiceId): Response
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);
        $items = Item::query()->where('invoice_id', $invoiceId)->get();
        
        $sumex = new \Sumex([
            'invoice' => $invoice,
            'items' => $items,
            'options' => [
                'copy' => '1',
                'storno' => '0',
            ],
        ]);
        
        return response($sumex->pdf())
            ->header('Content-Type', 'application/pdf');
    }
    
    /**
     * Delete invoice tax rate and recalculate invoice amounts
     * 
     * @param int $invoiceId Invoice ID
     * @param int $invoiceTaxRateId Tax rate ID to delete
     * @return RedirectResponse
     * 
     * @legacy-function delete_invoice_tax
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 324
     */
    public function deleteInvoiceTax(int $invoiceId, int $invoiceTaxRateId): RedirectResponse
    {
        InvoiceTaxRate::query()->where('invoice_tax_rate_id', $invoiceTaxRateId)->delete();
        
        // Recalculate invoice amounts
        $globalDiscount = ['item' => InvoiceAmount::getGlobalDiscount($invoiceId)];
        InvoiceAmount::calculate($invoiceId, $globalDiscount);
        
        return redirect()->route('invoices.view', ['invoiceId' => $invoiceId]);
    }
    
    /**
     * Recalculate all invoices in the system
     * 
     * @return RedirectResponse
     * 
     * @legacy-function recalculate_all_invoices
     * @legacy-file application/modules/invoices/controllers/Invoices.php
     * @legacy-line 337
     */
    public function recalculateAllInvoices(): RedirectResponse
    {
        $invoiceIds = Invoice::select('invoice_id')->get();
        
        foreach ($invoiceIds as $invoice) {
            $globalDiscount = ['item' => InvoiceAmount::getGlobalDiscount($invoice->invoice_id)];
            InvoiceAmount::calculate($invoice->invoice_id, $globalDiscount);
        }
        
        session()->flash('alert_success', trans('all_invoices_recalculated'));
        
        return redirect()->route('invoices.index');
    }
}
