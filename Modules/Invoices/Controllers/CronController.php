<?php

namespace Modules\Invoices\Controllers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Models\InvoicesRecurring;
use Modules\Invoices\Services\InvoiceAmountService;
use Modules\Invoices\Services\InvoiceGroupService;
use Modules\Invoices\Services\InvoiceService;
use Modules\Invoices\Services\InvoicesRecurringService;

class CronController
{
    protected InvoiceService $invoiceService;
    protected InvoiceGroupService $invoiceGroupService;
    protected InvoicesRecurringService $invoicesRecurringService;

    public function __construct(
        InvoiceService $invoiceService,
        InvoiceGroupService $invoiceGroupService,
        InvoicesRecurringService $invoicesRecurringService
    ) {
        $this->invoiceService = $invoiceService;
        $this->invoiceGroupService = $invoiceGroupService;
        $this->invoicesRecurringService = $invoicesRecurringService;
    }
    public function recur(?string $cronKey = null): void
    {
        $settingCronKey = get_setting('cron_key');

        if ($cronKey !== $settingCronKey) {
            log_message('error', '[Cron Recurring Invoices] Wrong cron key provided! ' . ($cronKey ?? 'null'));
            http_response_code(500);
            exit('Wrong cron key!');
        }

        $invoicesRecurring = InvoicesRecurring::active()
            ->with(['invoice', 'client'])
            ->get();

        foreach ($invoicesRecurring as $invoiceRecurring) {
            $recurInfo = [
                'invoice_id'           => $invoiceRecurring->invoice_id,
                'client_id'            => $invoiceRecurring->client_id,
                'invoice_group_id'     => $invoiceRecurring->invoice_group_id,
                'invoice_status_id'    => $invoiceRecurring->invoice_status_id,
                'invoice_number'       => $invoiceRecurring->invoice_number,
                'invoice_recurring_id' => $invoiceRecurring->invoice_recurring_id,
                'recur_start_date'     => $invoiceRecurring->recur_start_date,
                'recur_end_date'       => $invoiceRecurring->recur_end_date,
                'recur_frequency'      => $invoiceRecurring->recur_frequency,
                'recur_next_date'      => $invoiceRecurring->recur_next_date,
                'recur_status'         => $invoiceRecurring->recur_status,
            ];

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[Cron Recurring Invoices] Recurring Info: ' . json_encode($recurInfo, JSON_PRETTY_PRINT));
            }

            $sourceId = $invoiceRecurring->invoice_id;
            $invoice  = Invoice::with(['items', 'taxRates', 'client', 'user'])->findOrFail($sourceId);

            if (get_setting('einvoicing') && function_exists('get_einvoice_usage')) {
                get_einvoice_usage($invoice, [], false);
            }

            $dbArray = [
                'client_id'                => $invoice->client_id,
                'payment_method'           => $invoice->payment_method,
                'invoice_date_created'     => $invoiceRecurring->recur_next_date,
                'invoice_date_due'         => $this->getDateDue($invoiceRecurring->recur_next_date),
                'invoice_group_id'         => $invoice->invoice_group_id,
                'user_id'                  => $invoice->user_id,
                'invoice_number'           => $this->getInvoiceNumber($invoice->invoice_group_id),
                'invoice_url_key'          => $this->getUrlKey(),
                'invoice_terms'            => $invoice->invoice_terms,
                'invoice_discount_amount'  => $invoice->invoice_discount_amount,
                'invoice_discount_percent' => $invoice->invoice_discount_percent,
            ];

            $newInvoice = $this->invoiceService->createInvoice($dbArray);
            $targetId   = $newInvoice->invoice_id;

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[Cron Recurring Invoices] Recurring Invoice with id ' . $targetId . ' was created');
            }

            $this->copyInvoice($sourceId, $targetId);

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[Cron Recurring Invoices] Recurring Invoice with sourceId ' . $sourceId . ' was copied to id ' . $targetId);
            }

            $this->setNextRecurDate($invoiceRecurring->invoice_recurring_id);

            if (defined('IP_DEBUG') && IP_DEBUG) {
                log_message('debug', '[Cron Recurring Invoices] Next Recurring date was set');
            }

            if (get_setting('automatic_email_on_recur') && function_exists('mailer_configured') && mailer_configured()) {
                $this->emailNewInvoice($targetId, $invoice);
            } else {
                log_message('error', '[Cron Recurring Invoices] automatic_email_on_recur not set or mailer not configured');
            }
        }

        if (defined('IP_DEBUG') && IP_DEBUG) {
            log_message('debug', '[Cron Recurring Invoices] ' . $invoicesRecurring->count() . ' recurring invoices processed');
        }
    }

    private function getDateDue(string $createdDate): string
    {
        $daysUntilDue = get_setting('invoices_due_after') ?: 30;

        return date('Y-m-d', strtotime($createdDate . ' + ' . $daysUntilDue . ' days'));
    }

    private function getInvoiceNumber(int $invoiceGroupId): string
    {
        $invoiceGroup = $this->invoiceGroupService->findOrFail($invoiceGroupId);

        return $this->invoiceGroupService->generateInvoiceNumber($invoiceGroup);
    }

    private function getUrlKey(): string
    {
        do {
            $urlKey = $this->invoiceService->generateUrlKey();
            $exists = $this->invoiceService->urlKeyExists($urlKey);
        } while ($exists);

        return $urlKey;
    }

    private function copyInvoice(int $sourceId, int $targetId): void
    {
        $sourceInvoice = Invoice::with(['items', 'taxRates'])->findOrFail($sourceId);

        foreach ($sourceInvoice->items as $item) {
            $newItem             = $item->replicate();
            $newItem->invoice_id = $targetId;
            $newItem->save();
        }

        foreach ($sourceInvoice->taxRates as $taxRate) {
            $newTaxRate             = $taxRate->replicate();
            $newTaxRate->invoice_id = $targetId;
            $newTaxRate->save();
        }

        app(InvoiceAmountService::class)->calculate($targetId);
    }

    private function setNextRecurDate(int $recurringId): void
    {
        $recurring = $this->invoicesRecurringService->findOrFail($recurringId);

        $currentDate = $recurring->recur_next_date;
        $frequency   = $recurring->recur_frequency;

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

    private function emailNewInvoice(int $invoiceId, Invoice $originalInvoice): void
    {
        $emailTemplateId = get_setting('email_invoice_template');

        if ( ! $emailTemplateId) {
            log_message('error', '[Cron Recurring Invoices] No email template set in system settings!');

            return;
        }

        if ( ! function_exists('email_invoice')) {
            log_message('error', '[Cron Recurring Invoices] email_invoice helper not available');

            return;
        }

        $newInvoice = Invoice::with(['client', 'user'])->findOrFail($invoiceId);

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
            $newInvoice->update(['invoice_status_id' => 2]);

            return;
        }

        log_message('error', '[Cron Recurring Invoices] Invoice ' . $invoiceId . ' could not be sent. Check Email settings.');
    }
}
