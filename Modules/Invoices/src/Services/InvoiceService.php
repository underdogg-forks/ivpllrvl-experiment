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

        // Create invoice amount record
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
    public function copy_invoice($source_id, $target_id, $copy_recurring_items_only = false): void
    {
/*
        $this->load->model('invoices/item');
        $this->load->model('invoices/invoice_tax_rate');

        // Discounts calculation - since v1.6.3 Need if taxes applied after discounts
        $invoice         = $this->get_by_id($source_id); // This is the original invoice
        $global_discount = [
            'amount'         => $invoice->invoice_discount_amount,
            'percent'        => $invoice->invoice_discount_percent,
            'item'           => 0.0, // Updated by ref (Need for invoice_item_subtotal calculation in Mdl_invoice_amounts)
            'items_subtotal' => $this->mdl_items->get_items_subtotal($source_id),
        ];
        unset($invoice); // Free memory

        // Update the discounts - since v1.6.3
        $this->where('invoice_id', $target_id)->update('ip_invoices', [
            'invoice_discount_percent' => $global_discount['percent'],
            'invoice_discount_amount'  => $global_discount['amount'],
        ]);

        // Copy the items
        $invoice_items = $this->mdl_items->where('invoice_id', $source_id)->get()->result();

        foreach ($invoice_items as $invoice_item) {
            $db_array = [
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
            ];

            if ( ! $copy_recurring_items_only || $invoice_item->item_is_recurring) {
                $this->mdl_items->save(null, $db_array, $global_discount);
            }
        }

        // Copy the tax rates
        $invoice_tax_rates = $this->mdl_invoice_tax_rates->where('invoice_id', $source_id)->get()->result();

        foreach ($invoice_tax_rates as $invoice_tax_rate) {
            $db_array = [
                'invoice_id'              => $target_id,
                'tax_rate_id'             => $invoice_tax_rate->tax_rate_id,
                'include_item_tax'        => $invoice_tax_rate->include_item_tax,
                'invoice_tax_rate_amount' => $invoice_tax_rate->invoice_tax_rate_amount,
            ];

            $this->mdl_invoice_tax_rates->save(null, $db_array);
        }

        // Copy the custom fields
        $this->load->model('custom_fields/invoice_custom');
        $custom_fields = $this->mdl_invoice_custom->where('invoice_id', $source_id)->get()->result();

        $form_data = [];
        foreach ($custom_fields as $field) {
            $form_data[$field->invoice_custom_fieldid] = $field->invoice_custom_fieldvalue;
        }

        $this->mdl_invoice_custom->save_custom($target_id, $form_data);
*/
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
/*
        $this->load->model('invoices/item');
        $this->load->model('invoices/invoice_tax_rate');

        // Discounts calculation - since v1.6.3 Need if taxes applied after discounts
        $invoice         = $this->get_by_id($source_id); // This is the original invoice
        $global_discount = [
            'amount'         => $invoice->invoice_discount_amount,
            'percent'        => $invoice->invoice_discount_percent,
            'item'           => 0.0, // Updated by ref (Need for invoice_item_subtotal calculation in Mdl_invoice_amounts)
            'items_subtotal' => $this->mdl_items->get_items_subtotal($source_id),
        ];

        // Update the discounts - since v1.6.3
        $this->where('invoice_id', $target_id)->update('ip_invoices', [
            'invoice_discount_percent' => $global_discount['percent'],
            'invoice_discount_amount'  => $global_discount['amount'],
        ]);

        unset($invoice); // Free memory

        $invoice_items = $this->mdl_items->where('invoice_id', $source_id)->get()->result();

        foreach ($invoice_items as $invoice_item) {
            $db_array = [
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
            ];

            $this->mdl_items->save(null, $db_array, $global_discount);
        }

        $invoice_tax_rates = $this->mdl_invoice_tax_rates->where('invoice_id', $source_id)->get()->result();

        foreach ($invoice_tax_rates as $invoice_tax_rate) {
            $db_array = [
                'invoice_id'              => $target_id,
                'tax_rate_id'             => $invoice_tax_rate->tax_rate_id,
                'include_item_tax'        => $invoice_tax_rate->include_item_tax,
                'invoice_tax_rate_amount' => -$invoice_tax_rate->invoice_tax_rate_amount,
            ];

            $this->mdl_invoice_tax_rates->save(null, $db_array);
        }

        // Copy the custom fields
        $this->load->model('custom_fields/invoice_custom');
        $custom_fields = $this->mdl_invoice_custom->where('invoice_id', $source_id)->get()->result();

        $form_data = [];
        foreach ($custom_fields as $field) {
            $form_data[$field->invoice_custom_fieldid] = $field->invoice_custom_fieldvalue;
        }

        $this->mdl_invoice_custom->save_custom($target_id, $form_data);
*/
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
/*
        $this->load->model('payments/payment');

        $this->db->where('invoice_id', $invoice->invoice_id);
        $payment_results = $this->db->get('ip_payments');

        $invoice->payments = $payment_results->num_rows() > 0 ? $payment_results->result() : null;

        return $invoice;
*/
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
/*
        $this->load->model('invoice_groups/invoice_group');

        return $this->mdl_invoice_groups->generate_invoice_number($invoice_group_id);
*/
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
/*
        $this->load->helper('string');

        return random_string('alnum', 32);
*/
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
/*
        $invoice = $this->get_by_id($invoice_id);

        return $invoice->invoice_group_id;
*/
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
/*
        $parent_invoice = $this->get_by_id($parent_invoice_id);

        return $parent_invoice->invoice_number;
*/
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
/*
        $this->load->module('custom_fields/Mdl_invoice_custom');

        return $this->invoice_custom->get_by_invid($id);
*/
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
/*
        $invoice_array = [];

        if ( ! empty($invoice_number)) {
            $invoice_array = glob(uploads_archive_path() . '*_*' . $invoice_number . '*.pdf');
        } else {
            foreach (glob(uploads_archive_path() . '*.pdf') as $file) {
                $invoice_array[] = $file;
            }

            rsort($invoice_array);
        }

        return $invoice_array;
*/
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
/*
        $this->load->model('payments/payment');

        $this->db->where('invoice_id', $invoice->invoice_id);
        $payment_results = $this->db->get('ip_payments');

        $invoice->payments = $payment_results->num_rows() > 0 ? $payment_results->result() : null;

        return $invoice;
*/
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
/*
        $this->load->module('custom_fields/Mdl_invoice_custom');

        return $this->invoice_custom->get_by_invid($id);
*/
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
/*
        $invoice_array = [];

        if ( ! empty($invoice_number)) {
            $invoice_array = glob(uploads_archive_path() . '*_*' . $invoice_number . '*.pdf');
        } else {
            foreach (glob(uploads_archive_path() . '*.pdf') as $file) {
                $invoice_array[] = $file;
            }

            rsort($invoice_array);
        }

        return $invoice_array;
*/
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

        // Apply status filter using scopes
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
        $invoice = $this->get_by_id($invoice_id);

        if ( ! empty($invoice) && $invoice->is_read_only != 1 && get_setting('no_update_invoice_due_date_mail') == 0) {
            $current_date = date_to_mysql(date(date_format_setting()));
            $this->db->where('invoice_id', $invoice_id);
            $this->db->set('invoice_date_due', $this->get_date_due($current_date));
            $this->db->update('ip_invoices');
        }
    }
}
