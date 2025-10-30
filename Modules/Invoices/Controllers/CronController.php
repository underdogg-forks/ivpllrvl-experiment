<?php

namespace Modules\Invoices\Controllers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoicesRecurring;
use Modules\Core\Models\Setting;

/**
 * CronController
 *
 * Handles cron job operations for invoices, particularly recurring invoice generation
 */
class CronController
{
    /**
     * Process recurring invoices and generate new instances
     *
     * This method is called via cron to automatically generate invoices
     * from recurring invoice templates at scheduled intervals.
     *
     * @param string|null $cronKey Security key to verify cron authenticity
     * @return void
     *
     * @legacy-function recur
     * @legacy-file application/modules/invoices/controllers/Cron.php
     * @legacy-line 22
     */
    public function recur(?string $cronKey = null): void
    {
        // Check the provided cron key for security
        $settingCronKey = get_setting('cron_key');
        if ($cronKey !== $settingCronKey) {
            log_message('error', '[Cron Recurring Invoices] Wrong cron key provided! ' . $cronKey);
            http_response_code(500);
            exit('Wrong cron key!');
        }

        // Gather a list of active recurring invoices to process
        $invoicesRecurring = InvoicesRecurring::active()
            ->with(['invoice', 'client'])
            ->get();

        foreach ($invoicesRecurring as $invoiceRecurring) {
            $recurInfo = [
                'invoice_id' => $invoiceRecurring->invoice_id,
                'client_id' => $invoiceRecurring->client_id,
                'invoice_group_id' => $invoiceRecurring->invoice_group_id,
                'invoice_status_id' => $invoiceRecurring->invoice_status_id,
                'invoice_number' => $invoiceRecurring->invoice_number,
                'invoice_recurring_id' => $invoiceRecurring->invoice_recurring_id,
                'recur_start_date' => $invoiceRecurring->recur_start_date,
                'recur_end_date' => $invoiceRecurring->recur_end_date,
                'recur_frequency' => $invoiceRecurring->recur_frequency,
                'recur_next_date' => $invoiceRecurring->recur_next_date,
                'recur_status' => $invoiceRecurring->recur_status,
            ];

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[Cron Recurring Invoices] Recurring Info: ' . json_encode($recurInfo, JSON_PRETTY_PRINT));
            }

            // Get the source invoice (template)
            $sourceId = $invoiceRecurring->invoice_id;
            $invoice = Invoice::with(['items', 'taxRates', 'client', 'user'])->findOrFail($sourceId);

            // Automatic calculation mode for e-invoicing
            if (get_setting('einvoicing')) {
                // Only for shift legacy_calculation mode
                // Note: get_einvoice_usage is a legacy helper function
                if (function_exists('get_einvoice_usage')) {
                    get_einvoice_usage($invoice, [], false);
                }
            }

            // Prepare new invoice data
            $dbArray = [
                'client_id' => $invoice->client_id,
                'payment_method' => $invoice->payment_method,
                'invoice_date_created' => $invoiceRecurring->recur_next_date,
                'invoice_date_due' => $this->getDateDue($invoiceRecurring->recur_next_date),
                'invoice_group_id' => $invoice->invoice_group_id,
                'user_id' => $invoice->user_id,
                'invoice_number' => $this->getInvoiceNumber($invoice->invoice_group_id),
                'invoice_url_key' => $this->getUrlKey(),
                'invoice_terms' => $invoice->invoice_terms,
                'invoice_discount_amount' => $invoice->invoice_discount_amount,
                'invoice_discount_percent' => $invoice->invoice_discount_percent,
            ];

            // Create the new invoice
            $newInvoice = Invoice::query()->create($dbArray);
            $targetId = $newInvoice->invoice_id;

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[Cron Recurring Invoices] Recurring Invoice with id ' . $targetId . ' was created');
            }

            // Copy items and related data from source to new invoice
            $this->copyInvoice($sourceId, $targetId);

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[Cron Recurring Invoices] Recurring Invoice with sourceId ' . $sourceId . ' was copied to id ' . $targetId);
            }

            // Update the next recur date for the recurring invoice
            $this->setNextRecurDate($invoiceRecurring->invoice_recurring_id);

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[Cron Recurring Invoices] Next Recurring date was set');
            }

            // Email the new invoice if applicable
            if (get_setting('automatic_email_on_recur') && function_exists('mailer_configured') && mailer_configured()) {
                $this->emailNewInvoice($targetId, $invoice);
            } else {
                log_message('error', '[Cron Recurring Invoices] Automatic_email_on_recur was not set or mailer was not configured');
            }
        }

        if (defined('IP_DEBUG') && IP_DEBUG) {
            log_message('debug', '[Cron Recurring Invoices] ' . count($invoicesRecurring) . ' recurring invoices processed');
        }
    }

    /**
     * Calculate due date based on creation date
     *
     * @param string $createdDate Invoice creation date
     * @return string Due date
     */
    private function getDateDue(string $createdDate): string
    {
        $daysUntilDue = get_setting('invoices_due_after') ?: 30;
        return date('Y-m-d', strtotime($createdDate . ' + ' . $daysUntilDue . ' days'));
    }

    /**
     * Generate a unique invoice number for the given group
     *
     * @param int $invoiceGroupId Invoice group ID
     * @return string Generated invoice number
     */
    private function getInvoiceNumber(int $invoiceGroupId): string
    {
        // Use the InvoiceGroup model to generate the number
        $invoiceGroup = \Modules\Invoices\Models\InvoiceGroup::query()->findOrFail($invoiceGroupId);
        return $invoiceGroup->generateInvoiceNumber();
    }

    /**
     * Generate a unique URL key for the invoice
     *
     * @return string Unique URL key
     */
    private function getUrlKey(): string
    {
        do {
            $urlKey = bin2hex(random_bytes(16));
            $exists = Invoice::query()->where('invoice_url_key', $urlKey)->exists();
        } while ($exists);

        return $urlKey;
    }

    /**
     * Copy invoice items and related data from source to target
     *
     * @param int $sourceId Source invoice ID
     * @param int $targetId Target invoice ID
     * @return void
     */
    private function copyInvoice(int $sourceId, int $targetId): void
    {
        $sourceInvoice = Invoice::with(['items', 'taxRates'])->findOrFail($sourceId);

        // Copy items
        foreach ($sourceInvoice->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $targetId;
            $newItem->save();
        }

        // Copy tax rates
        foreach ($sourceInvoice->taxRates as $taxRate) {
            $newTaxRate = $taxRate->replicate();
            $newTaxRate->invoice_id = $targetId;
            $newTaxRate->save();
        }

        // Recalculate amounts for new invoice
        if (class_exists('\Modules\Invoices\Models\InvoiceAmount')) {
            \Modules\Invoices\Models\InvoiceAmount::calculate($targetId);
        }
    }

    /**
     * Set the next recur date for a recurring invoice
     *
     * @param int $recurringId Recurring invoice ID
     * @return void
     */
    private function setNextRecurDate(int $recurringId): void
    {
        $recurring = InvoicesRecurring::query()->findOrFail($recurringId);

        // Calculate next date based on frequency
        $currentDate = $recurring->recur_next_date;
        $frequency = $recurring->recur_frequency;

        // Frequency mapping: 1=weekly, 2=biweekly, 3=monthly, 4=quarterly, 5=semiannually, 6=annually
        $intervals = [
            1 => '+1 week',
            2 => '+2 weeks',
            3 => '+1 month',
            4 => '+3 months',
            5 => '+6 months',
            6 => '+1 year',
        ];

        $interval = $intervals[$frequency] ?? '+1 month';
        $nextDate = date('Y-m-d', strtotime($currentDate . ' ' . $interval));

        $recurring->update(['recur_next_date' => $nextDate]);
    }

    /**
     * Email the new invoice to the client
     *
     * @param int $invoiceId New invoice ID
     * @param Invoice $originalInvoice Original invoice with user/client data
     * @return void
     */
    private function emailNewInvoice(int $invoiceId, Invoice $originalInvoice): void
    {
        // Get email template
        $emailTemplateId = get_setting('email_invoice_template');
        if (!$emailTemplateId) {
            log_message('error', '[Cron Recurring Invoices] No email template set in the system settings!');
            return;
        }

        // Note: This uses legacy email template system
        // In a full migration, this would use Illuminate Mail
        if (function_exists('email_invoice')) {
            $newInvoice = Invoice::with(['client', 'user'])->findOrFail($invoiceId);

            // Use legacy email helper function
            // This is a temporary bridge until full email system migration
            $emailSent = email_invoice(
                $invoiceId,
                get_setting('default_invoice_template'),
                [$originalInvoice->user->user_email ?? '', ''],
                $newInvoice->client->client_email ?? '',
                trans('invoice') . ' #' . $newInvoice->invoice_number,
                trans('new_recurring_invoice_email_body'),
                '',
                ''
            );

            if ($emailSent) {
                $newInvoice->update(['invoice_status_id' => 2]); // Mark as sent
            } else {
                log_message('error', '[Cron Recurring Invoices] Invoice ' . $invoiceId . ' could not be sent. Please review your Email settings.');
            }
        }
    }
