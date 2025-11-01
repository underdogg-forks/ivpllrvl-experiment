<?php

namespace Modules\Core\Controllers;

use AllowDynamicProperties;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Core\Controllers\GuestController as BaseGuestController;
use Modules\CustomFields\Services\CustomFieldsService;

use function Modules\Guest\Controllers\show_404;

use Modules\Invoices\Services\InvoicesService;
use Modules\Invoices\Services\InvoiceTaxRatesService;
use Modules\Invoices\Services\ItemsService;
use Modules\Payments\app\Services\PaymentMethodsService;
use Modules\Quotes\Services\QuoteItemsService;
use Modules\Quotes\Services\QuotesService;
use Modules\Quotes\Services\QuoteTaxRatesService;

#[AllowDynamicProperties]
class View extends BaseGuestController
{
    /**
     * Render the public invoice page identified by a URL key.
     *
     * Loads a guest-visible invoice by its URL key, marks it viewed for non-staff guests when appropriate, collects related data (payment method, items, tax rates, custom fields, attachments, and overdue status), and returns the configured public invoice view populated with that data.
     *
     * @param string $invoice_url_key the invoice URL key to display
     *
     * @return \Illuminate\View\View the rendered public invoice view populated with invoice data
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException if the URL key is empty or no matching guest-visible invoice is found
     */
    public function invoice($invoice_url_key = '')
    {
        if ( ! $invoice_url_key) {
            abort(404);
        }
        $invoice = (new InvoicesService())->guestVisible()->where('invoice_url_key', $invoice_url_key)->get();
        if ($invoice->numRows() != 1) {
            abort(404);
        }
        $invoice = $invoice->row();
        if (Session::get('user_type') != 1 && $invoice->invoice_status_id == 2) {
            (new InvoicesService())->markViewed($invoice->invoice_id);
        }
        $payment_method = (new PaymentMethodsService())->getById($invoice->payment_method);
        if ($invoice->payment_method == 0) {
            $payment_method = null;
        }
        $custom_fields = [
            'invoice' => (new CustomFieldsService())->getValuesForFields('mdl_invoice_custom', $invoice->invoice_id),
            'client'  => (new CustomFieldsService())->getValuesForFields('mdl_client_custom', $invoice->client_id),
            'user'    => (new CustomFieldsService())->getValuesForFields('mdl_user_custom', $invoice->user_id),
        ];
        $attachments = $this->getAttachments($invoice_url_key);
        $is_overdue  = $invoice->invoice_balance > 0 && strtotime($invoice->invoice_date_due) < time();
        $data        = [
            'invoice'            => $invoice,
            'items'              => (new ItemsService())->getByInvoiceId($invoice->invoice_id),
            'invoice_tax_rates'  => (new InvoiceTaxRatesService())->getByInvoiceId($invoice->invoice_id),
            'invoice_url_key'    => $invoice_url_key,
            'flash_message'      => Session::get('flash_message'),
            'payment_method'     => $payment_method,
            'is_overdue'         => $is_overdue,
            'attachments'        => $attachments,
            'custom_fields'      => $custom_fields,
            'legacy_calculation' => Config::get('legacy_calculation'),
        ];

        return view('invoice_templates.public.' . Config::get('public_invoice_template'), $data);
    }

    /**
     * Generate the PDF for a public invoice identified by its URL key.
     *
     * If the invoice exists and is publicly viewable, produces the invoice PDF using the provided template or a template selected automatically.
     * If no matching invoice is found, the method produces no output.
     *
     * @param string      $invoice_url_key  public URL key identifying the invoice
     * @param bool        $stream           when true, stream the PDF output to the client; when false, return or buffer the generated PDF content
     * @param string|null $invoice_template optional PDF template name to use; when null a template is chosen automatically
     */
    public function generateInvoicePdf($invoice_url_key, $stream = true, $invoice_template = null)
    {
        $invoice = (new InvoicesService())->guestVisible()->where('invoice_url_key', $invoice_url_key)->get();
        if ($invoice->numRows() == 1) {
            $invoice = $invoice->row();
            if ( ! $invoice_template) {
// TODO: Laravel autoloads helpers - $this->load->helper('template');
                $invoice_template = select_pdf_invoice_template($invoice);
            }
// TODO: Laravel autoloads helpers - $this->load->helper('pdf');
            generate_invoice_pdf($invoice->invoice_id, $stream, $invoice_template, 1);
        }
    }

    /**
     * Generate the Sumex PDF for a guest-visible invoice identified by its URL key.
     *
     * If the invoice does not exist or the invoice has no Sumex identifier, a 404 response is shown.
     * If no invoice template is provided, the configured `pdf_invoice_template` setting is used.
     *
     * @param string      $invoice_url_key  the public URL key that identifies the invoice
     * @param bool        $stream           (Compatibility) Stream flag; kept for compatibility but not used by this implementation
     * @param string|null $invoice_template optional PDF template name to use; when null the configured template is applied
     *
     * @return void
     */
    public function generateSumexPdf($invoice_url_key, $stream = true, $invoice_template = null)
    {
        $invoice = (new InvoicesService())->guestVisible()->where('invoice_url_key', $invoice_url_key)->get();
        if ($invoice->numRows() == 1) {
            $invoice = $invoice->row();
            if ($invoice->sumex_id == null) {
                show_404();
            }
            if ( ! $invoice_template) {
                $invoice_template = get_setting('pdf_invoice_template');
            }
// TODO: Laravel autoloads helpers - $this->load->helper('pdf');
            generate_invoice_sumex($invoice->invoice_id);
        }
    }

    /**
     * Render the public quote page for the quote identified by its URL key.
     *
     * @param string $quote_url_key the public URL key that identifies the quote
     *
     * @return \Illuminate\View\View the rendered view for the public quote template
     */
    public function quote($quote_url_key = '')
    {
        if ( ! $quote_url_key) {
            abort(404);
        }
        $quote = (new QuotesService())->guestVisible()->where('quote_url_key', $quote_url_key)->get();
        if ($quote->numRows() != 1) {
            abort(404);
        }
        $quote = $quote->row();
        if (Session::get('user_type') != 1 && $quote->quote_status_id == 2) {
            (new QuotesService())->markViewed($quote->quote_id);
        }
        $custom_fields = [
            'quote'  => (new CustomFieldsService())->getValuesForFields('mdl_quote_custom', $quote->quote_id),
            'client' => (new CustomFieldsService())->getValuesForFields('mdl_client_custom', $quote->client_id),
            'user'   => (new CustomFieldsService())->getValuesForFields('mdl_user_custom', $quote->user_id),
        ];
        $attachments = $this->getAttachments($quote_url_key);
        $is_expired  = strtotime($quote->quote_date_expires) < time();
        $data        = [
            'quote'              => $quote,
            'items'              => (new QuoteItemsService())->getByQuoteId($quote->quote_id),
            'quote_tax_rates'    => (new QuoteTaxRatesService())->getByQuoteId($quote->quote_id),
            'quote_url_key'      => $quote_url_key,
            'flash_message'      => Session::get('flash_message'),
            'is_expired'         => $is_expired,
            'attachments'        => $attachments,
            'custom_fields'      => $custom_fields,
            'legacy_calculation' => Config::get('legacy_calculation'),
        ];

        return view('quote_templates.public.' . Config::get('public_quote_template'), $data);
    }

    /**
     * Render the PDF for a public quote identified by its URL key.
     *
     * If the quote cannot be found, a 404 page is shown. When no template is provided,
     * the configured `pdf_quote_template` setting is used.
     *
     * @param string      $quote_url_key  the public URL key of the quote to generate
     * @param bool        $stream         whether to stream the generated PDF to the client (`true`) or return it (`false`)
     * @param string|null $quote_template optional PDF template name to use; when `null`, the configured template is applied
     */
    public function generateQuotePdf($quote_url_key, $stream = true, $quote_template = null)
    {
        $quote = (new QuotesService())->guestVisible()->where('quote_url_key', $quote_url_key)->get()->row();
        if ( ! $quote) {
            show_404();
        }
        if ( ! $quote_template) {
            $quote_template = get_setting('pdf_quote_template');
        }
// TODO: Laravel autoloads helpers - $this->load->helper('pdf');
        generate_quote_pdf($quote->quote_id, $stream, $quote_template);
    }

    /**
     * Approves the quote identified by the public URL key, sends an "approved" status notification, and redirects to the public quote view.
     *
     * @param string $quote_url_key public URL key identifying the quote to approve
     */
    public function approveQuote(string $quote_url_key)
    {
        (new QuotesService())->approveQuoteByKey($quote_url_key);
        email_quote_status((new QuotesService())->where('ip_quotes.quote_url_key', $quote_url_key)->get()->row()->quote_id, 'approved');
        redirect('guest/view/quote/' . $quote_url_key);
    }

    /**
     * Rejects the quote identified by the public URL key, sends a rejection notification, and redirects the guest to the quote view.
     *
     * @param string $quote_url_key the public URL key that identifies the quote to reject
     */
    public function rejectQuote(string $quote_url_key)
    {
        (new QuotesService())->rejectQuoteByKey($quote_url_key);
        email_quote_status((new QuotesService())->where('ip_quotes.quote_url_key', $quote_url_key)->get()->row()->quote_id, 'rejected');
        redirect('guest/view/quote/' . $quote_url_key);
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
        $results = DB::table('ip_uploads')->select('file_name_new', 'file_name_original')->where('url_key', $url_key)->get();
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
