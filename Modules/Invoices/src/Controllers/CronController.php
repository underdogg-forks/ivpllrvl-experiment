<?php

namespace Modules\Invoices\Controllers;

use Illuminate\Support\Facades\Log;
use Modules\Core\Services\EmailTemplateService;
use Modules\Core\Services\UploadService;
use Modules\Core\Support\MailerHelper;
use Modules\Core\Support\TranslationHelper;
use Modules\Invoices\Services\InvoiceService;
use Modules\Invoices\Services\InvoicesRecurringService;

/**
 * CronController.
 *
 * Handles scheduled cron tasks for recurring invoices
 *
 * @legacy-file application/modules/invoices/controllers/Cron.php
 */
class CronController
{
    /**
     * Initialize the CronController with dependency injection.
     *
     * @param InvoiceService           $invoiceService
     * @param InvoicesRecurringService $invoicesRecurringService
     * @param EmailTemplateService     $emailTemplateService
     * @param UploadService            $uploadService
     */
    public function __construct(
        protected InvoiceService $invoiceService,
        protected InvoicesRecurringService $invoicesRecurringService,
        protected EmailTemplateService $emailTemplateService,
        protected UploadService $uploadService
    ) {}

    /**
     * Process recurring invoices via cron job.
     *
     * @param string|null $cron_key Cron authorization key
     *
     * @return void
     *
     * @legacy-function recur
     *
     * @legacy-file application/modules/invoices/controllers/Cron.php
     */
    public function recur(?string $cron_key = null): void
    {
        // Check the provided cron key
        if ($cron_key != SettingsHelper::getSetting('cron_key')) {
            Log::error('[CronController] Wrong cron key provided! ' . $cron_key);
            abort(500, TranslationHelper::trans('wrong_cron_key_provided'));
        }

        // Gather a list of recurring invoices to generate
        $invoices_recurring = $this->invoicesRecurringService->getActive();
        $recurInfo          = [];

        foreach ($invoices_recurring as $invoice_recurring) {
            $recurInfo = [
                'invoice_id'           => $invoice_recurring->invoice_id,
                'client_id'            => $invoice_recurring->client_id,
                'invoice_group_id'     => $invoice_recurring->invoice_group_id,
                'invoice_status_id'    => $invoice_recurring->invoice_status_id,
                'invoice_number'       => $invoice_recurring->invoice_number,
                'invoice_recurring_id' => $invoice_recurring->invoice_recurring_id,
                'recur_start_date'     => $invoice_recurring->recur_start_date,
                'recur_end_date'       => $invoice_recurring->recur_end_date,
                'recur_frequency'      => $invoice_recurring->recur_frequency,
                'recur_next_date'      => $invoice_recurring->recur_next_date,
                'recur_status'         => $invoice_recurring->recur_status,
            ];

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[CronController] Recurring Info: ' . json_encode($recurInfo, JSON_PRETTY_PRINT));
            }

            // This is the original invoice id
            $source_id = $invoice_recurring->invoice_id;

            // This is the original invoice
            $invoice = $this->invoiceService->find($source_id);

            // Automatic calculation mode
            if (SettingsHelper::getSetting('einvoicing')) {
                // Only for shift legacy_calculation mode
                get_einvoice_usage($invoice, [], false);
            }

            // Create the new invoice
            $db_array = [
                'client_id'                => $invoice->client_id,
                'payment_method'           => $invoice->payment_method,
                'invoice_date_created'     => $invoice_recurring->recur_next_date,
                'invoice_date_due'         => $this->invoiceService->getDateDue($invoice_recurring->recur_next_date),
                'invoice_group_id'         => $invoice->invoice_group_id,
                'user_id'                  => $invoice->user_id,
                'invoice_number'           => $this->invoiceService->getInvoiceNumber($invoice->invoice_group_id),
                'invoice_url_key'          => $this->invoiceService->getUrlKey(),
                'invoice_terms'            => $invoice->invoice_terms,
                'invoice_discount_amount'  => $invoice->invoice_discount_amount,
                'invoice_discount_percent' => $invoice->invoice_discount_percent,
            ];

            // This is the new invoice id
            $target_id = $this->invoiceService->createInvoice($db_array, false);

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[CronController] Invoice with id ' . $target_id . ' was created');
            }

            // Copy the original invoice to the new invoice
            $this->invoiceService->copyInvoice($source_id, $target_id, false);

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[CronController] Invoice with sourceId ' . $source_id . ' was copied to id ' . $target_id);
            }

            // Update the next recur date for the recurring invoice
            $this->invoicesRecurringService->setNextRecurDate($invoice_recurring->invoice_recurring_id);

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[CronController] Next recurring date was set');
            }

            // Email the new invoice if applicable
            if (SettingsHelper::getSetting('automatic_email_on_recur') && MailerHelper::mailerConfigured()) {
                $new_invoice = $this->invoiceService->find($target_id);

                // Set the email body, use default email template if available
                $email_template_id = SettingsHelper::getSetting('email_invoice_template');
                if ( ! $email_template_id) {
                    Log::error('[CronController] No email template set in the system settings!');
                    continue;
                }

                $email_template = $this->emailTemplateService->find($email_template_id);
                if ( ! $email_template) {
                    Log::error('[CronController] No email template set in the system settings!');
                    continue;
                }

                $tpl = $email_template;

                // Prepare the attachments
                $attachment_files = $this->uploadService->getInvoiceUploads($target_id);

                // Prepare the body
                $body = $tpl->email_template_body;
                if (mb_strlen($body) != mb_strlen(strip_tags($body))) {
                    $body = htmlspecialchars_decode($body, ENT_COMPAT);
                } else {
                    $body = htmlspecialchars_decode(nl2br($body), ENT_COMPAT);
                }

                $from         = empty($tpl->email_template_from_email) ? [$invoice->user_email, ''] : [$tpl->email_template_from_email, $tpl->email_template_from_name];
                $subject      = empty($tpl->email_template_subject) ? TranslationHelper::trans('invoice') . ' #' . $new_invoice->invoice_number : $tpl->email_template_subject;
                $pdf_template = $tpl->email_template_pdf_template;
                $to           = $invoice->client_email;
                $cc           = $tpl->email_template_cc;
                $bcc          = $tpl->email_template_bcc;

                $email_invoice = email_invoice($target_id, $pdf_template, $from, $to, $subject, $body, $cc, $bcc, $attachment_files);

                if ($email_invoice) {
                    $this->invoiceService->markSent($target_id);
                } else {
                    Log::error('[CronController] Invoice ' . $target_id . ' could not be sent. Please review your Email settings.');
                }
            } else {
                Log::error('[CronController] Automatic_email_on_recur was not set or mailer was not configured');
            }
        }

        if (defined('IP_DEBUG') && IP_DEBUG) {
            log_message('debug', '[CronController] ' . count($invoices_recurring) . ' recurring invoices processed');
        }
    }
}
