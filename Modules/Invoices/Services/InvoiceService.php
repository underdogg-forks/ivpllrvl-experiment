<?php

namespace Modules\Invoices\Services;

use DateInterval;
use DateTime;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Models\InvoiceTaxRate;
use Modules\Invoices\Models\Item;

class InvoiceService
{
    public const STATUSES = [
        1 => [
            'label' => 'draft',
            'class' => 'draft',
            'href'  => 'invoices/status/draft',
        ],
        2 => [
            'label' => 'sent',
            'class' => 'sent',
            'href'  => 'invoices/status/sent',
        ],
        3 => [
            'label' => 'viewed',
            'class' => 'viewed',
            'href'  => 'invoices/status/viewed',
        ],
        4 => [
            'label' => 'paid',
            'class' => 'paid',
            'href'  => 'invoices/status/paid',
        ],
    ];

    public function getStatuses(): array
    {
        return self::STATUSES;
    }

    public function getValidationRules(): array
    {
        return [
            'client_id'            => 'required|integer',
            'invoice_date_created' => 'required|date',
            'invoice_group_id'     => 'required|integer',
            'invoice_password'     => 'nullable|string',
            'user_id'              => 'required|integer',
        ];
    }

    public function getSaveValidationRules(?int $invoiceId = null): array
    {
        $uniqueRule = 'unique:ip_invoices,invoice_number';
        if ($invoiceId) {
            $uniqueRule .= ',' . $invoiceId . ',invoice_id';
        }

        return [
            'invoice_number'       => $uniqueRule,
            'invoice_date_created' => 'required|date',
            'invoice_date_due'     => 'required|date',
            'invoice_password'     => 'nullable|string',
        ];
    }

    public function calculateDateDue(string $invoiceDateCreated): string
    {
        $dueAfter = get_setting('invoices_due_after');
        $dueDate  = new DateTime($invoiceDateCreated);
        $dueDate->add(new DateInterval('P' . $dueAfter . 'D'));

        return $dueDate->format('Y-m-d');
    }

    public function generateInvoiceNumber(int $invoiceGroupId): string
    {
        $invoiceGroup = InvoiceGroup::findOrFail($invoiceGroupId);

        return app(InvoiceGroupService::class)->generateInvoiceNumber($invoiceGroup);
    }

    public function generateUrlKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getInvoiceGroupId(int $invoiceId): int
    {
        $invoice = Invoice::findOrFail($invoiceId);

        return $invoice->invoice_group_id;
    }

    public function getParentInvoiceNumber(int $parentInvoiceId): string
    {
        $parentInvoice = Invoice::findOrFail($parentInvoiceId);

        return $parentInvoice->invoice_number;
    }

    public function deleteInvoice(int $invoiceId): ?bool
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $deleted = $invoice->delete();

        InvoiceAmount::where('invoice_id', $invoiceId)->delete();
        Item::where('invoice_id', $invoiceId)->delete();
        InvoiceTaxRate::where('invoice_id', $invoiceId)->delete();

        return $deleted;
    }

    public function markViewed(int $invoiceId): bool
    {
        $invoice = Invoice::select('invoice_status_id')
            ->where('invoice_id', $invoiceId)
            ->first();

        if ($invoice && $invoice->invoice_status_id == 2) {
            return Invoice::where('invoice_id', $invoiceId)
                ->update(['invoice_status_id' => 3]) > 0;
        }

        return false;
    }

    public function markSent(int $invoiceId): bool
    {
        $invoice = Invoice::select('invoice_status_id')
            ->where('invoice_id', $invoiceId)
            ->first();

        if ($invoice && $invoice->invoice_status_id == 1) {
            return Invoice::where('invoice_id', $invoiceId)
                ->update(['invoice_status_id' => 2]) > 0;
        }

        return false;
    }

    public function generateInvoiceNumberIfApplicable(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $generateForDraft = get_setting('generate_invoice_number_for_draft');
        if ($invoice->invoice_status_id == 1 && empty($invoice->invoice_number) && $generateForDraft == 0) {
            $invoiceNumber = $this->generateInvoiceNumber($invoice->invoice_group_id);
            Invoice::where('invoice_id', $invoiceId)
                ->update(['invoice_number' => $invoiceNumber]);
        }
    }

    public function isOverdue(Invoice $invoice): bool
    {
        if (in_array($invoice->invoice_status_id, [1, 4], true)) {
            return false;
        }

        $dueDate = new DateTime($invoice->invoice_date_due);
        $now     = new DateTime();

        return $now > $dueDate;
    }

    public function getDaysOverdue(Invoice $invoice): int
    {
        if (! $this->isOverdue($invoice)) {
            return 0;
        }

        $dueDate = new DateTime($invoice->invoice_date_due);
        $now     = new DateTime();

        return $now->diff($dueDate)->days;
    }
}
