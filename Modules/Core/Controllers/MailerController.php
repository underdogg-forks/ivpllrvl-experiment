<?php

namespace Modules\Core\Controllers;

use Modules\Core\Support\MailerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Modules\Core\Services\EmailTemplateService;
use Modules\Core\Services\CustomFieldService;
use Modules\Invoices\Services\InvoiceService;
use Modules\Invoices\Services\TemplateService;
use Modules\Quotes\Services\QuoteService;
use Modules\Core\Services\UploadService;

/**
 * MailerController
 *
 * Handles email composition and sending for invoices and quotes
 *
 * @legacy-file application/modules/mailer/controllers/Mailer.php
 */
class MailerController
{
    protected EmailTemplateService $emailTemplateService;
    protected CustomFieldService $customFieldService;
    protected InvoiceService $invoiceService;
    protected TemplateService $templateService;
    protected QuoteService $quoteService;
    protected UploadService $uploadService;
    protected bool $mailer_configured;

    /**
     * Initialize the MailerController with dependency injection.
     *
     * @param EmailTemplateService $emailTemplateService
     * @param CustomFieldService $customFieldService
     * @param InvoiceService $invoiceService
     * @param TemplateService $templateService
     * @param QuoteService $quoteService
     * @param UploadService $uploadService
     */
    public function __construct(
        EmailTemplateService $emailTemplateService,
        CustomFieldService $customFieldService,
        InvoiceService $invoiceService,
        TemplateService $templateService,
        QuoteService $quoteService,
        UploadService $uploadService
    ) {
        $this->emailTemplateService = $emailTemplateService;
        $this->customFieldService = $customFieldService;
        $this->invoiceService = $invoiceService;
        $this->templateService = $templateService;
        $this->quoteService = $quoteService;
        $this->uploadService = $uploadService;
        $this->mailer_configured = MailerHelper::mailerConfigured();

        if (!$this->mailer_configured) {
            abort(response()->view('core::mailer_not_configured'), 503);
        }
    }

    /**
     * Display the invoice mail composer view.
     *
     * @param Request $request
     * @param int $invoice_id
     *
     * @return \Illuminate\Contracts\View\View|void
     *
     * @legacy-function invoice
     * @legacy-file application/modules/mailer/controllers/Mailer.php
     */
    public function invoice(Request $request, int $invoice_id)
    {
        if (!$this->mailer_configured) {
            return;
        }

        $invoice = $this->invoiceService->getById($invoice_id);
        $email_template_id = select_email_invoice_template($invoice);
        $email_template = '{}';

        if ($email_template_id) {
            $email_template = json_encode($this->emailTemplateService->getById($email_template_id));
        }

        $custom_fields = [];
        foreach (array_keys($this->customFieldService->customTables()) as $table) {
            $custom_fields[$table] = $this->customFieldService->byTable($table)->get()->result();
        }

        return view('core::mailer_invoice', [
            'selected_email_template' => $email_template_id,
            'selected_pdf_template' => select_pdf_invoice_template($invoice),
            'email_templates' => $this->emailTemplateService->where('email_template_type', 'invoice')->get()->result(),
            'email_template' => $email_template,
            'custom_fields' => $custom_fields,
            'pdf_templates' => $this->templateService->getInvoiceTemplates(),
            'invoice' => $invoice,
        ]);
    }

    /**
     * Display the mailer UI for a specific quote.
     *
     * @param Request $request
     * @param int $quote_id
     *
     * @return \Illuminate\View\View|void
     *
     * @legacy-function quote
     * @legacy-file application/modules/mailer/controllers/Mailer.php
     */
    public function quote(Request $request, int $quote_id)
    {
        if (!$this->mailer_configured) {
            return;
        }

        $email_template_id = get_setting('email_quote_template');
        $email_template = '{}';

        if ($email_template_id) {
            $email_template = json_encode($this->emailTemplateService->getById($email_template_id));
        }

        $custom_fields = [];
        foreach (array_keys($this->customFieldService->customTables()) as $table) {
            $custom_fields[$table] = $this->customFieldService->byTable($table)->get()->result();
        }

        return view('core::mailer_quote', [
            'selected_email_template' => $email_template_id,
            'selected_pdf_template' => get_setting('pdf_quote_template'),
            'email_templates' => $this->emailTemplateService->where('email_template_type', 'quote')->get()->result(),
            'email_template' => $email_template,
            'custom_fields' => $custom_fields,
            'pdf_templates' => $this->templateService->getQuoteTemplates(),
            'quote' => $this->quoteService->getById($quote_id),
        ]);
    }

    /**
     * Send an invoice email with an optional PDF and attachments.
     *
     * @param Request $request
     * @param string $invoice_id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     *
     * @legacy-function sendInvoice
     * @legacy-file application/modules/mailer/controllers/Mailer.php
     */
    public function sendInvoice(Request $request, string $invoice_id)
    {
        if ($request->has('btn_cancel')) {
            return Redirect::to('invoices/view/' . $invoice_id);
        }

        if (!$this->mailer_configured) {
            return abort(response()->view('core::mailer_not_configured'), 503);
        }

        $to = $request->input('to_email');
        $from = [$request->input('from_email'), $request->input('from_name')];
        $pdf_template = $request->input('pdf_template');
        $subject = $request->input('subject');
        $body = $request->input('body');

        if (mb_strlen($body) != mb_strlen(strip_tags($body))) {
            $body = htmlspecialchars_decode($body, ENT_COMPAT);
        } else {
            $body = htmlspecialchars_decode(nl2br($body), ENT_COMPAT);
        }

        $cc = $request->input('cc');
        $bcc = $request->input('bcc');
        $attachment_files = $this->uploadService->getInvoiceUploads($invoice_id);

        $this->invoiceService->generateInvoiceNumberIfApplicable($invoice_id);

        if (email_invoice($invoice_id, $pdf_template, $from, $to, $subject, $body, $cc, $bcc, $attachment_files)) {
            $this->invoiceService->markSent($invoice_id);
            Session::flash('alert_success', trans('email_successfully_sent'));

            return Redirect::to('invoices/view/' . $invoice_id);
        }

        return Redirect::to('mailer/invoice/' . $invoice_id);
    }

    /**
     * Send a quote email with an optional PDF attachment and additional uploads.
     *
     * @param Request $request
     * @param string $quote_id
     *
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @legacy-function sendQuote
     * @legacy-file application/modules/mailer/controllers/Mailer.php
     */
    public function sendQuote(Request $request, string $quote_id)
    {
        if ($request->has('btn_cancel')) {
            return Redirect::to('quotes/view/' . $quote_id);
        }

        if (!$this->mailer_configured) {
            return abort(response()->view('core::mailer_not_configured'), 503);
        }

        $to = $request->input('to_email');
        $from = [$request->input('from_email'), $request->input('from_name')];
        $pdf_template = $request->input('pdf_template');
        $subject = $request->input('subject');
        $body = $request->input('body');

        if (mb_strlen($body) != mb_strlen(strip_tags($body))) {
            $body = htmlspecialchars_decode($body, ENT_COMPAT);
        } else {
            $body = htmlspecialchars_decode(nl2br($body), ENT_COMPAT);
        }

        $cc = $request->input('cc');
        $bcc = $request->input('bcc');
        $attachment_files = $this->uploadService->getQuoteUploads($quote_id);

        $this->quoteService->generateQuoteNumberIfApplicable($quote_id);

        if (email_quote($quote_id, $pdf_template, $from, $to, $subject, $body, $cc, $bcc, $attachment_files)) {
            $this->quoteService->markSent($quote_id);
            Session::flash('alert_success', trans('email_successfully_sent'));

            return Redirect::to('quotes/view/' . $quote_id);
        }

        return Redirect::to('mailer/quote/' . $quote_id);
    }
}
