<?php

namespace Modules\Invoices\Services;

use DateInterval;
use DateTime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\SettingsHelper;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Models\InvoiceTaxRate;
use Modules\Invoices\Models\Item;

/**
 *
 */
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
    public function calculateDateDue(string $invoice_date_created): string
    {
        $dueAfter = (int) SettingsHelper::getSetting('invoices_due_after');
        $dueDate  = new DateTime($invoice_date_created);
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
        return (int) $invoice->invoice_group_id;
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
        return (string) $parentInvoice->invoice_number;
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

        return DB::transaction(function () use ($invoice, $invoiceId) {
            $deleted = (bool) $invoice->delete();
            InvoiceAmount::query()->where('invoice_id', $invoiceId)->delete();
            Item::query()->where('invoice_id', $invoiceId)->delete();
            InvoiceTaxRate::query()->where('invoice_id', $invoiceId)->delete();
            DB::table('ip_invoice_custom')->where('invoice_id', $invoiceId)->delete();
            return $deleted;
        });
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
        $row = Invoice::query()->select('invoice_status_id')->where('invoice_id', $invoiceId)->first();
        if (! $row || $row->invoice_status_id !== 2) {
            return false;
        }
        return Invoice::query()->where('invoice_id', $invoiceId)->update(['invoice_status_id' => 3]) > 0;
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
        $row = Invoice::query()->select('invoice_status_id')->where('invoice_id', $invoiceId)->first();
        if (! $row || $row->invoice_status_id !== 1) {
            return false;
        }
        return Invoice::query()->where('invoice_id', $invoiceId)->update(['invoice_status_id' => 2]) > 0;
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
        $generateForDraft = (int) SettingsHelper::getSetting('generate_invoice_number_for_draft');
        if ($invoice->invoice_status_id !== 1 || ! empty($invoice->invoice_number) || $generateForDraft !== 0) {
            return;
        }
        $invoiceNumber = $this->generateInvoiceNumber((int) $invoice->invoice_group_id);
        Invoice::query()->where('invoice_id', $invoiceId)->update(['invoice_number' => $invoiceNumber]);
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
        if (! $this->isOverdue($invoice)) {
            return 0;
        }
        $dueDate = new DateTime($invoice->invoice_date_due);
        $now     = new DateTime();
        return (int) $now->diff($dueDate)->days;
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
        return Invoice::query()
            ->where('invoice_balance', '>', 0)
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
        $invoice->amounts()->create(['invoice_id' => $invoice->invoice_id]);
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
    public function copy_invoice($source_id, $target_id, $copy_recurring_items_only = false): void
    {
        $source = Invoice::findOrFail($source_id);

        $global_discount = [
            'amount'         => $source->invoice_discount_amount ?? 0.0,
            'percent'        => $source->invoice_discount_percent ?? 0.0,
            'item'           => 0.0,
            'items_subtotal' => (float) Item::query()->where('invoice_id', $source_id)->sum(DB::raw('item_price * item_quantity')),
        ];

        Invoice::query()->where('invoice_id', $target_id)->update([
            'invoice_discount_percent' => $global_discount['percent'],
            'invoice_discount_amount'  => $global_discount['amount'],
        ]);

        $items = Item::query()->where('invoice_id', $source_id)->get();
        foreach ($items as $invoice_item) {
            if ($copy_recurring_items_only && ! (bool) $invoice_item->item_is_recurring) {
                continue;
            }

            Item::create([
                'invoice_id'           => $target_id,
                'item_tax_rate_id'     => $invoice_item->item_tax_rate_id,
                'item_product_id'      => $invoice_item->item_product_id,
                'item_task_id'         => $invoice_item->item_task_id,
                'item_name'            => $invoice_item->item_name,
                'item_description'     => $invoice_item->item_description,
                'item_quantity'        => $invoice_item->item_quantity,
                'item_price'           => $invoice_item->item_price,
                'item_discount_amount' => $invoice_item->item_discount_amount,
                'item_order'           => $invoice_item->item_order,
                'item_is_recurring'    => $invoice_item->item_is_recurring,
                'item_product_unit'    => $invoice_item->item_product_unit,
                'item_product_unit_id' => $invoice_item->item_product_unit_id,
            ]);
        }

        $invoice_tax_rates = InvoiceTaxRate::query()->where('invoice_id', $source_id)->get();
        foreach ($invoice_tax_rates as $invoice_tax_rate) {
            InvoiceTaxRate::create([
                'invoice_id'              => $target_id,
                'tax_rate_id'             => $invoice_tax_rate->tax_rate_id,
                'include_item_tax'        => $invoice_tax_rate->include_item_tax,
                'invoice_tax_rate_amount' => $invoice_tax_rate->invoice_tax_rate_amount,
            ]);
        }

        $custom_fields = DB::table('ip_invoice_custom')->where('invoice_id', $source_id)->get();
        foreach ($custom_fields as $field) {
            DB::table('ip_invoice_custom')->insert([
                'invoice_id'                => $target_id,
                'invoice_custom_fieldid'    => $field->invoice_custom_fieldid,
                'invoice_custom_fieldvalue' => $field->invoice_custom_fieldvalue,
            ]);
        }
    }

    /**
     * Copies invoice items, tax rates, etc from source to target.
     *
     * @param int $source_id
     * @param int $target_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function copy_credit_invoice()
     */
    public function copy_credit_invoice($source_id, $target_id)
    {
        $source = Invoice::findOrFail($source_id);

        $global_discount = [
            'amount'         => $source->invoice_discount_amount ?? 0.0,
            'percent'        => $source->invoice_discount_percent ?? 0.0,
            'item'           => 0.0,
            'items_subtotal' => (float) Item::query()->where('invoice_id', $source_id)->sum(DB::raw('item_price * item_quantity')),
        ];

        Invoice::query()->where('invoice_id', $target_id)->update([
            'invoice_discount_percent' => $global_discount['percent'],
            'invoice_discount_amount'  => $global_discount['amount'],
        ]);

        $items = Item::query()->where('invoice_id', $source_id)->get();
        foreach ($items as $invoice_item) {
            Item::create([
                'invoice_id'           => $target_id,
                'item_tax_rate_id'     => $invoice_item->item_tax_rate_id,
                'item_product_id'      => $invoice_item->item_product_id,
                'item_task_id'         => $invoice_item->item_task_id,
                'item_name'            => $invoice_item->item_name,
                'item_description'     => $invoice_item->item_description,
                'item_quantity'        => $invoice_item->item_quantity * -1,
                'item_price'           => $invoice_item->item_price,
                'item_discount_amount' => $invoice_item->item_discount_amount,
                'item_order'           => $invoice_item->item_order,
                'item_is_recurring'    => $invoice_item->item_is_recurring,
                'item_product_unit'    => $invoice_item->item_product_unit,
                'item_product_unit_id' => $invoice_item->item_product_unit_id,
            ]);
        }

        $invoice_tax_rates = InvoiceTaxRate::query()->where('invoice_id', $source_id)->get();
        foreach ($invoice_tax_rates as $invoice_tax_rate) {
            InvoiceTaxRate::create([
                'invoice_id'              => $target_id,
                'tax_rate_id'             => $invoice_tax_rate->tax_rate_id,
                'include_item_tax'        => $invoice_tax_rate->include_item_tax,
                'invoice_tax_rate_amount' => -1 * ($invoice_tax_rate->invoice_tax_rate_amount),
            ]);
        }

        $custom_fields = DB::table('ip_invoice_custom')->where('invoice_id', $source_id)->get();
        foreach ($custom_fields as $field) {
            DB::table('ip_invoice_custom')->insert([
                'invoice_id'                => $target_id,
                'invoice_custom_fieldid'    => $field->invoice_custom_fieldid,
                'invoice_custom_fieldvalue' => $field->invoice_custom_fieldvalue,
            ]);
        }
    }

    /**
     * @param $invoice
     *
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_payments()
     */
    public function get_payments($invoice)
    {
        $payments = DB::table('ip_payments')->where('invoice_id', $invoice->invoice_id)->get();
        $invoice->payments = $payments->isNotEmpty() ? $payments->toArray() : null;
        return $invoice;
    }

    /**
     * @param $invoice_group_id
     *
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_invoice_number()
     */
    public function get_invoice_number($invoice_group_id)
    {
        $invoiceGroup = InvoiceGroup::findOrFail($invoice_group_id);
        return app(InvoiceGroupService::class)->generateInvoiceNumber($invoiceGroup);
    }

    /**
     * @return string
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_url_key()
     */
    public function get_url_key()
    {
        return bin2hex(random_bytes(16));
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
    public function get_invoice_group_id($invoice_id)
    {
        $invoice = Invoice::findOrFail($invoice_id);
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
    public function get_parent_invoice_number($parent_invoice_id)
    {
        $parentInvoice = Invoice::findOrFail($parent_invoice_id);
        return $parentInvoice->invoice_number;
    }

    /**
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_custom_values()
     */
    public function get_custom_values($id)
    {
        $rows = DB::table('ip_invoice_custom')->where('invoice_id', $id)->get();
        if ($rows->isEmpty()) {
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $out[$r->invoice_custom_fieldid] = $r->invoice_custom_fieldvalue;
        }
        return $out;
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_archives()
     */
    public function get_archives($invoice_number): array
    {
        $base = function_exists('uploads_archive_path') ? uploads_archive_path() : storage_path('app/uploads/archive/');
        $files = [];
        if (! empty($invoice_number)) {
            $pattern = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*_*' . $invoice_number . '*.pdf';
            $files = glob($pattern) ?: [];
            return $files;
        }
        $pattern = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.pdf';
        $files = glob($pattern) ?: [];
        rsort($files);
        return $files;
    }

    /**
     * @param $invoice
     *
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_payments()
     */
    public function get_payments_duplicate($invoice)
    {
        $payments = DB::table('ip_payments')->where('invoice_id', $invoice->invoice_id)->get();
        $invoice->payments = $payments->isNotEmpty() ? $payments->toArray() : null;
        return $invoice;
    }

    /**
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_custom_values()
     */
    public function get_custom_values_duplicate($id)
    {
        return $this->get_custom_values($id);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function get_archives()
     */
    public function get_archives_duplicate($invoice_number): array
    {
        return $this->get_archives($invoice_number);
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
    ): LengthAwarePaginator {
        $query = Invoice::query()->with($relations);
        if (is_string($status)) {
            match ($status) {
                'draft'   => $query->draft(),
                'sent'    => $query->sent(),
                'viewed'  => $query->viewed(),
                'paid'    => $query->paid(),
                'unpaid'  => $query->unpaid(),
                'overdue' => $query->overdue(),
                default   => null,
            };
        }
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
    public function getByClientId(int $clientId): Collection
    {
        return Invoice::query()->where('client_id', $clientId)->get();
    }

    /**
     * Update the invoice due date.
     *
     * @param $invoice_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice.php
     *
     * @legacy-function update_invoice_due_date()
     */
    public function update_invoice_due_date($invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        if (! $invoice) {
            return;
        }
        if (($invoice->is_read_only ?? 0) == 1) {
            return;
        }
        $noUpdate = (int) SettingsHelper::getSetting('no_update_invoice_due_date_mail');
        if ($noUpdate === 1) {
            return;
        }
        $current_date = (new DateTime())->format('Y-m-d');
        $newDue = $this->calculateDateDue($current_date);
        Invoice::query()->where('invoice_id', $invoice_id)->update(['invoice_date_due' => $newDue]);
    }
}
