<?php

namespace Modules\Invoices\Services;

use DateInterval;
use DateTime;
use Modules\Core\Support\SettingsHelper;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Models\InvoiceTaxRate;
use Modules\Invoices\Models\Item;

class InvoiceService
{
    /**
     * @return array
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function statuses()
     */
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

    /**
     * @param string $invoice_date_created
     *
     * @return string
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_date_due()
     */
    public function calculateDateDue(string $invoiceDateCreated): string
    {
        $dueAfter = SettingsHelper::getSetting('invoices_due_after');
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

    public function getByUrlKey(string $urlKey): Invoice
    {
        return Invoice::query()->where('invoice_url_key', $urlKey)->firstOrFail();
    }

    public function urlKeyExists(string $urlKey): bool
    {
        return Invoice::query()->where('invoice_url_key', $urlKey)->exists();
    }

    /**
     * @param $invoice_id
     *
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_invoice_group_id()
     */
    public function getInvoiceGroupId(int $invoiceId): int
    {
        $invoice = Invoice::findOrFail($invoiceId);

        return $invoice->invoice_group_id;
    }

    /**
     * @param int $parent_invoice_id
     *
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_parent_invoice_number()
     */
    public function getParentInvoiceNumber(int $parentInvoiceId): string
    {
        $parentInvoice = Invoice::findOrFail($parentInvoiceId);

        return $parentInvoice->invoice_number;
    }

    /**
     * @param int $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function delete()
     */
    public function delete(int $invoiceId): ?bool
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $deleted = $invoice->delete();

        InvoiceAmount::query()->where('invoice_id', $invoiceId)->delete();
        Item::query()->where('invoice_id', $invoiceId)->delete();
        InvoiceTaxRate::query()->where('invoice_id', $invoiceId)->delete();

        return $deleted;
    }

    /**
     * @param $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function mark_viewed()
     */
    public function markViewed(int $invoiceId): bool
    {
        $invoice = Invoice::query()->select('invoice_status_id')
            ->where('invoice_id', $invoiceId)
            ->first();

        if ( ! $invoice || $invoice->invoice_status_id !== 2) {
            return false;
        }

        return Invoice::query()->where('invoice_id', $invoiceId)
            ->update(['invoice_status_id' => 3]) > 0;
    }

    /**
     * @param $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function mark_sent()
     */

    public function markSent(int $invoiceId): bool
    {
        $invoice = Invoice::query()->select('invoice_status_id')
            ->where('invoice_id', $invoiceId)
            ->first();

        if ( ! $invoice || $invoice->invoice_status_id !== 1) {
            return false;
        }

        return Invoice::query()->where('invoice_id', $invoiceId)
            ->update(['invoice_status_id' => 2]) > 0;
    }

    /**
     * @param $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function generate_invoice_number_if_applicable()
     */
    public function generateInvoiceNumberIfApplicable(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $generateForDraft = SettingsHelper::getSetting('generate_invoice_number_for_draft');

        if ($invoice->invoice_status_id !== 1 || ! empty($invoice->invoice_number) || $generateForDraft != 0) {
            return;
        }

        $invoiceNumber = $this->generateInvoiceNumber($invoice->invoice_group_id);
        Invoice::query()->where('invoice_id', $invoiceId)
            ->update(['invoice_number' => $invoiceNumber]);
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

    /**
     * @param $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     */
    public function getDaysOverdue(Invoice $invoice): int
    {
        if ( ! $this->isOverdue($invoice)) {
            return 0;
        }

        $dueDate = new DateTime($invoice->invoice_date_due);
        $now     = new DateTime();

        return $now->diff($dueDate)->days;
    }

    /**
     * @param $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     */
    public function getOpenInvoices()
    {
        return Invoice::query()->where('invoice_balance', '>', 0)
            ->with('client')
            ->orderBy('invoice_date_created', 'desc')
            ->get();
    }

    /**
     * @param bool $include_invoice_tax_rates
     *
     * @return int|null
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function create()
     */
    public function createInvoice(array $data): Invoice
    {
        $invoice = Invoice::create($data);

        $invoice->amounts()->create([
            'invoice_id' => $invoice->invoice_id,
        ]);

        return $invoice;
    }

    /**
     * Copies invoice items, tax rates, etc from source to target.
     *
     * @param int  $source_id
     * @param int  $target_id
     * @param bool $copy_recurring_items_only
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function copy_invoice()
     */
    public function copy_invoice(int $sourceId, int $targetId, bool $copyRecurringItemsOnly = false): void
    {
        $sourceInvoice = Invoice::with(['items', 'taxRates', 'customFields'])->findOrFail($sourceId);
        $targetInvoice = Invoice::findOrFail($targetId);

        $globalDiscount = [
            'amount' => $sourceInvoice->invoice_discount_amount,
            'percent' => $sourceInvoice->invoice_discount_percent,
        ];

        $targetInvoice->update([
            'invoice_discount_amount' => $globalDiscount['amount'],
            'invoice_discount_percent' => $globalDiscount['percent'],
        ]);

        foreach ($sourceInvoice->items as $item) {
            if (!$copyRecurringItemsOnly || $item->item_is_recurring) {
                $targetInvoice->items()->create($item->replicate(['invoice_id'])->toArray());
            }
        }

        foreach ($sourceInvoice->taxRates as $taxRate) {
            $targetInvoice->taxRates()->create($taxRate->replicate(['invoice_id'])->toArray());
        }

        $customValues = [];
        foreach ($sourceInvoice->customFields as $field) {
            $customValues[$field->invoice_custom_fieldid] = $field->invoice_custom_fieldvalue;
        }
        $targetInvoice->customFields()->sync($customValues);
    }

    /**
     * Copies invoice items, tax rates, etc from source to target as credit.
     *
     * @param int $sourceId
     * @param int $targetId
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function copy_credit_invoice()
     */
    public function copy_credit_invoice(int $sourceId, int $targetId): void
    {
        $sourceInvoice = Invoice::with(['items', 'taxRates', 'customFields'])->findOrFail($sourceId);
        $targetInvoice = Invoice::findOrFail($targetId);

        $globalDiscount = [
            'amount' => $sourceInvoice->invoice_discount_amount,
            'percent' => $sourceInvoice->invoice_discount_percent,
        ];

        $targetInvoice->update([
            'invoice_discount_amount' => $globalDiscount['amount'],
            'invoice_discount_percent' => $globalDiscount['percent'],
        ]);

        foreach ($sourceInvoice->items as $item) {
            $replicatedItem = $item->replicate(['invoice_id']);
            $replicatedItem->item_quantity = $replicatedItem->item_quantity * -1;
            $targetInvoice->items()->create($replicatedItem->toArray());
        }

        foreach ($sourceInvoice->taxRates as $taxRate) {
            $replicatedRate = $taxRate->replicate(['invoice_id']);
            $replicatedRate->invoice_tax_rate_amount *= -1;
            $targetInvoice->taxRates()->create($replicatedRate->toArray());
        }

        $customValues = [];
        foreach ($sourceInvoice->customFields as $field) {
            $customValues[$field->invoice_custom_fieldid] = $field->invoice_custom_fieldvalue;
        }
        $targetInvoice->customFields()->sync($customValues);
    }

    /**
     * @param Invoice $invoice
     *
     * @return Invoice
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_payments()
     */
    public function get_payments(Invoice $invoice): Invoice
    {
        $invoice->load('payments');

        return $invoice;
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_custom_values()
     */
    public function get_custom_values(int $id)
    {
        return Invoice::findOrFail($id)->customFields;
    }

    /**
     * @param string $invoiceNumber
     *
     * @return array
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_archives()
     */
    public function get_archives(string $invoiceNumber): array
    {
        $files = [];

        $path = uploads_archive_path();

        if (!empty($invoiceNumber)) {
            $files = glob($path . '*_*' . $invoiceNumber . '*.pdf') ?: [];
        } else {
            $files = glob($path . '*.pdf') ?: [];
            rsort($files);
        }

        return $files;
    }

    /**
     * Update an invoice by ID.
     *
     * @param int   $invoiceId
     * @param array $data
     *
     * @return int
     */
    public function updateInvoice(int $invoiceId, array $data): int
    {
        return Invoice::query()->where('invoice_id', $invoiceId)->update($data);
    }

    /**
     * Find an invoice with its relationships.
     *
     * @param int   $id        Invoice ID
     * @param array $relations Relations to eager load
     *
     * @return Invoice|null
     */
    public function findWithRelations(int $id, array $relations = ['client', 'user']): ?Invoice
    {
        return Invoice::query()->with($relations)->find($id);
    }

    /**
     * Find an invoice with its relationships or fail.
     *
     * @param int   $id        Invoice ID
     * @param array $relations Relations to eager load
     *
     * @return Invoice
     */
    public function findWithRelationsOrFail(int $id, array $relations = ['client', 'user']): Invoice
    {
        return Invoice::query()->with($relations)->findOrFail($id);
    }

    /**
     * Get all invoices with relationships, ordered and filtered.
     *
     * @param array       $relations Relations to eager load
     * @param string|null $status    Status filter
     * @param int         $perPage   Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(
        array $relations = ['client', 'user'],
        ?string $status = null,
        int $perPage = 15
    ) {
        $query = Invoice::query()->with($relations);

        match ($status) {
            'draft'   => $query->draft(),
            'sent'    => $query->sent(),
            'viewed'  => $query->viewed(),
            'paid'    => $query->paid(),
            'unpaid'  => $query->unpaid(),
            'overdue' => $query->overdue(),
            default   => null
        };

        return $query->orderBy('invoice_date_created', 'desc')->paginate($perPage);
    }

    /**
     * Get all invoices for a specific client.
     *
     * @param int $clientId Client ID
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function by_client()
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByClientId(int $clientId): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::query()->where('client_id', $clientId)->get();
    }

    /**
     * Update the invoice due date.
     *
     * @param int $invoiceId
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function update_invoice_due_date()
     */
    public function update_invoice_due_date(int $invoiceId): void
    {
        $invoice = Invoice::find($invoiceId);

        if (!empty($invoice) && $invoice->is_read_only != 1 && get_setting('no_update_invoice_due_date_mail') == 0) {
            $currentDate = date_to_mysql(date(date_format_setting()));
            $invoice->update(['invoice_date_due' => $this->calculateDateDue($currentDate)]);
        }
    }
}
