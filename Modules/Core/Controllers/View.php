<?php

namespace Modules\Core\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Modules\Core\Models\Upload;
use Modules\Core\Services\CustomFieldService;
use Modules\Invoices\Services\InvoiceService;
use Modules\Invoices\Services\InvoiceTaxRateService;
use Modules\Invoices\Services\InvoiceItemService;
use Modules\Payments\Services\PaymentMethodService;
use Modules\Quotes\Services\QuoteItemService;
use Modules\Quotes\Services\QuoteService;
use Modules\Quotes\Services\QuoteTaxRateService;

/**
 * View Controller
 *
 * Handles public viewing of invoices and quotes
 *
 * @legacy-file application/modules/guest/controllers/View.php
 */
class View
{
    protected InvoiceService $invoiceService;
    protected QuoteService $quoteService;
    protected PaymentMethodService $paymentMethodService;
    protected CustomFieldService $customFieldService;
    protected InvoiceItemService $invoiceItemService;
    protected InvoiceTaxRateService $invoiceTaxRateService;
    protected QuoteItemService $quoteItemService;
    protected QuoteTaxRateService $quoteTaxRateService;

    /**
     * Initialize the View controller with dependency injection.
     *
     * @param InvoiceService $invoiceService
     * @param QuoteService $quoteService
     * @param PaymentMethodService $paymentMethodService
     * @param CustomFieldService $customFieldService
     * @param InvoiceItemService $invoiceItemService
     * @param InvoiceTaxRateService $invoiceTaxRateService
     * @param QuoteItemService $quoteItemService
     * @param QuoteTaxRateService $quoteTaxRateService
     */
    public function __construct(
        InvoiceService $invoiceService,
        QuoteService $quoteService,
        PaymentMethodService $paymentMethodService,
        CustomFieldService $customFieldService,
        InvoiceItemService $invoiceItemService,
        InvoiceTaxRateService $invoiceTaxRateService,
        QuoteItemService $quoteItemService,
        QuoteTaxRateService $quoteTaxRateService
    ) {
        $this->invoiceService = $invoiceService;
        $this->quoteService = $quoteService;
        $this->paymentMethodService = $paymentMethodService;
        $this->customFieldService = $customFieldService;
        $this->invoiceItemService = $invoiceItemService;
        $this->invoiceTaxRateService = $invoiceTaxRateService;
        $this->quoteItemService = $quoteItemService;
        $this->quoteTaxRateService = $quoteTaxRateService;
    }
    /**
     * Display public invoice page.
     *
     * @param string $invoice_url_key Invoice URL key
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function invoice
     * @legacy-file application/modules/guest/controllers/View.php
     */
    public function invoice(string $invoice_url_key = ''): \Illuminate\View\View
    {
        if (!$invoice_url_key) {
            abort(404);
        }

        // TODO: Implement guestVisible scope
        $invoice = $this->invoiceService->getByUrlKey($invoice_url_key);
        
        if (!$invoice) {
            abort(404);
        }

        // Mark as viewed for non-admin users
        if (Session::get('user_type') != 1 && $invoice->invoice_status_id == 2) {
            $this->invoiceService->markViewed($invoice->invoice_id);
        }

        $payment_method = null;
        if ($invoice->payment_method != 0) {
            $payment_method = $this->paymentMethodService->find($invoice->payment_method);
        }

        $custom_fields = [
            'invoice' => $this->customFieldService->getValuesForFields('mdl_invoice_custom', $invoice->invoice_id),
            'client' => $this->customFieldService->getValuesForFields('mdl_client_custom', $invoice->client_id),
            'user' => $this->customFieldService->getValuesForFields('mdl_user_custom', $invoice->user_id),
        ];

        $attachments = $this->getAttachments($invoice_url_key);
        $is_overdue = $invoice->invoice_balance > 0 && strtotime($invoice->invoice_date_due) < time();

        $data = [
            'invoice' => $invoice,
            'items' => $this->invoiceItemService->getByInvoiceId($invoice->invoice_id),
            'invoice_tax_rates' => $this->invoiceTaxRateService->getByInvoiceId($invoice->invoice_id),
            'invoice_url_key' => $invoice_url_key,
            'flash_message' => Session::get('flash_message'),
            'payment_method' => $payment_method,
            'is_overdue' => $is_overdue,
            'attachments' => $attachments,
            'custom_fields' => $custom_fields,
            'legacy_calculation' => Config::get('legacy_calculation'),
        ];

        return view('core::invoice_templates_public_' . Config::get('public_invoice_template'), $data);
    }

    /**
     * Generate invoice PDF.
     *
     * @param string $invoice_url_key Invoice URL key
     * @param bool $stream Stream PDF or download
     * @param string|null $invoice_template PDF template name
     *
     * @return void
     *
     * @legacy-function generateInvoicePdf
     * @legacy-file application/modules/guest/controllers/View.php
     */
    public function generateInvoicePdf(string $invoice_url_key, bool $stream = true, ?string $invoice_template = null): void
    {
        $invoice = $this->invoiceService->getByUrlKey($invoice_url_key);
        
        if ($invoice) {
            if (!$invoice_template) {
                $invoice_template = select_pdf_invoice_template($invoice);
            }
            generate_invoice_pdf($invoice->invoice_id, $stream, $invoice_template, 1);
        }
    }

    /**
     * Generate Sumex PDF for invoice.
     *
     * @param string $invoice_url_key Invoice URL key
     * @param bool $stream Stream PDF or download
     * @param string|null $invoice_template PDF template name
     *
     * @return void
     *
     * @legacy-function generateSumexPdf
     * @legacy-file application/modules/guest/controllers/View.php
     */
    public function generateSumexPdf(string $invoice_url_key, bool $stream = true, ?string $invoice_template = null): void
    {
        $invoice = $this->invoiceService->getByUrlKey($invoice_url_key);
        
        if ($invoice) {
            if ($invoice->sumex_id == null) {
                abort(404);
            }
            if (!$invoice_template) {
                $invoice_template = get_setting('pdf_invoice_template');
            }
            generate_invoice_sumex($invoice->invoice_id);
        }
    }

    /**
     * Display public quote page.
     *
     * @param string $quote_url_key Quote URL key
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function quote
     * @legacy-file application/modules/guest/controllers/View.php
     */
    public function quote(string $quote_url_key = ''): \Illuminate\View\View
    {
        if (!$quote_url_key) {
            abort(404);
        }

        // TODO: Implement guestVisible scope
        $quote = $this->quoteService->getByUrlKey($quote_url_key);
        
        if (!$quote) {
            abort(404);
        }

        // Mark as viewed for non-admin users
        if (Session::get('user_type') != 1 && $quote->quote_status_id == 2) {
            $this->quoteService->markViewed($quote->quote_id);
        }

        $custom_fields = [
            'quote' => $this->customFieldService->getValuesForFields('mdl_quote_custom', $quote->quote_id),
            'client' => $this->customFieldService->getValuesForFields('mdl_client_custom', $quote->client_id),
            'user' => $this->customFieldService->getValuesForFields('mdl_user_custom', $quote->user_id),
        ];

        $attachments = $this->getAttachments($quote_url_key);
        $is_expired = strtotime($quote->quote_date_expires) < time();

        $data = [
            'quote' => $quote,
            'items' => $this->quoteItemService->getByQuoteId($quote->quote_id),
            'quote_tax_rates' => $this->quoteTaxRateService->getByQuoteId($quote->quote_id),
            'quote_url_key' => $quote_url_key,
            'flash_message' => Session::get('flash_message'),
            'is_expired' => $is_expired,
            'attachments' => $attachments,
            'custom_fields' => $custom_fields,
            'legacy_calculation' => Config::get('legacy_calculation'),
        ];

        return view('core::quote_templates_public_' . Config::get('public_quote_template'), $data);
    }

    /**
     * Generate quote PDF.
     *
     * @param string $quote_url_key Quote URL key
     * @param bool $stream Stream PDF or download
     * @param string|null $quote_template PDF template name
     *
     * @return void
     *
     * @legacy-function generateQuotePdf
     * @legacy-file application/modules/guest/controllers/View.php
     */
    public function generateQuotePdf(string $quote_url_key, bool $stream = true, ?string $quote_template = null): void
    {
        $quote = $this->quoteService->getByUrlKey($quote_url_key);
        
        if (!$quote) {
            abort(404);
        }

        if (!$quote_template) {
            $quote_template = get_setting('pdf_quote_template');
        }

        generate_quote_pdf($quote->quote_id, $stream, $quote_template);
    }

    /**
     * Approve a quote.
     *
     * @param string $quote_url_key Quote URL key
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function approveQuote
     * @legacy-file application/modules/guest/controllers/View.php
     */
    public function approveQuote(string $quote_url_key): \Illuminate\Http\RedirectResponse
    {
        $this->quoteService->approveQuoteByKey($quote_url_key);
        
        $quote = $this->quoteService->getByUrlKey($quote_url_key);
        if ($quote) {
            email_quote_status($quote->quote_id, 'approved');
        }

        return redirect()->route('guest.view.quote', ['quote_url_key' => $quote_url_key]);
    }

    /**
     * Reject a quote.
     *
     * @param string $quote_url_key Quote URL key
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function rejectQuote
     * @legacy-file application/modules/guest/controllers/View.php
     */
    public function rejectQuote(string $quote_url_key): \Illuminate\Http\RedirectResponse
    {
        $this->quoteService->rejectQuoteByKey($quote_url_key);
        
        $quote = $this->quoteService->getByUrlKey($quote_url_key);
        if ($quote) {
            email_quote_status($quote->quote_id, 'rejected');
        }

        return redirect()->route('guest.view.quote', ['quote_url_key' => $quote_url_key]);
    }

    /**
     * Retrieve stored uploads associated with a given URL key.
     *
     * Returns an array of attachments found for the provided URL key. Each attachment is an associative array with:
     * - `name`: original filename,
     * - `fullname`: stored filename,
     * - `size`: file size in bytes (0 if the file is missing).
     *
     * @param string $url_key the URL key that identifies the uploads
     *
     * @return array<int, array{name:string,fullname:string,size:int}> list of attachments matching the URL key
     */
    private function getAttachments(string $url_key): array
    {
        $results = Upload::select('file_name_new', 'file_name_original')
            ->where('url_key', $url_key)
            ->get();
        $names   = [];
        foreach ($results as $row) {
            $names[] = [
                'name'     => $row->file_name_original,
                'fullname' => $row->file_name_new,
                'size'     => file_exists(storage_path('app/uploads/' . $row->file_name_new)) ? filesize(storage_path('app/uploads/' . $row->file_name_new)) : 0,
            ];
        }

        return $names;
    }
}
