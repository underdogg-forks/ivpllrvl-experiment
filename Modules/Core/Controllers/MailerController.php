<?php

namespace Modules\Core\Controllers\Controllers;

use AllowDynamicProperties;
use Modules\Core\Support\MailerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Modules\Core\Controllers\AdminController;
use Modules\Core\Services\EmailTemplatesService;
use Modules\Core\Services\CustomFieldsService;
use Modules\Invoices\Services\InvoicesService;
use Modules\Invoices\Services\TemplatesService;
use Modules\Quotes\Services\QuotesService;
use Modules\Core\Services\UploadsService;

#[AllowDynamicProperties]
class MailerController extends AdminController
{
    private bool $mailer_configured;

    /**
     * Initialize the MailerController and ensure the mailer is configured.
     *
     * If the mailer is not configured, aborts with HTTP 503 and renders the `mailer.not_configured` view.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mailer_configured = MailerHelper::mailerConfigured();
        if ( ! $this->mailer_configured) {
            abort(response()->view('mailer.not_configured'), 503);
        }
    }

    /**
     * Display the invoice mail composer view populated with templates, custom fields, PDF templates, and invoice data.
     *
     * @param Request $request    the HTTP request instance
     * @param int     $invoice_id the ID of the invoice to compose an email for
     *
     * @return \Illuminate\Contracts\View\View The rendered mailer.invoice view populated with:
     *                                         - selected_email_template: ID of the chosen email template
     *                                         - selected_pdf_template: chosen PDF template for the invoice
     *                                         - email_templates: list of invoice email templates
     *                                         - email_template: JSON-encoded selected email template (or '{}')
     *                                         - custom_fields: custom fields grouped by table
     *                                         - pdf_templates: available invoice PDF templates
     *                                         - invoice: the invoice model
     */
    public function invoice(Request $request, int $invoice_id)
    {
        if ( ! $this->mailer_configured) {
            return;
        }
        $invoice           = (new InvoicesService())->getById($invoice_id);
        $email_template_id = select_email_invoice_template($invoice);
        $email_template    = '{}';
        if ($email_template_id) {
            $email_template = json_encode((new EmailTemplatesService())->getById($email_template_id));
        }
        $custom_fields = [];
        foreach (array_keys((new CustomFieldsService())->customTables()) as $table) {
            $custom_fields[$table] = (new CustomFieldsService())->byTable($table)->get()->result();
        }

        return view('mailer.invoice', [
            'selected_email_template' => $email_template_id,
            'selected_pdf_template'   => select_pdf_invoice_template($invoice),
            'email_templates'         => (new EmailTemplatesService())->where('email_template_type', 'invoice')->get()->result(),
            'email_template'          => $email_template,
            'custom_fields'           => $custom_fields,
            'pdf_templates'           => (new TemplatesService())->getInvoiceTemplates(),
            'invoice'                 => $invoice,
        ]);
    }

    /**
     * Display the mailer UI for a specific quote, including templates, custom fields, and PDF options.
     *
     * If the mailer is not configured, the method exits without rendering the view.
     *
     * @param Request $request  the current HTTP request
     * @param int     $quote_id the ID of the quote to prepare for emailing
     *
     * @return \Illuminate\View\View The rendered 'mailer.quote' view containing:
     *                               - selected_email_template: the chosen email template ID
     *                               - selected_pdf_template: the chosen PDF template ID
     *                               - email_templates: available email templates of type 'quote'
     *                               - email_template: JSON-encoded selected email template or '{}' if none
     *                               - custom_fields: custom fields grouped by table
     *                               - pdf_templates: available quote PDF templates
     *                               - quote: the quote model instance
     */
    public function quote(Request $request, int $quote_id)
    {
        if ( ! $this->mailer_configured) {
            return;
        }
        $email_template_id = get_setting('email_quote_template');
        $email_template    = '{}';
        if ($email_template_id) {
            $email_template = json_encode((new EmailTemplatesService())->getById($email_template_id));
        }
        $custom_fields = [];
        foreach (array_keys((new CustomFieldsService())->customTables()) as $table) {
            $custom_fields[$table] = (new CustomFieldsService())->byTable($table)->get()->result();
        }

        return view('mailer.quote', [
            'selected_email_template' => $email_template_id,
            'selected_pdf_template'   => get_setting('pdf_quote_template'),
            'email_templates'         => (new EmailTemplatesService())->where('email_template_type', 'quote')->get()->result(),
            'email_template'          => $email_template,
            'custom_fields'           => $custom_fields,
            'pdf_templates'           => (new TemplatesService())->getQuoteTemplates(),
            'quote'                   => (new QuotesService())->getById($quote_id),
        ]);
    }

    /**
     * Send an invoice email with an optional PDF and attachments.
     *
     * This will read email fields from the request, normalize the body HTML,
     * attach any invoice uploads, ensure the invoice number exists, and attempt
     * to send the email. On success the invoice is marked as sent and a success
     * flash message is set.
     *
     * @param Request $request    incoming HTTP request containing email fields (to, from, subject, body, cc, bcc, pdf_template) and an optional cancel button
     * @param string  $invoice_id identifier of the invoice to email
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response redirects to the invoice view on success or when cancelled, redirects back to the mailer form on failure, or returns a 503 response if the mailer is not configured
     */
    public function sendInvoice(Request $request, string $invoice_id)
    {
        if ($request->has('btn_cancel')) {
            return Redirect::to('invoices/view/' . $invoice_id);
        }
        if ( ! $this->mailer_configured) {
            return abort(response()->view('mailer.not_configured'), 503);
        }
        $to           = $request->input('to_email');
        $from         = [$request->input('from_email'), $request->input('from_name')];
        $pdf_template = $request->input('pdf_template');
        $subject      = $request->input('subject');
        $body         = $request->input('body');
        if (mb_strlen($body) != mb_strlen(strip_tags($body))) {
            $body = htmlspecialchars_decode($body, ENT_COMPAT);
        } else {
            $body = htmlspecialchars_decode(nl2br($body), ENT_COMPAT);
        }
        $cc               = $request->input('cc');
        $bcc              = $request->input('bcc');
        $attachment_files = (new UploadsService())->getInvoiceUploads($invoice_id);
        (new InvoicesService())->generateInvoiceNumberIfApplicable($invoice_id);
        if (email_invoice($invoice_id, $pdf_template, $from, $to, $subject, $body, $cc, $bcc, $attachment_files)) {
            (new InvoicesService())->markSent($invoice_id);
            Session::flash('alert_success', trans('email_successfully_sent'));

            return Redirect::to('invoices/view/' . $invoice_id);
        }

        return Redirect::to('mailer/invoice/' . $invoice_id);
    }

    /**
     * Send a quote email with an optional PDF attachment and additional uploads.
     *
     * Sends the specified quote to the recipient(s), optionally attaching a selected PDF template
     * and any uploaded files. If the email is successfully sent the quote is marked as sent.
     *
     * @param Request $request  the HTTP request containing email fields (`to_email`, `from_email`, `from_name`, `pdf_template`, `subject`, `body`, `cc`, `bcc`) and optional cancel action
     * @param string  $quote_id the identifier of the quote to send
     *
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *                                                                                      Redirects to the quote view when cancelled or after a successful send;
     *                                                                                      redirects back to the mailer quote page if sending fails;
     *                                                                                      aborts with a 503 response rendering the `mailer.not_configured` view when the mailer is not configured.
     */
    public function sendQuote(Request $request, string $quote_id)
    {
        if ($request->has('btn_cancel')) {
            return Redirect::to('quotes/view/' . $quote_id);
        }
        if ( ! $this->mailer_configured) {
            return abort(response()->view('mailer.not_configured'), 503);
        }
        $to           = $request->input('to_email');
        $from         = [$request->input('from_email'), $request->input('from_name')];
        $pdf_template = $request->input('pdf_template');
        $subject      = $request->input('subject');
        $body         = $request->input('body');
        if (mb_strlen($body) != mb_strlen(strip_tags($body))) {
            $body = htmlspecialchars_decode($body, ENT_COMPAT);
        } else {
            $body = htmlspecialchars_decode(nl2br($body), ENT_COMPAT);
        }
        $cc               = $request->input('cc');
        $bcc              = $request->input('bcc');
        $attachment_files = (new UploadsService())->getQuoteUploads($quote_id);
        (new QuotesService())->generateQuoteNumberIfApplicable($quote_id);
        if (email_quote($quote_id, $pdf_template, $from, $to, $subject, $body, $cc, $bcc, $attachment_files)) {
            (new QuotesService())->markSent($quote_id);
            Session::flash('alert_success', trans('email_successfully_sent'));

            return Redirect::to('quotes/view/' . $quote_id);
        }

        return Redirect::to('mailer/quote/' . $quote_id);
    }
}
