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

        InvoiceAmount::query()->where('invoice_id', $invoiceId)->delete();
        Item::query()->where('invoice_id', $invoiceId)->delete();
        InvoiceTaxRate::query()->where('invoice_id', $invoiceId)->delete();

        return $deleted;
    }

    public function markViewed(int $invoiceId): bool
    {
        $invoice = Invoice::query()->select('invoice_status_id')
            ->where('invoice_id', $invoiceId)
            ->first();

                if (!$invoice || $invoice->invoice_status_id !== 2) {
            return false;
        }

        return Invoice::query()->where('invoice_id', $invoiceId)
            ->update(['invoice_status_id' => 3]) > 0;
    }

    public function markSent(int $invoiceId): bool
    {
        $invoice = Invoice::query()->select('invoice_status_id')
            ->where('invoice_id', $invoiceId)
            ->first();

                if (!$invoice || $invoice->invoice_status_id !== 1) {
            return false;
        }

        return Invoice::query()->where('invoice_id', $invoiceId)
            ->update(['invoice_status_id' => 2]) > 0;
    }

        public function generateInvoiceNumberIfApplicable(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $generateForDraft = SettingsHelper::getSetting('generate_invoice_number_for_draft');
        
        if ($invoice->invoice_status_id !== 1 || !empty($invoice->invoice_number) || $generateForDraft != 0) {
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

    public function getDaysOverdue(Invoice $invoice): int
    {
        if (! $this->isOverdue($invoice)) {
            return 0;
        }

        $dueDate = new DateTime($invoice->invoice_date_due);
        $now     = new DateTime();

        return $now->diff($dueDate)->days;
        }

    public function getOpenInvoices()
    {
        return Invoice::query()->where('invoice_balance', '>', 0)
            ->with('client')
            ->orderBy('invoice_date_created', 'desc')
            ->get();
    }

    public function createInvoice(array $data): Invoice
    {
        $invoice = Invoice::create($data);

        // Create invoice amount record
        $invoice->amounts()->create([
            'invoice_id' => $invoice->invoice_id,
        ]);

        return $invoice;
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
     * @param int $id Invoice ID
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
     * @param int $id Invoice ID
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
     * @param array $relations Relations to eager load
     * @param string|null $status Status filter
     * @param int $perPage Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(
        array $relations = ['client', 'user'],
        ?string $status = null,
        int $perPage = 15
    ) {
        $query = Invoice::query()->with($relations);

        // Apply status filter using scopes
        match ($status) {
            'draft' => $query->draft(),
            'sent' => $query->sent(),
            'viewed' => $query->viewed(),
            'paid' => $query->paid(),
            'unpaid' => $query->unpaid(),
            'overdue' => $query->overdue(),
            default => null
        };

        return $query->orderBy('invoice_date_created', 'desc')->paginate($perPage);
    }

    /**
     * Get all invoices for a specific client.
     *
     * @param int $clientId Client ID
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByClientId(int $clientId): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::query()->where('client_id', $clientId)->get();
    }
}

