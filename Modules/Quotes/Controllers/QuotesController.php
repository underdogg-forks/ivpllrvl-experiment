<?php

namespace Modules\Quotes\Controllers;

use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomValue;
use Modules\Core\Services\CustomFieldService;
use Modules\Core\Services\CustomValueService;
use Modules\Core\Services\UserService;
use Modules\Core\Support\PdfHelper;
use Modules\Core\Support\TranslationHelper;
use Modules\Products\Models\TaxRate;
use Modules\Products\Models\Unit;
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
 * QuotesController
 *
 * Handles quote management including creation, editing, viewing, PDF generation,
 * status filtering, and tax management
 *
 * @legacy-file application/modules/quotes/controllers/Quotes.php
 */
class QuotesController
{
    public function __construct(
        protected QuoteService $quoteService,
        protected QuoteAmountService $quoteAmountService,
        protected UserService $userService,
        protected CustomFieldService $customFieldService,
        protected CustomValueService $customValueService,
        protected TaxRateService $taxRateService,
        protected UnitService $unitService,
        protected QuoteItemService $quoteItemService,
        protected QuoteTaxRateService $quoteTaxRateService
    ) {
    }

    /**
     * Redirect to all quotes view.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     *
     * @legacy-line 29
     */
    public function index()
    {
        return redirect()->route('quotes.status', ['status' => 'all']);
    }

    /**
     * Display quotes filtered by status with pagination.
     *
     * @param string $status Quote status filter (all, draft, sent, viewed, approved, rejected, canceled)
     * @param int    $page   Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function status
     *
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     *
     * @legacy-line 38
     */
    public function status(string $status = 'all', int $page = 0)
    {
        $quotes = $this->quoteService->getAllWithRelations(['client', 'user'], $status, 15);

        return view('quotes::index', [
            'quotes'             => $quotes,
            'status'             => $status,
            'filter_display'     => true,
            'filter_placeholder' => TranslationHelper::trans('filter_quotes'),
            'filter_method'      => 'filter_quotes',
            'quote_statuses'     => $this->quoteService->getStatuses(),
        ]);
    }

    /**
     * Display detailed view of a specific quote with items, tax rates, and custom fields.
     *
     * @param int $quote_id The quote ID
     *
     * @return \Illuminate\View\View
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @legacy-function view
     *
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     *
     * @legacy-line 83
     */
    public function view(int $quote_id)
    {
        $quote = $this->quoteService->findWithRelations($quote_id, [
            'client',
            'user',
            'invoiceGroup',
            'items.product',
            'items.unit',
            'taxRates.taxRate',
            'amounts',
        ]);

        if ( ! $quote) {
            abort(404, 'Quote not found');
        }

        // Get custom fields for quotes
        $customFields = $this->customFieldService->getByTableOrdered('ip_quote_custom');

        // Get custom field values for select/dropdown fields
        $customValues = [];
        foreach ($customFields as $field) {
            if (in_array($field->custom_field_type, ['select', 'dropdown'])) {
                $customValues[$field->custom_field_id] = $this->customValueService->getByFieldId($field->custom_field_id);
            }
        }

        // Get all items for this quote
        $items = $this->quoteItemService->getByQuoteId($quote_id);

        // Get tax rates
        $taxRates      = $this->taxRateService->getAll();
        $quoteTaxRates = $this->quoteTaxRateService->getByQuoteId($quote_id);

        // Get units
        $units = Unit::all();

        // Check if there are multiple admin users (for user change functionality)
        $changeUser = $this->userService->hasMultipleActiveAdmins();

        return view('quotes::view', [
            'quote'           => $quote,
            'items'           => $items,
            'quote_id'        => $quote_id,
            'change_user'     => $changeUser,
            'units'           => $units,
            'tax_rates'       => $taxRates,
            'quote_tax_rates' => $quoteTaxRates,
            'quote_statuses'  => $this->quoteService->getStatuses(),
            'custom_fields'   => $customFields,
            'custom_values'   => $customValues,
            'custom_js_vars'  => [
                'currency_symbol'           => config('invoiceplane.currency_symbol', '$'),
                'currency_symbol_placement' => config('invoiceplane.currency_symbol_placement', 'before'),
                'decimal_point'             => config('invoiceplane.decimal_point', '.'),
            ],
            'legacy_calculation' => config('invoiceplane.legacy_calculation', false),
        ]);
    }

    /**
     * Delete a quote and all related records.
     *
     * @param int $quote_id The quote ID to delete
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     *
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     *
     * @legacy-line 188
     */
    public function delete(int $quote_id)
    {
        $this->quoteService->deleteQuote($quote_id);

        return redirect()->route('quotes.index')
            ->with('success', 'Quote deleted successfully');
    }

    /**
     * Generate PDF for a quote.
     *
     * @param int         $quote_id       The quote ID
     * @param bool        $stream         Whether to stream the PDF or download it
     * @param string|null $quote_template The template to use for PDF generation
     *
     * @return mixed PDF response
     *
     * @legacy-function generate_pdf
     *
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     *
     * @legacy-line 201
     */
    public function generatePdf(int $quote_id, bool $stream = true, ?string $quote_template = null)
    {
        // Mark quote as sent if configured
        if (config('invoiceplane.mark_quotes_sent_pdf', false)) {
            $this->quoteService->generateQuoteNumberIfApplicable($quote_id);
            $this->quoteService->markSent($quote_id);
        }

        // Generate PDF using helper function
        return PdfHelper::generate_quote_pdf($quote_id, $stream, $quote_template);
    }

    /**
     * Delete a tax rate from a quote and recalculate amounts.
     *
     * @param int $quote_id          The quote ID
     * @param int $quote_tax_rate_id The tax rate ID to delete
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete_quote_tax
     *
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     *
     * @legacy-line 217
     */
    public function deleteQuoteTax(int $quote_id, int $quote_tax_rate_id)
    {
        // Delete the tax rate
        $this->quoteTaxRateService->delete($quote_tax_rate_id);

        // Get global discount for recalculation
        $globalDiscount = ['item' => $this->quoteAmountService->getGlobalDiscount($quote_id)];

        // Recalculate quote amounts
        $this->quoteAmountService->calculate($quote_id, $globalDiscount);

        return redirect()->route('quotes.view', ['quote_id' => $quote_id])
            ->with('success', 'Tax rate deleted and quote recalculated');
    }

    /**
     * Recalculate all quotes in the system.
     *
     * Used for batch operations or after system configuration changes
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function recalculate_all_quotes
     *
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     *
     * @legacy-line 230
     */
    public function recalculateAllQuotes()
    {
        $quoteIds = $this->quoteService->getAllQuoteIds();

        foreach ($quoteIds as $quoteId) {
            $globalDiscount = ['item' => $this->quoteAmountService->getGlobalDiscount($quoteId)];
            $this->quoteAmountService->calculate($quoteId, $globalDiscount);
        }

        return redirect()->back()
            ->with('success', 'All quotes recalculated successfully');
    }
}
