<?php

namespace Modules\Quotes\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Models\QuoteCustom;
use Modules\Core\Models\User;
use Modules\Core\Services\UserService;
use Modules\Crm\Models\Client;
use Modules\Crm\Services\ClientService;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceGroupService;
use Modules\Invoices\Services\InvoiceItemService;
use Modules\Invoices\Services\InvoiceService;
use Modules\Invoices\Services\InvoiceTaxRateService;
use Modules\Products\Services\TaxRateService;
use Modules\Products\Services\UnitService;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteAmount;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteTaxRate;
use Modules\Quotes\Services\QuoteAmountService;
use Modules\Quotes\Services\QuoteItemService;
use Modules\Quotes\Services\QuoteService;
use Modules\Quotes\Services\QuoteTaxRateService;

/**
 * QuotesAjaxController.
 *
 * Handles AJAX operations for quotes including create, save, copy, and convert to invoice
 */
class QuotesAjaxController
{
    public function __construct(
        protected QuoteService $quoteService,
        protected QuoteAmountService $quoteAmountService,
        protected QuoteItemService $quoteItemService,
        protected QuoteTaxRateService $quoteTaxRateService,
        protected UnitService $unitService,
        protected InvoiceService $invoiceService,
        protected UserService $userService,
        protected ClientService $clientService,
        protected InvoiceGroupService $invoiceGroupService,
        protected TaxRateService $taxRateService
    ) {}

    /**
     * Save quote with items, tax rates, and custom fields.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @legacy-function save
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 21
     */
    public function save(Request $request): JsonResponse
    {
        $quoteId = (int) $request->input('quote_id');
        $quote   = Quote::findOrFail($quoteId);

        // Validate quote
        $validation = $this->quoteService->getSaveValidationRules($quoteId);
        $validator  = validator($request->all(), $validation);

        if ($validator->fails()) {
            return response()->json([
                'success'           => 0,
                'validation_errors' => $validator->errors()->toArray(),
            ]);
        }

        // Process items
        $items                = json_decode($request->input('items'), false);
        $quoteDiscountPercent = (float) $request->input('quote_discount_percent', 0);
        $quoteDiscountAmount  = (float) $request->input('quote_discount_amount', 0);

        // Only one discount allowed
        if ($quoteDiscountPercent && $quoteDiscountAmount) {
            $quoteDiscountAmount = 0.0;
        }

        // Calculate items subtotal for global discount distribution
        $itemsSubtotal = 0.0;
        if ($quoteDiscountAmount) {
            foreach ($items as $item) {
                if ( ! empty($item->item_name)) {
                    $itemsSubtotal += $item->item_quantity * $item->item_price;
                }
            }
        }

        $globalDiscount = [
            'amount'         => $quoteDiscountAmount,
            'percent'        => $quoteDiscountPercent,
            'item'           => 0.0,
            'items_subtotal' => $itemsSubtotal,
        ];

        // Save each item
        foreach ($items as $item) {
            if ( ! empty($item->item_name)) {
                $itemData = [
                    'quote_id'             => $quoteId,
                    'item_tax_rate_id'     => $item->item_tax_rate_id ?? null,
                    'item_product_id'      => $item->item_product_id ?? null,
                    'item_name'            => $item->item_name,
                    'item_description'     => $item->item_description ?? '',
                    'item_quantity'        => $item->item_quantity ?? 0,
                    'item_price'           => $item->item_price ?? 0,
                    'item_discount_amount' => $item->item_discount_amount ?? null,
                    'item_product_unit_id' => $item->item_product_unit_id ?? null,
                    'item_product_unit'    => $this->unitService->getUnitName($item->item_product_unit_id ?? null, $item->item_quantity ?? 1),
                    'item_order'           => $item->item_order ?? 0,
                ];

                $itemId = $item->item_id ?? null;
                if ($itemId) {
                    $itemData['item_id'] = $itemId;
                }
                $this->quoteItemService->saveItem($itemData, $globalDiscount);
            } elseif (empty($item->item_name) && ( ! empty($item->item_quantity) || ! empty($item->item_price))) {
                return response()->json([
                    'success'           => 0,
                    'validation_errors' => ['item_name' => 'The item name field is required.'],
                ]);
            }
        }

        // Generate quote number if needed
        $quoteNumber   = $request->input('quote_number');
        $quoteStatusId = $request->input('quote_status_id');

        if (empty($quoteNumber) && $quoteStatusId != 1) {
            $quoteGroupId = $quote->invoice_group_id;
            $quoteNumber  = $this->quoteService->generateQuoteNumber($quoteGroupId);
        }

        // Adjust discount for non-legacy calculation
        if ( ! config('legacy_calculation') && $quoteDiscountAmount && $quoteDiscountAmount != $globalDiscount['item']) {
            $quoteDiscountAmount = $globalDiscount['item'];
        }

        // Save quote
        $quote->update([
            'quote_number'           => $quoteNumber,
            'quote_status_id'        => $quoteStatusId,
            'quote_date_created'     => $request->input('quote_date_created'),
            'quote_date_expires'     => $request->input('quote_date_expires'),
            'quote_password'         => $request->input('quote_password'),
            'notes'                  => $request->input('notes'),
            'quote_discount_amount'  => $quoteDiscountAmount,
            'quote_discount_percent' => $quoteDiscountPercent,
        ]);

        // Recalculate amounts for legacy mode
        if (config('legacy_calculation')) {
            QuoteAmount::calculate($quoteId, $globalDiscount);
        }

        // Save custom fields
        if ($request->input('custom')) {
            $customData = [];
            $values     = [];

            foreach ($request->input('custom') as $custom) {
                if (preg_match("/^(.*)\[\]$/i", $custom['name'], $matches)) {
                    $values[$matches[1]][] = $custom['value'];
                } else {
                    $values[$custom['name']] = $custom['value'];
                }
            }

            foreach ($values as $key => $value) {
                if (preg_match("/^custom\[(.*?)\](?:\[\]|)$/", $key, $matches)) {
                    $customData[$matches[1]] = $value;
                }
            }

            $result = QuoteCustom::saveCustom($quoteId, $customData);
            if ($result !== true) {
                return response()->json([
                    'success'           => 0,
                    'validation_errors' => $result,
                ]);
            }
        }

        return response()->json(['success' => 1]);
    }

    /**
     * Save quote tax rate (legacy calculation only).
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @legacy-function save_quote_tax_rate
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 183
     */
    public function saveQuoteTaxRate(Request $request): JsonResponse
    {
        $validation = $this->quoteTaxRateService->getValidationRules();
        $validator  = validator($request->all(), $validation);

        if ($validator->fails()) {
            return response()->json([
                'success'           => 0,
                'validation_errors' => $validator->errors()->toArray(),
            ]);
        }

        // Only save for legacy calculation mode
        if (config('legacy_calculation')) {
            QuoteTaxRate::saveTaxRate($request->all());
        }

        return response()->json(['success' => 1]);
    }

    /**
     * Delete a quote item.
     *
     * @param Request $request
     * @param int     $quoteId
     *
     * @return JsonResponse
     *
     * @legacy-function delete_item
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 207
     */
    public function deleteItem(Request $request, int $quoteId): JsonResponse
    {
        $success = 0;
        $itemId  = (int) $request->input('item_id');

        // Verify quote exists
        $quote = $this->quoteService->find($quoteId);
        if ($quote && ! empty($itemId)) {
            $deleted = QuoteItem::deleteItem($itemId);
            if ($deleted) {
                $success = 1;
            }
        }

        return response()->json(['success' => $success]);
    }

    /**
     * Get a quote item by ID.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @legacy-function get_item
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 229
     */
    public function getItem(Request $request): JsonResponse
    {
        $itemId = (int) $request->input('item_id');
        $item   = $this->quoteItemService->find($itemId);

        return response()->json($item ?? []);
    }

    /**
     * Display modal for copying a quote.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_copy_quote
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 238
     */
    public function modalCopyQuote(Request $request)
    {
        $quoteId  = (int) $request->input('quote_id');
        $clientId = (int) $request->input('client_id');

        $quote  = $this->quoteService->findWithRelationsOrFail($quoteId, ['client']);
        $client = $this->clientService->find($clientId);

        $data = [
            'invoice_groups' => $this->invoiceGroupService->getAll(),
            'tax_rates'      => $this->taxRateService->getAll(),
            'quote_id'       => $quoteId,
            'quote'          => $quote,
            'client'         => $client,
        ];

        return view('quotes::modal_copy_quote', $data);
    }

    /**
     * Copy a quote with all items, tax rates, and custom fields.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @legacy-function copy_quote
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 259
     */
    public function copyQuote(Request $request): JsonResponse
    {
        $validation = $this->quoteService->getValidationRules();
        $validator  = validator($request->all(), $validation);

        if ($validator->fails()) {
            return response()->json([
                'success'           => 0,
                'validation_errors' => $validator->errors()->toArray(),
            ]);
        }

        // Create new quote
        $targetId = $this->quoteService->createQuote($request->all())->quote_id;
        $sourceId = (int) $request->input('quote_id');

        // Copy all related data
        Quote::copyQuote($sourceId, $targetId);

        return response()->json([
            'success'  => 1,
            'quote_id' => $targetId,
        ]);
    }

    /**
     * Display modal for changing quote user.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_change_user
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 294
     */
    public function modalChangeUser(Request $request)
    {
        $userId  = (int) $request->input('user_id');
        $quoteId = (int) $request->input('quote_id');

        $data = [
            'user_id'  => $userId,
            'quote_id' => $quoteId,
            'users'    => User::latest()->get(),
        ];

        return view('layout::ajax.modal_change_user_client', $data);
    }

    /**
     * Change the user assigned to a quote.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @legacy-function change_user
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 308
     */
    public function changeUser(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id');
        $user   = $this->userService->find($userId);

        if ( ! $user) {
            return response()->json([
                'success'           => 0,
                'validation_errors' => ['user_id' => 'User not found'],
            ]);
        }

        $quoteId = (int) $request->input('quote_id');
        $this->quoteService->updateQuote($quoteId, ['user_id' => $userId]);

        return response()->json([
            'success'  => 1,
            'quote_id' => $quoteId,
        ]);
    }

    /**
     * Display modal for changing quote client.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_change_client
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 343
     */
    public function modalChangeClient(Request $request)
    {
        $clientId = (int) $request->input('client_id');
        $quoteId  = (int) $request->input('quote_id');

        $data = [
            'client_id' => $clientId,
            'quote_id'  => $quoteId,
            'clients'   => Client::latest()->get(),
        ];

        return view('layout::ajax.modal_change_user_client', $data);
    }

    /**
     * Change the client assigned to a quote.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @legacy-function change_client
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 357
     */
    public function changeClient(Request $request): JsonResponse
    {
        $clientId = (int) $request->input('client_id');
        $client   = $this->clientService->find($clientId);

        if ( ! $client) {
            return response()->json([
                'success'           => 0,
                'validation_errors' => ['client_id' => 'Client not found'],
            ]);
        }

        $quoteId = (int) $request->input('quote_id');
        $this->quoteService->updateQuote($quoteId, ['client_id' => $clientId]);

        return response()->json([
            'success'  => 1,
            'quote_id' => $quoteId,
        ]);
    }

    /**
     * Display modal for creating a new quote.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_create_quote
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 392
     */
    public function modalCreateQuote(Request $request)
    {
        $clientId = (int) $request->input('client_id');
        $client   = $this->clientService->find($clientId);

        $data = [
            'invoice_groups' => $this->invoiceGroupService->getAll(),
            'tax_rates'      => $this->taxRateService->getAll(),
            'client'         => $client,
            'clients'        => Client::latest()->get(),
        ];

        return view('quotes::modal_create_quote', $data);
    }

    /**
     * Create a new quote.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @legacy-function create
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 411
     */
    public function create(Request $request): JsonResponse
    {
        $validation = $this->quoteService->getValidationRules();
        $validator  = validator($request->all(), $validation);

        if ($validator->fails()) {
            return response()->json([
                'success'           => 0,
                'validation_errors' => $validator->errors()->toArray(),
            ]);
        }

        $quote   = $this->quoteService->createQuote($request->all());
        $quoteId = $quote->quote_id;

        return response()->json([
            'success'  => 1,
            'quote_id' => $quoteId,
        ]);
    }

    /**
     * Display modal for converting quote to invoice.
     *
     * @param int $quoteId
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function modal_quote_to_invoice
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 433
     */
    public function modalQuoteToInvoice(int $quoteId)
    {
        $quote = $this->quoteService->findOrFail($quoteId);

        $data = [
            'invoice_groups' => $this->invoiceGroupService->getAll(),
            'quote_id'       => $quoteId,
            'quote'          => $quote,
        ];

        return view('quotes::modal_quote_to_invoice', $data);
    }

    /**
     * Convert a quote to an invoice.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @legacy-function quote_to_invoice
     *
     * @legacy-file application/modules/quotes/controllers/Ajax.php
     *
     * @legacy-line 449
     */
    public function quoteToInvoice(Request $request): JsonResponse
    {
        $validation = $this->invoiceService->getValidationRules();
        $validator  = validator($request->all(), $validation);

        if ($validator->fails()) {
            return response()->json([
                'success'           => 0,
                'validation_errors' => $validator->errors()->toArray(),
            ]);
        }

        // Get the quote
        $quoteId = (int) $request->input('quote_id');
        $quote   = $this->quoteService->findWithRelationsOrFail($quoteId, ['items', 'taxRates']);

        // Create new invoice
        $invoiceData = array_merge($request->all(), [
            'client_id'        => $quote->client_id,
            'user_id'          => $quote->user_id,
            'invoice_group_id' => $request->input('invoice_group_id', $quote->invoice_group_id),
        ]);

        $invoiceId = Invoice::createInvoice($invoiceData, false);

        // Update invoice discounts
        $this->invoiceService->updateInvoice($invoiceId, [
            'invoice_discount_amount'  => $quote->quote_discount_amount,
            'invoice_discount_percent' => $quote->quote_discount_percent,
        ]);

        // Save invoice ID to quote
        $this->quoteService->updateQuote($quoteId, ['invoice_id' => $invoiceId]);

        // Prepare global discount for items
        $globalDiscount = [
            'amount'         => $quote->quote_discount_amount,
            'percent'        => $quote->quote_discount_percent,
            'item'           => 0.0,
            'items_subtotal' => QuoteItem::getItemsSubtotal($quoteId),
        ];

        // Copy quote items to invoice
        foreach ($quote->items as $quoteItem) {
            $itemData = [
                'invoice_id'           => $invoiceId,
                'item_tax_rate_id'     => $quoteItem->item_tax_rate_id,
                'item_product_id'      => $quoteItem->item_product_id,
                'item_name'            => $quoteItem->item_name,
                'item_description'     => $quoteItem->item_description,
                'item_quantity'        => $quoteItem->item_quantity,
                'item_price'           => $quoteItem->item_price,
                'item_product_unit_id' => $quoteItem->item_product_unit_id,
                'item_product_unit'    => $quoteItem->item_product_unit,
                'item_discount_amount' => $quoteItem->item_discount_amount,
                'item_order'           => $quoteItem->item_order,
            ];

            app(InvoiceItemService::class)->saveItem(null, $itemData, $invoiceId, $globalDiscount);
        }

        // Copy quote tax rates to invoice
        foreach ($quote->taxRates as $quoteTaxRate) {
            $taxRateData = [
                'invoice_id'              => $invoiceId,
                'tax_rate_id'             => $quoteTaxRate->tax_rate_id,
                'include_item_tax'        => $quoteTaxRate->include_item_tax,
                'invoice_tax_rate_amount' => $quoteTaxRate->quote_tax_rate_amount,
            ];

            app(InvoiceTaxRateService::class)->saveTaxRate($taxRateData);
        }

        return response()->json([
            'success'    => 1,
            'invoice_id' => $invoiceId,
        ]);
    }
}
