<?php

namespace Modules\Invoices\Controllers;

use AllowDynamicProperties;
use Modules\Core\Support\MailerHelper;
use Illuminate\Support\Facades\Log;
use Modules\Core\Controllers\BaseController;

#[AllowDynamicProperties]
class CronController extends BaseController
{
    /**
     * Validate the provided cron key, generate invoices for all active recurring schedules, update their next run dates, and optionally email the generated invoices.
     *
     * If the provided cron key does not match the system setting, an error is logged, an HTTP 500 error is shown, and execution is terminated.
     *
     * @param string|null $cron_key the cron key used to authorize this operation; if null, the system setting will be used for comparison
     */
    public function recur($cron_key = null)
    {
        // Check the provided cron key
        if ($cron_key != get_setting('cron_key')) {
            Log::error('[CronController RecurringController InvoicesController] Wrong cron key provided! ' . $cron_key);
            show_error(trans('wrong_cron_key_provided'), 500);
            exit('Wrong cron key!');
        }
        // Gather a list of recurring invoices to generate
        $invoices_recurring = (new InvoicesRecurringService())->active()->get()->result();
        $recurInfo          = [];
        foreach ($invoices_recurring as $invoice_recurring) {
            $recurInfo = ['invoice_id' => $invoice_recurring->invoice_id, 'client_id' => $invoice_recurring->client_id, 'invoice_group_id' => $invoice_recurring->invoice_group_id, 'invoice_status_id' => $invoice_recurring->invoice_status_id, 'invoice_number' => $invoice_recurring->invoice_number, 'invoice_recurring_id' => $invoice_recurring->invoice_recurring_id, 'recur_start_date' => $invoice_recurring->recur_start_date, 'recur_end_date' => $invoice_recurring->recur_end_date, 'recur_frequency' => $invoice_recurring->recur_frequency, 'recur_next_date' => $invoice_recurring->recur_next_date, 'recur_status' => $invoice_recurring->recur_status];
            if (IP_DEBUG) {
                log_message('debug', '[CronController RecurringController InvoicesController] RecurringController Info: ' . json_encode($recurInfo, JSON_PRETTY_PRINT));
            }
            // This is the original invoice id
            $source_id = $invoice_recurring->invoice_id;
            // This is the original invoice
            $invoice = (new InvoicesService())->getById($source_id);
            // Automatic calculation mode
            if (get_setting('einvoicing')) {
// TODO: Laravel autoloads helpers - $this->load->helper('e-invoice');
                // Only for shift legacy_calculation mode
                get_einvoice_usage($invoice, [], false);
            }
            // Create the new invoice
            $db_array = ['client_id' => $invoice->client_id, 'payment_method' => $invoice->payment_method, 'invoice_date_created' => $invoice_recurring->recur_next_date, 'invoice_date_due' => (new InvoicesService())->getDateDue($invoice_recurring->recur_next_date), 'invoice_group_id' => $invoice->invoice_group_id, 'user_id' => $invoice->user_id, 'invoice_number' => (new InvoicesService())->getInvoiceNumber($invoice->invoice_group_id), 'invoice_url_key' => (new InvoicesService())->getUrlKey(), 'invoice_terms' => $invoice->invoice_terms, 'invoice_discount_amount' => $invoice->invoice_discount_amount, 'invoice_discount_percent' => $invoice->invoice_discount_percent];
            // This is the new invoice id
            $target_id = (new InvoicesService())->create($db_array, false);
            if (IP_DEBUG) {
                log_message('debug', '[CronController RecurringController InvoicesController] RecurringController Invoice with id ' . $target_id . ' was created');
            }
            // Copy the original invoice to the new invoice
            (new InvoicesService())->copyInvoice($source_id, $target_id, false);
            if (IP_DEBUG) {
                log_message('debug', '[CronController RecurringController InvoicesController] RecurringController Invoice with sourceId ' . $source_id . ' was copied to id ' . $target_id);
            }
            // Update the next recur date for the recurring invoice
            (new InvoicesRecurringService())->setNextRecurDate($invoice_recurring->invoice_recurring_id);
            if (IP_DEBUG) {
                log_message('debug', '[CronController RecurringController InvoicesController] Next RecurringController date was set');
            }
            // Email the new invoice if applicable
            if (get_setting('automatic_email_on_recur') && MailerHelper::mailerConfigured()) {
                $new_invoice = (new InvoicesService())->getById($target_id);
                // Set the email body, use default email template if available
                $email_template_id = get_setting('email_invoice_template');
                if ( ! $email_template_id) {
                    Log::error('[CronController RecurringController InvoicesController] No email template set in the system settings!');
                    continue;
                }
                $email_template = (new EmailTemplatesService())->where('email_template_id', $email_template_id)->get();
                if ($email_template->numRows() == 0) {
                    Log::error('[CronController RecurringController InvoicesController] No email template set in the system settings!');
                    continue;
                }
                $tpl = $email_template->row();
                // Prepare the attachments
                $attachment_files = (new UploadsService())->getInvoiceUploads($target_id);
                // Prepare the body
                $body = $tpl->email_template_body;
                if (mb_strlen($body) != mb_strlen(strip_tags($body))) {
                    $body = htmlspecialchars_decode($body, ENT_COMPAT);
                } else {
                    $body = htmlspecialchars_decode(nl2br($body), ENT_COMPAT);
                }
                $from          = empty($tpl->email_template_from_email) ? [$invoice->user_email, ''] : [$tpl->email_template_from_email, $tpl->email_template_from_name];
                $subject       = empty($tpl->email_template_subject) ? trans('invoice') . ' #' . $new_invoice->invoice_number : $tpl->email_template_subject;
                $pdf_template  = $tpl->email_template_pdf_template;
                $to            = $invoice->client_email;
                $cc            = $tpl->email_template_cc;
                $bcc           = $tpl->email_template_bcc;
                $email_invoice = email_invoice($target_id, $pdf_template, $from, $to, $subject, $body, $cc, $bcc, $attachment_files);
                if ($email_invoice) {
                    (new InvoicesService())->markSent($target_id);
                } else {
                    Log::error('[CronController RecurringController InvoicesController] Invoice ' . $target_id . 'could not be sent. Please review your Email settings.');
                }
            } else {
                Log::error('[CronController RecurringController InvoicesController] Automatic_email_on_recur was not set or mailer was not configured');
            }
        }
        if (IP_DEBUG) {
            log_message('debug', '[CronController RecurringController InvoicesController] ' . count($invoices_recurring) . ' recurring invoices processed');
        }
    }
}
