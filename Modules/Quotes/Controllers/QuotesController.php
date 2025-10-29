<?php

namespace Modules\Quotes\Controllers;

use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteAmount;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteTaxRate;
use Modules\Products\Models\TaxRate;
use Modules\Products\Models\Unit;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomValue;

/**
 * QuotesController
 * 
 * Handles quote management including creation, editing, viewing, PDF generation,
 * status filtering, and tax management.
 * 
 * Migrated from CodeIgniter HMVC to Laravel/Illuminate with PSR-4 compliance
 * 
 * @package Modules\Quotes\Http\Controllers
 */
class QuotesController
{
    /**
     * Redirect to all quotes view
     * 
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function index
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     * @legacy-line 29
     */
    public function index()
    {
        return redirect()->route('quotes.status', ['status' => 'all']);
    }

    /**
     * Display quotes filtered by status with pagination
     * 
     * @param string $status Quote status filter (all, draft, sent, viewed, approved, rejected, canceled)
     * @param int $page Page number for pagination
     * @return \Illuminate\View\View
     * 
     * @legacy-function status
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     * @legacy-line 38
     */
    public function status(string $status = 'all', int $page = 0)
    {
        $query = Quote::with(['client', 'user']);

        // Apply status filter
        switch ($status) {
            case 'draft':
                $query->draft();
                break;
            case 'sent':
                $query->sent();
                break;
            case 'viewed':
                $query->viewed();
                break;
            case 'approved':
                $query->approved();
                break;
            case 'rejected':
                $query->rejected();
                break;
            case 'canceled':
                $query->canceled();
                break;
            case 'all':
            default:
                // No filter for 'all'
                break;
        }

        // Paginate results
        $quotes = $query->paginate(15);

        return view('quotes::index', [
            'quotes' => $quotes,
            'status' => $status,
            'filter_display' => true,
            'filter_placeholder' => trans('filter_quotes'),
            'filter_method' => 'filter_quotes',
            'quote_statuses' => Quote::statuses(),
        ]);
    }

    /**
     * Display detailed view of a specific quote with items, tax rates, and custom fields
     * 
     * @param int $quote_id The quote ID
     * @return \Illuminate\View\View
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * 
     * @legacy-function view
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     * @legacy-line 83
     */
    public function view(int $quote_id)
    {
        $quote = Quote::with([
            'client',
            'user',
            'invoiceGroup',
            'items.product',
            'items.unit',
            'taxRates.taxRate',
            'amounts'
        ])->find($quote_id);

        if (!$quote) {
            abort(404, 'Quote not found');
        }

        // Get custom fields for quotes
        $customFields = CustomField::query()->where('custom_field_table', 'ip_quote_custom')
            ->orderBy('custom_field_order')
            ->get();

        // Get custom field values for select/dropdown fields
        $customValues = [];
        foreach ($customFields as $field) {
            if (in_array($field->custom_field_type, ['select', 'dropdown'])) {
                $customValues[$field->custom_field_id] = CustomValue::query()->where('custom_field_id', $field->custom_field_id)->get();
            }
        }

        // Get all items for this quote
        $items = QuoteItem::query()->where('quote_id', $quote_id)
            ->with(['product', 'unit'])
            ->orderBy('item_order')
            ->get();

        // Get tax rates
        $taxRates = TaxRate::query()->all();
        $quoteTaxRates = QuoteTaxRate::query()->where('quote_id', $quote_id)
            ->with('taxRate')
            ->get();

        // Get units
        $units = Unit::query()->all();

        // Check if there are multiple admin users (for user change functionality)
        $changeUser = \DB::table('ip_users')
            ->where('user_type', 1)
            ->where('user_active', 1)
            ->count() > 1;

        return view('quotes::view', [
            'quote' => $quote,
            'items' => $items,
            'quote_id' => $quote_id,
            'change_user' => $changeUser,
            'units' => $units,
            'tax_rates' => $taxRates,
            'quote_tax_rates' => $quoteTaxRates,
            'quote_statuses' => Quote::statuses(),
            'custom_fields' => $customFields,
            'custom_values' => $customValues,
            'custom_js_vars' => [
                'currency_symbol' => config('invoiceplane.currency_symbol', '$'),
                'currency_symbol_placement' => config('invoiceplane.currency_symbol_placement', 'before'),
                'decimal_point' => config('invoiceplane.decimal_point', '.'),
            ],
            'legacy_calculation' => config('invoiceplane.legacy_calculation', false),
        ]);
    }

    /**
     * Delete a quote and all related records
     * 
     * @param int $quote_id The quote ID to delete
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function delete
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     * @legacy-line 188
     */
    public function delete(int $quote_id)
    {
        Quote::deleteQuote($quote_id);

        return redirect()->route('quotes.index')
            ->with('success', 'Quote deleted successfully');
    }

    /**
     * Generate PDF for a quote
     * 
     * @param int $quote_id The quote ID
     * @param bool $stream Whether to stream the PDF or download it
     * @param string|null $quote_template The template to use for PDF generation
     * @return mixed PDF response
     * 
     * @legacy-function generate_pdf
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     * @legacy-line 201
     */
    public function generatePdf(int $quote_id, bool $stream = true, ?string $quote_template = null)
    {
        // Mark quote as sent if configured
        if (config('invoiceplane.mark_quotes_sent_pdf', false)) {
            Quote::generateQuoteNumberIfApplicable($quote_id);
            Quote::markSent($quote_id);
        }

        // Generate PDF using helper function
        // TODO: Implement PDF generation helper
        return generate_quote_pdf($quote_id, $stream, $quote_template);
    }

    /**
     * Delete a tax rate from a quote and recalculate amounts
     * 
     * @param int $quote_id The quote ID
     * @param int $quote_tax_rate_id The tax rate ID to delete
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function delete_quote_tax
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     * @legacy-line 217
     */
    public function deleteQuoteTax(int $quote_id, int $quote_tax_rate_id)
    {
        // Delete the tax rate
        QuoteTaxRate::query()->where('quote_tax_rate_id', $quote_tax_rate_id)->delete();

        // Get global discount for recalculation
        $globalDiscount = ['item' => QuoteAmount::getGlobalDiscount($quote_id)];

        // Recalculate quote amounts
        QuoteAmount::calculate($quote_id, $globalDiscount);

        return redirect()->route('quotes.view', ['quote_id' => $quote_id])
            ->with('success', 'Tax rate deleted and quote recalculated');
    }

    /**
     * Recalculate all quotes in the system
     * 
     * Used for batch operations or after system configuration changes
     * 
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function recalculate_all_quotes
     * @legacy-file application/modules/quotes/controllers/Quotes.php
     * @legacy-line 230
     */
    public function recalculateAllQuotes()
    {
        $quoteIds = Quote::pluck('quote_id');

        foreach ($quoteIds as $quoteId) {
            $globalDiscount = ['item' => QuoteAmount::getGlobalDiscount($quoteId)];
            QuoteAmount::calculate($quoteId, $globalDiscount);
        }

        return redirect()->back()
            ->with('success', 'All quotes recalculated successfully');
    }
}

