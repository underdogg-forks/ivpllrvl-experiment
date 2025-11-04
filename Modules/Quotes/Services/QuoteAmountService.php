<?php

namespace Modules\Quotes\Services;

use Illuminate\Support\Facades\DB as FacadeDB;
use Modules\Core\Support\SettingsHelper;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteAmount;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteItemAmount;
use Modules\Quotes\Models\QuoteTaxRate;

/**
 * QuoteAmountService.
 *
 * Service class for managing quote amount calculations and reporting
 * Extracted from QuoteAmount model
 */
class QuoteAmountService
{
    /**
     * QuoteService instance.
     *
     * @var QuoteService
     */
    /**
     * Constructor.
     *
     * @param QuoteService $quoteService
     */
    public function __construct(
        protected QuoteService $quoteService
    ) {
    }

    /**
     * Calculate quote amounts including items, taxes, and discounts.
     *
     * @param int   $quoteId
     * @param array $globalDiscount
     *
     * @return void
     */
    public function calculate(int $quoteId, array $globalDiscount = []): void
    {
        $decimalPlaces = (int) SettingsHelper::getSetting('tax_rate_decimal_places');

        // Get all item IDs for this quote
        $itemIds = QuoteItem::query()->where('quote_id', $quoteId)->pluck('item_id');

        // Get the basic totals from quote item amounts using Eloquent
        $quoteAmounts = QuoteItemAmount::query()->whereIn('item_id', $itemIds)
            ->selectRaw('
                SUM(item_subtotal) AS quote_item_subtotal,
                SUM(item_tax_total) AS quote_item_tax_total,
                SUM(item_subtotal) + SUM(item_tax_total) AS quote_total,
                SUM(item_discount) AS quote_item_discount
            ')
            ->first();

        // Handle case when no items exist
        if (! $quoteAmounts || $quoteAmounts->quote_item_subtotal === null) {
            $quoteAmounts = (object) [
                'quote_item_subtotal'  => 0.0,
                'quote_item_tax_total' => 0.0,
                'quote_total'          => 0.0,
                'quote_item_discount'  => 0.0,
            ];
        }

        // Calculate subtotal and total based on legacy or new calculation mode
        $legacyCalculation = config_item('legacy_calculation');

        if ($legacyCalculation) {
            $quoteItemSubtotal = $quoteAmounts->quote_item_subtotal - $quoteAmounts->quote_item_discount;
            $quoteSubtotal     = $quoteItemSubtotal + $quoteAmounts->quote_item_tax_total;
            $quoteTotal        = $this->calculateDiscount($quoteId, $quoteSubtotal, $decimalPlaces);
        } else {
            $globalDiscountItem = $globalDiscount['item'] ?? 0.0;
            $quoteItemSubtotal  = $quoteAmounts->quote_item_subtotal - $quoteAmounts->quote_item_discount - $globalDiscountItem;
            $quoteTotal         = $quoteItemSubtotal + $quoteAmounts->quote_item_tax_total;
        }

        // Save or update quote amounts
        $dbArray = [
            'quote_id'             => $quoteId,
            'quote_item_subtotal'  => $quoteItemSubtotal,
            'quote_item_tax_total' => $quoteAmounts->quote_item_tax_total,
            'quote_total'          => $quoteTotal,
        ];

        QuoteAmount::updateOrCreate(
            ['quote_id' => $quoteId],
            $dbArray
        );

        // Calculate quote taxes
        $this->calculateQuoteTaxes($quoteId, $decimalPlaces);
    }

    /**
     * Calculate discount for legacy calculation mode.
     *
     * @param int   $quoteId
     * @param float $quoteTotal
     * @param int   $decimalPlaces
     *
     * @return float
     */
    public function calculateDiscount(int $quoteId, float $quoteTotal, int $decimalPlaces = 2): float
    {
        $quote = Quote::findOrFail($quoteId);

        $total           = (float) number_format((float) $quoteTotal, $decimalPlaces, '.', '');
        $discountAmount  = (float) number_format((float) $quote->quote_discount_amount, $decimalPlaces, '.', '');
        $discountPercent = (float) number_format((float) $quote->quote_discount_percent, $decimalPlaces, '.', '');

        $total -= $discountAmount;

        return $total - round(($total / 100 * $discountPercent), $decimalPlaces);
    }

    /**
     * Get global discount for a quote.
     *
     * @param int $quoteId
     *
     * @return float
     */
    public function getGlobalDiscount(int $quoteId): float
    {
        // Get all item IDs for this quote
        $itemIds = QuoteItem::query()->where('quote_id', $quoteId)->pluck('item_id');

        // Calculate global discount using Eloquent
        $result = QuoteItemAmount::query()->whereIn('item_id', $itemIds)
            ->selectRaw('
                SUM(item_subtotal) - (SUM(item_total) - SUM(item_tax_total) + SUM(item_discount)) AS global_discount
            ')
            ->first();

        return (float) ($result->global_discount ?? 0.0);
    }

    /**
     * Calculate quote taxes.
     *
     * @param int $quoteId
     * @param int $decimalPlaces
     *
     * @return void
     */
    public function calculateQuoteTaxes(int $quoteId, int $decimalPlaces = 2): void
    {
        $legacyCalculation = config_item('legacy_calculation');

        // Only applicable in legacy calculation mode
        $quoteTaxRates = $legacyCalculation
            ? QuoteTaxRate::query()->where('quote_id', $quoteId)->get()
            : collect([]);

        if ($quoteTaxRates->isNotEmpty()) {
            // Get current quote amount record
            $quoteAmount = QuoteAmount::query()->where('quote_id', $quoteId)->first();

            // Loop through quote taxes and update amounts
            foreach ($quoteTaxRates as $quoteTaxRate) {
                if ($quoteTaxRate->include_item_tax) {
                    // Include applied item tax
                    $quoteTaxRateAmount = ($quoteAmount->quote_item_subtotal + $quoteAmount->quote_item_tax_total)
                        * ($quoteTaxRate->quote_tax_rate_percent / 100);
                } else {
                    // Don't include applied item tax
                    $quoteTaxRateAmount = $quoteAmount->quote_item_subtotal
                        * ($quoteTaxRate->quote_tax_rate_percent / 100);
                }

                // Update the quote tax rate record
                QuoteTaxRate::query()->where('quote_tax_rate_id', $quoteTaxRate->quote_tax_rate_id)
                    ->update(['quote_tax_rate_amount' => $quoteTaxRateAmount]);
            }

            // Update quote amount with total tax
            $quoteTaxTotal = QuoteTaxRate::query()->where('quote_id', $quoteId)
                ->sum('quote_tax_rate_amount');

            QuoteAmount::query()->where('quote_id', $quoteId)
                ->update(['quote_tax_total' => $quoteTaxTotal]);

            // Get updated quote amount
            $quoteAmount = QuoteAmount::query()->where('quote_id', $quoteId)->first();

            // Recalculate quote total
            $quoteTotal = $quoteAmount->quote_item_subtotal
                + $quoteAmount->quote_item_tax_total
                + $quoteAmount->quote_tax_total;

            // Apply discount for legacy calculation
            if ($legacyCalculation) {
                $quoteTotal = $this->calculateDiscount($quoteId, $quoteTotal, $decimalPlaces);
            }

            // Update quote amount
            QuoteAmount::query()->where('quote_id', $quoteId)
                ->update(['quote_total' => $quoteTotal]);
        } else {
            // No quote taxes applied
            QuoteAmount::query()->where('quote_id', $quoteId)
                ->update(['quote_tax_total' => '0.00']);
        }
    }

    /**
     * Get total quoted amount for a period.
     *
     * @param string|null $period
     *
     * @return float
     */
    public function getTotalQuoted(?string $period = null): float
    {
        $query = QuoteAmount::query();

        switch ($period) {
            case 'month':
                $query->whereHas('quote', function ($q) {
                    $q->whereRaw('MONTH(quote_date_created) = MONTH(NOW())')
                      ->whereRaw('YEAR(quote_date_created) = YEAR(NOW())');
                });
                break;

            case 'last_month':
                $query->whereHas('quote', function ($q) {
                    $q->whereRaw('MONTH(quote_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                      ->whereRaw('YEAR(quote_date_created) = YEAR(NOW() - INTERVAL 1 MONTH)');
                });
                break;

            case 'year':
                $query->whereHas('quote', function ($q) {
                    $q->whereRaw('YEAR(quote_date_created) = YEAR(NOW())');
                });
                break;

            case 'last_year':
                $query->whereHas('quote', function ($q) {
                    $q->whereRaw('YEAR(quote_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                });
                break;

            default:
                // No filter - all quotes
                break;
        }

        return (float) $query->sum('quote_total');
    }

    /**
     * Get status totals for a period.
     *
     * @param string $period
     *
     * @return array
     */
    public function getStatusTotals(string $period = 'this-month'): array
    {
        $query = QuoteAmount::query()
            ->join('ip_quotes', 'ip_quotes.quote_id', '=', 'ip_quote_amounts.quote_id')
            ->selectRaw('
                ip_quotes.quote_status_id,
                SUM(ip_quote_amounts.quote_total) AS sum_total,
                COUNT(*) AS num_total
            ')
            ->groupBy('ip_quotes.quote_status_id');

        switch ($period) {
            case 'last-month':
                $query->whereRaw('MONTH(ip_quotes.quote_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                      ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                break;

            case 'this-quarter':
                $query->whereRaw('QUARTER(ip_quotes.quote_date_created) = QUARTER(NOW())')
                      ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                break;

            case 'last-quarter':
                $query->whereRaw('QUARTER(ip_quotes.quote_date_created) = QUARTER(NOW() - INTERVAL 1 QUARTER)')
                      ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                break;

            case 'this-year':
                $query->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                break;

            case 'last-year':
                $query->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                break;

            default: // 'this-month'
                $query->whereRaw('MONTH(ip_quotes.quote_date_created) = MONTH(NOW())')
                      ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                break;
        }

        $results = $query->get()->toArray();

        $return   = [];
        $statuses = $this->quoteService->getStatuses();

        foreach ($statuses as $key => $status) {
            $return[$key] = [
                'quote_status_id' => $key,
                'class'           => $status['class'],
                'label'           => $status['label'],
                'href'            => $status['href'],
                'sum_total'       => 0,
                'num_total'       => 0,
            ];
        }

        foreach ($results as $result) {
            $resultArray       = (array) $result;
            $statusId          = $resultArray['quote_status_id'];
            $return[$statusId] = array_merge($return[$statusId], $resultArray);
        }

        return $return;
    }
}
