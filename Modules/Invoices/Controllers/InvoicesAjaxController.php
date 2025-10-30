<?php



namespace Modules\Invoices\Controllers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\Item;
use Modules\Invoices\Models\InvoiceTaxRate;
use Modules\Invoices\Models\InvoicesRecurring;
use Modules\Crm\Models\Client;
use Modules\Users\Models\User;
use Modules\Products\Models\Unit;
use Modules\Core\Models\InvoiceCustom;

/**
 * AJAX controller for invoice operations
 *
 * Handles AJAX requests for invoice management including creation, saving,
 * copying, and conversion operations.
 *
 * @legacy-file application/modules/invoices/controllers/Ajax.php
 */
class InvoicesAjaxController
{
    /**
     * Save invoice with items, tax rates, and custom fields
     *
     * @return array JSON response with success/error status
     *
     * @legacy-function save
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 21
     */
    public function save(): array
    {
        $invoiceId = request()->input('invoice_id');
        $invoice = Invoice::query()->findOrFail($invoiceId);

        // Validate invoice
        $validationRules = Invoice::validationRulesSaveInvoice();
        $validator = validator(request()->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'success' => 0,
                'validation_errors' => $validator->errors()->toArray()
            ];
        }

        // Get items from JSON
        $items = json_decode(request()->input('items'), true);

        // Handle discount precedence - percent by default, prevent both
        $invoiceDiscountPercent = (float) request()->input('invoice_discount_percent', 0);
        $invoiceDiscountAmount = (float) request()->input('invoice_discount_amount', 0);

        if ($invoiceDiscountPercent && $invoiceDiscountAmount) {
            $invoiceDiscountAmount = 0.0;
        }

        // Calculate items subtotal for global discount distribution
        $itemsSubtotal = 0.0;
        if ($invoiceDiscountAmount) {
            foreach ($items as $item) {
                if (!empty($item['item_name'])) {
                    $itemsSubtotal += $item['item_quantity'] * $item['item_price'];
                }
            }
        }

        $globalDiscount = [
            'amount' => $invoiceDiscountAmount,
            'percent' => $invoiceDiscountPercent,
            'item' => 0.0,
            'items_subtotal' => $itemsSubtotal,
        ];

        // Save each item
        foreach ($items as $item) {
            if (!empty($item['item_name'])) {
                // Validation: prevent quantity/price without name
                if (empty($item['item_name']) && (!empty($item['item_quantity']) || !empty($item['item_price']))) {
                    return [
                        'success' => 0,
                        'validation_errors' => ['item_name' => ['The item field is required.']]
                    ];
                }

                $itemId = $item['item_id'] ?? null;
                Item::saveItem($itemId, $item, $invoiceId, $globalDiscount);
            }
        }

        // Update invoice fields
        $invoice->update(request()->only([
            'invoice_number',
            'invoice_date_created',
            'invoice_date_due',
            'invoice_status_id',
            'invoice_password',
            'invoice_discount_amount',
            'invoice_discount_percent',
            'invoice_terms',
        ]));

        // Save custom fields
        InvoiceCustom::saveCustomFields($invoiceId, request()->input('custom'));

        // Recalculate amounts
        $invoice->recalculate();

        return ['success' => 1];
    }

    /**
     * Save invoice tax rate (legacy calculation mode)
     *
     * @return array JSON response
     *
     * @legacy-function save_invoice_tax_rate
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 220
     */
    public function saveInvoiceTaxRate(): array
    {
        $invoiceId = request()->input('invoice_id');
        $taxRateId = request()->input('tax_rate_id');
        $includeItemTax = request()->input('include_item_tax', 0);

        InvoiceTaxRate::saveTaxRate($invoiceId, $taxRateId, $includeItemTax);

        return ['success' => 1];
    }

    /**
     * Delete invoice item and recalculate invoice
     *
     * @param int $invoiceId Invoice ID
     * @return array JSON response
     *
     * @legacy-function delete_item
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 244
     */
    public function deleteItem(int $invoiceId): array
    {
        $itemId = request()->input('item_id');

        $item = Item::query()->where('invoice_id', $invoiceId)
            ->where('item_id', $itemId)
            ->first();

        if (!$item) {
            return ['success' => 0];
        }

        Item::deleteItem($itemId);

        // Recalculate invoice
        $invoice = Invoice::query()->findOrFail($invoiceId);
        $invoice->recalculate();

        return ['success' => 1];
    }

    /**
     * Get invoice item data
     *
     * @return array Item data
     *
     * @legacy-function get_item
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 271
     */
    public function getItem(): array
    {
        $itemId = request()->input('item_id');
        $item = Item::query()->find($itemId);

        return $item ? $item->toArray() : [];
    }

    /**
     * Display modal for copying invoice
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_copy_invoice
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 280
     */
    public function modalCopyInvoice()
    {
        $invoiceId = request()->input('invoice_id');
        $invoice = Invoice::with(['client', 'user'])->findOrFail($invoiceId);

        $clients = Client::query()->orderBy('client_name')->get();
        $users = User::query()->all();

        return view('invoices::modal_copy_invoice', compact('invoice', 'clients', 'users'));
    }

    /**
     * Copy invoice with all related data
     *
     * @return array JSON response with new invoice URL
     *
     * @legacy-function copy_invoice
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 302
     */
    public function copyInvoice(): array
    {
        $sourceId = request()->input('invoice_id');
        $clientId = request()->input('client_id');
        $userId = request()->input('user_id');
        $invoiceDate = request()->input('invoice_date_created');
        $includeInvoiceTaxRates = request()->input('invoice_change_client', 0) == 0;

        // Create new invoice
        $newInvoice = Invoice::query()->create([
            'client_id' => $clientId,
            'user_id' => $userId,
            'invoice_date_created' => $invoiceDate,
            'invoice_status_id' => 1, // Draft
        ]);

        // Copy invoice data
        Invoice::copyInvoice($sourceId, $newInvoice->invoice_id, $includeInvoiceTaxRates);

        return [
            'success' => 1,
            'invoice_id' => $newInvoice->invoice_id,
        ];
    }

    /**
     * Display modal for changing invoice user
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_change_user
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 337
     */
    public function modalChangeUser()
    {
        $invoiceId = request()->input('invoice_id');
        $invoice = Invoice::query()->findOrFail($invoiceId);
        $users = User::query()->all();

        return view('invoices::modal_change_user', compact('invoice', 'users'));
    }

    /**
     * Change invoice user
     *
     * @return array JSON response
     *
     * @legacy-function change_user
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 351
     */
    public function changeUser(): array
    {
        $invoiceId = request()->input('invoice_id');
        $userId = request()->input('user_id');

        $user = User::query()->find($userId);
        if (!$user) {
            return ['success' => 0, 'error' => 'User not found'];
        }

        Invoice::query()->where('invoice_id', $invoiceId)->update(['user_id' => $userId]);

        return ['success' => 1];
    }

    /**
     * Display modal for changing invoice client
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_change_client
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 386
     */
    public function modalChangeClient()
    {
        $invoiceId = request()->input('invoice_id');
        $invoice = Invoice::with('client')->findOrFail($invoiceId);
        $clients = Client::query()->orderBy('client_name')->get();

        return view('invoices::modal_change_client', compact('invoice', 'clients'));
    }

    /**
     * Change invoice client
     *
     * @return array JSON response
     *
     * @legacy-function change_client
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 400
     */
    public function changeClient(): array
    {
        $invoiceId = request()->input('invoice_id');
        $clientId = request()->input('client_id');

        $client = Client::query()->find($clientId);
        if (!$client) {
            return ['success' => 0, 'error' => 'Client not found'];
        }

        Invoice::query()->where('invoice_id', $invoiceId)->update(['client_id' => $clientId]);

        return ['success' => 1];
    }

    /**
     * Display modal for creating new invoice
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_create_invoice
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 435
     */
    public function modalCreateInvoice()
    {
        $clients = Client::query()->orderBy('client_name')->get();
        $users = User::query()->all();

        return view('invoices::modal_create_invoice', compact('clients', 'users'));
    }

    /**
     * Create new invoice
     *
     * @return array JSON response with new invoice ID
     *
     * @legacy-function create
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 454
     */
    public function create(): array
    {
        $invoice = Invoice::query()->create([
            'client_id' => request()->input('client_id'),
            'user_id' => request()->input('user_id'),
            'invoice_date_created' => request()->input('invoice_date_created'),
            'invoice_status_id' => 1, // Draft
        ]);

        return [
            'success' => 1,
            'invoice_id' => $invoice->invoice_id,
        ];
    }

    /**
     * Create recurring invoice
     *
     * @return array JSON response with recurring invoice ID
     *
     * @legacy-function create_recurring
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 476
     */
    public function createRecurring(): array
    {
        $recurring = InvoicesRecurring::query()->create([
            'client_id' => request()->input('client_id'),
            'user_id' => request()->input('user_id'),
            'invoice_group_id' => request()->input('invoice_group_id'),
            'recur_start_date' => request()->input('recur_start_date'),
            'recur_end_date' => request()->input('recur_end_date'),
            'recur_frequency' => request()->input('recur_frequency'),
        ]);

        return [
            'success' => 1,
            'invoice_recurring_id' => $recurring->invoice_recurring_id,
        ];
    }

    /**
     * Display modal for creating recurring invoice
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_create_recurring
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 497
     */
    public function modalCreateRecurring()
    {
        $clients = Client::query()->orderBy('client_name')->get();
        $users = User::query()->all();

        return view('invoices::modal_create_recurring', compact('clients', 'users'));
    }

    /**
     * Get recurring start date based on frequency
     *
     * @return array JSON response with start date
     *
     * @legacy-function get_recur_start_date
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 511
     */
    public function getRecurStartDate(): array
    {
        $frequency = request()->input('recur_frequency');
        $startDate = date('Y-m-d');

        // Calculate start date based on frequency
        switch ($frequency) {
            case '1M':
                $startDate = date('Y-m-d', strtotime('+1 month'));
                break;
            case '3M':
                $startDate = date('Y-m-d', strtotime('+3 months'));
                break;
            case '6M':
                $startDate = date('Y-m-d', strtotime('+6 months'));
                break;
            case '1Y':
                $startDate = date('Y-m-d', strtotime('+1 year'));
                break;
        }

        return ['recur_start_date' => $startDate];
    }

    /**
     * Display modal for creating credit invoice
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_create_credit
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 519
     */
    public function modalCreateCredit()
    {
        $invoiceId = request()->input('invoice_id');
        $invoice = Invoice::with(['client', 'user'])->findOrFail($invoiceId);

        return view('invoices::modal_create_credit', compact('invoice'));
    }

    /**
     * Create credit invoice from existing invoice
     *
     * @return array JSON response with credit invoice ID
     *
     * @legacy-function create_credit
     * @legacy-file application/modules/invoices/controllers/Ajax.php
     * @legacy-line 538
     */
    public function createCredit(): array
    {
        $sourceId = request()->input('invoice_id');
        $creditDate = request()->input('invoice_date_created');

        $sourceInvoice = Invoice::query()->findOrFail($sourceId);

        // Create credit invoice
        $creditInvoice = Invoice::query()->create([
            'client_id' => $sourceInvoice->client_id,
            'user_id' => $sourceInvoice->user_id,
            'invoice_date_created' => $creditDate,
            'invoice_status_id' => 1, // Draft
            'is_read_only' => 0,
        ]);

        // Copy invoice data as credit (negative amounts)
        Invoice::copyCreditInvoice($sourceId, $creditInvoice->invoice_id);

        return [
            'success' => 1,
            'invoice_id' => $creditInvoice->invoice_id,
        ];
    }
}
