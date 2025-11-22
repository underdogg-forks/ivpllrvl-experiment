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
use Modules\Invoices\Models\InvoiceCustom;
use Modules\Invoices\Models\Payment;
use RuntimeException;

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
        $dueAfter = (int) SettingsHelper::getSetting('invoices_due_after');
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

    public function getInvoiceGroupId(int $invoiceId): int
    {
        $invoice = Invoice::findOrFail($invoiceId);
        return (int) $invoice->invoice_group_id;
    }

    public function getParentInvoiceNumber(int $parentInvoiceId): string
    {
        $parentInvoice = Invoice::findOrFail($parentInvoiceId);
        return (string) $parentInvoice->invoice_number;
    }

    public function delete(int $invoiceId): ?bool
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $deleted = DB::transaction(function () use ($invoice, $invoiceId) {
            $deleted = (bool) $invoice->delete();
            InvoiceAmount::query()->where('invoice_id', $invoiceId)->delete();
            Item::query()->where('invoice_id', $invoiceId)->delete();
            InvoiceTaxRate::query()->where('invoice_id', $invoiceId)->delete();
            DB::table('ip_invoice_custom')->where('invoice_id', $invoiceId)->delete();
            return $deleted;
        });

        return $deleted;
    }

    public function markViewed(int $invoiceId): bool
    {
        $invoice = Invoice::query()->select('invoice_status_id')->where('invoice_id', $invoiceId)->first();
        if (! $invoice || $invoice->invoice_status_id !== 2) {
            return false;
        }
        return Invoice::query()->where('invoice_id', $invoiceId)->update(['invoice_status_id' => 3]) > 0;
    }

    public function markSent(int $invoiceId): bool
    {
        $invoice = Invoice::query()->select('invoice_status_id')->where('invoice_id', $invoiceId)->first();
        if (! $invoice || $invoice->invoice_status_id !== 1) {
            return false;
        }
        return Invoice::query()->where('invoice_id', $invoiceId)->update(['invoice_status_id' => 2]) > 0;
    }

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

    public function getDaysOverdue(Invoice $invoice): int
    {
        if (! $this->isOverdue($invoice)) {
            return 0;
        }
        $dueDate = new DateTime($invoice->invoice_date_due);
        $now     = new DateTime();
        return (int) $now->diff($dueDate)->days;
    }

    public function getOpenInvoices(): Collection
    {
        return Invoice::query()
            ->where('invoice_balance', '>', 0)
            ->with('client')
            ->orderBy('invoice_date_created', 'desc')
            ->get();
    }

    public function createInvoice(array $data): Invoice
    {
        $invoice = Invoice::create($data);
        $invoice->amounts()->create(['invoice_id' => $invoice->invoice_id]);
        return $invoice;
    }

    public function copyInvoice(int $sourceId, int $targetId, bool $copyRecurringOnly = false): void
    {
        $source = Invoice::findOrFail($sourceId);
        $globalDiscount = [
            'amount'         => $source->invoice_discount_amount ?? 0.0,
            'percent'        => $source->invoice_discount_percent ?? 0.0,
            'item'           => 0.0,
            'items_subtotal' => (float) Item::query()->where('invoice_id', $sourceId)->sum(DB::raw('item_price * item_quantity')),
        ];

        Invoice::query()->where('invoice_id', $targetId)->update([
            'invoice_discount_percent' => $globalDiscount['percent'],
            'invoice_discount_amount'  => $globalDiscount['amount'],
        ]);

        $items = Item::query()->where('invoice_id', $sourceId)->get();
        foreach ($items as $item) {
            if ($copyRecurringOnly && ! (bool) $item->item_is_recurring) {
                continue;
            }

            Item::create([
                'invoice_id'           => $targetId,
                'item_tax_rate_id'     => $item->item_tax_rate_id,
                'item_product_id'      => $item->item_product_id,
                'item_task_id'         => $item->item_task_id,
                'item_name'            => $item->item_name,
                'item_description'     => $item->item_description,
                'item_quantity'        => $item->item_quantity,
                'item_price'           => $item->item_price,
                'item_discount_amount' => $item->item_discount_amount,
                'item_order'           => $item->item_order,
                'item_is_recurring'    => $item->item_is_recurring,
                'item_product_unit'    => $item->item_product_unit,
                'item_product_unit_id' => $item->item_product_unit_id,
            ]);
        }

        $taxRates = InvoiceTaxRate::query()->where('invoice_id', $sourceId)->get();
        foreach ($taxRates as $tax) {
            InvoiceTaxRate::create([
                'invoice_id'                 => $targetId,
                'tax_rate_id'                => $tax->tax_rate_id,
                'include_item_tax'           => $tax->include_item_tax,
                'invoice_tax_rate_amount'    => $tax->invoice_tax_rate_amount,
            ]);
        }

        $custom = DB::table('ip_invoice_custom')->where('invoice_id', $sourceId)->get();
        foreach ($custom as $field) {
            DB::table('ip_invoice_custom')->insert([
                'invoice_id'                => $targetId,
                'invoice_custom_fieldid'    => $field->invoice_custom_fieldid,
                'invoice_custom_fieldvalue' => $field->invoice_custom_fieldvalue,
            ]);
        }
    }

    public function copyCreditInvoice(int $sourceId, int $targetId): void
    {
        $source = Invoice::findOrFail($sourceId);
        $globalDiscount = [
            'amount'         => $source->invoice_discount_amount ?? 0.0,
            'percent'        => $source->invoice_discount_percent ?? 0.0,
            'item'           => 0.0,
            'items_subtotal' => (float) Item::query()->where('invoice_id', $sourceId)->sum(DB::raw('item_price * item_quantity')),
        ];

        Invoice::query()->where('invoice_id', $targetId)->update([
            'invoice_discount_percent' => $globalDiscount['percent'],
            'invoice_discount_amount'  => $globalDiscount['amount'],
        ]);

        $items = Item::query()->where('invoice_id', $sourceId)->get();
        foreach ($items as $item) {
            Item::create([
                'invoice_id'           => $targetId,
                'item_tax_rate_id'     => $item->item_tax_rate_id,
                'item_product_id'      => $item->item_product_id,
                'item_task_id'         => $item->item_task_id,
                'item_name'            => $item->item_name,
                'item_description'     => $item->item_description,
                'item_quantity'        => $item->item_quantity * -1,
                'item_price'           => $item->item_price,
                'item_discount_amount' => $item->item_discount_amount,
                'item_order'           => $item->item_order,
                'item_is_recurring'    => $item->item_is_recurring,
                'item_product_unit'    => $item->item_product_unit,
                'item_product_unit_id' => $item->item_product_unit_id,
            ]);
        }

        $taxRates = InvoiceTaxRate::query()->where('invoice_id', $sourceId)->get();
        foreach ($taxRates as $tax) {
            InvoiceTaxRate::create([
                'invoice_id'              => $targetId,
                'tax_rate_id'             => $tax->tax_rate_id,
                'include_item_tax'        => $tax->include_item_tax,
                'invoice_tax_rate_amount' => -1 * ($tax->invoice_tax_rate_amount),
            ]);
        }

        $custom = DB::table('ip_invoice_custom')->where('invoice_id', $sourceId)->get();
        foreach ($custom as $field) {
            DB::table('ip_invoice_custom')->insert([
                'invoice_id'                => $targetId,
                'invoice_custom_fieldid'    => $field->invoice_custom_fieldid,
                'invoice_custom_fieldvalue' => $field->invoice_custom_fieldvalue,
            ]);
        }
    }

    public function getPaymentsForInvoice(Invoice $invoice): ?Collection
    {
        $payments = DB::table('ip_payments')->where('invoice_id', $invoice->invoice_id)->get();
        if ($payments->isEmpty()) {
            return null;
        }
        return collect($payments);
    }

    public function getInvoiceNumberFromGroup(int $invoiceGroupId): string
    {
        $invoiceGroup = InvoiceGroup::findOrFail($invoiceGroupId);
        return app(InvoiceGroupService::class)->generateInvoiceNumber($invoiceGroup);
    }

    public function getUrlKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getInvoiceGroupIdById(int $invoiceId): int
    {
        $invoice = Invoice::findOrFail($invoiceId);
        return (int) $invoice->invoice_group_id;
    }

    public function getParentInvoiceNumberById(int $parentInvoiceId): string
    {
        $parentInvoice = Invoice::findOrFail($parentInvoiceId);
        return (string) $parentInvoice->invoice_number;
    }

    public function getCustomValues(int $invoiceId): array
    {
        $rows = DB::table('ip_invoice_custom')->where('invoice_id', $invoiceId)->get();
        if ($rows->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row->invoice_custom_fieldid] = $row->invoice_custom_fieldvalue;
        }

        return $result;
    }

    public function getArchives(?string $invoiceNumber = null): array
    {
        $base = function_exists('uploads_archive_path') ? uploads_archive_path() : storage_path('app/uploads/archive/');
        $files = [];

        if ($invoiceNumber !== null && $invoiceNumber !== '') {
            $pattern = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*_*' . $invoiceNumber . '*.pdf';
            $files = glob($pattern);
            if ($files === false) {
                return [];
            }
            return $files;
        }

        $pattern = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.pdf';
        $files = glob($pattern) ?: [];
        rsort($files);
        return $files;
    }

    public function updateInvoice(int $invoiceId, array $data): int
    {
        return Invoice::query()->where('invoice_id', $invoiceId)->update($data);
    }

    public function findWithRelations(int $id, array $relations = ['client', 'user']): ?Invoice
    {
        return Invoice::query()->with($relations)->find($id);
    }

    public function findWithRelationsOrFail(int $id, array $relations = ['client', 'user']): Invoice
    {
        return Invoice::query()->with($relations)->findOrFail($id);
    }

    public function getAllWithRelations(array $relations = ['client', 'user'], ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
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

    public function getByClientId(int $clientId): Collection
    {
        return Invoice::query()->where('client_id', $clientId)->get();
    }

    public function updateInvoiceDueDate(int $invoiceId): void
    {
        $invoice = Invoice::find($invoiceId);
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

        $currentDate = (new DateTime())->format('Y-m-d');
        $newDue = $this->calculateDateDue($currentDate);
        Invoice::query()->where('invoice_id', $invoiceId)->update(['invoice_date_due' => $newDue]);
    }
}
