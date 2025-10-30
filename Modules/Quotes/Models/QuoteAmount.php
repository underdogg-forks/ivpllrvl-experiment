<?php

namespace Modules\Quotes\Models;

use Modules\Core\Models\BaseModel;

/**
 * QuoteAmount Model
 *
 * Eloquent model for managing quote amounts
 * Migrated from CodeIgniter model
 */
class QuoteAmount extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_quote_amounts';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'quote_amount_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quote_id',
        'quote_item_subtotal',
        'quote_item_tax_total',
        'quote_tax_total',
        'quote_total',
        'quote_item_discount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quote_amount_id' => 'integer',
        'quote_id' => 'integer',
        'quote_item_subtotal' => 'decimal:2',
        'quote_item_tax_total' => 'decimal:2',
        'quote_tax_total' => 'decimal:2',
        'quote_total' => 'decimal:2',
        'quote_item_discount' => 'decimal:2',
    ];

    /**
     * Get the quote that owns the amount.
     */
    public function quote()
    {
        return $this->belongsTo('Modules\Quotes\Models\Quote', 'quote_id', 'quote_id');
    }

    /**
     * Calculate quote amounts including items, taxes, and discounts.
     *
     * @param int $quoteId
     * @param array $globalDiscount
     * @return void
     */
    public static function calculate(int $quoteId, array $globalDiscount = []): void
    {
        $decimalPlaces = (int) get_setting('tax_rate_decimal_places');

        // Get the basic totals from quote item amounts
        $quoteAmounts = \DB::table('ip_quote_item_amounts')
            ->selectRaw('
                SUM(item_subtotal) AS quote_item_subtotal,
                SUM(item_tax_total) AS quote_item_tax_total,
                SUM(item_subtotal) + SUM(item_tax_total) AS quote_total,
                SUM(item_discount) AS quote_item_discount
            ')
            ->whereIn('item_id', function ($query) use ($quoteId) {
                $query->select('item_id')
                    ->from('ip_quote_items')
                    ->where('quote_id', $quoteId);
            })
            ->first();

        // Calculate subtotal and total based on legacy or new calculation mode
        $legacyCalculation = config_item('legacy_calculation');

        if ($legacyCalculation) {
            $quoteItemSubtotal = $quoteAmounts->quote_item_subtotal - $quoteAmounts->quote_item_discount;
            $quoteSubtotal = $quoteItemSubtotal + $quoteAmounts->quote_item_tax_total;
            $quoteTotal = static::calculateDiscount($quoteId, $quoteSubtotal, $decimalPlaces);
        } else {
            $globalDiscountItem = $globalDiscount['item'] ?? 0.0;
            $quoteItemSubtotal = $quoteAmounts->quote_item_subtotal - $quoteAmounts->quote_item_discount - $globalDiscountItem;
            $quoteTotal = $quoteItemSubtotal + $quoteAmounts->quote_item_tax_total;
        }

        // Save or update quote amounts
        $dbArray = [
            'quote_id' => $quoteId,
            'quote_item_subtotal' => $quoteItemSubtotal,
            'quote_item_tax_total' => $quoteAmounts->quote_item_tax_total,
            'quote_total' => $quoteTotal,
        ];

        static::updateOrCreate(
            ['quote_id' => $quoteId],
            $dbArray
        );

        // Calculate quote taxes
        static::calculateQuoteTaxes($quoteId, $decimalPlaces);
    }

    /**
     * Calculate discount for legacy calculation mode.
     *
     * @param int $quoteId
     * @param float $quoteTotal
     * @param int $decimalPlaces
     * @return float
     */
    public static function calculateDiscount(int $quoteId, float $quoteTotal, int $decimalPlaces = 2): float
    {
        $quote = Quote::findOrFail($quoteId);

        $total = (float) number_format((float) $quoteTotal, $decimalPlaces, '.', '');
        $discountAmount = (float) number_format((float) $quote->quote_discount_amount, $decimalPlaces, '.', '');
        $discountPercent = (float) number_format((float) $quote->quote_discount_percent, $decimalPlaces, '.', '');

        $total -= $discountAmount;

        return $total - round(($total / 100 * $discountPercent), $decimalPlaces);
    }

    /**
     * Get global discount for a quote.
     *
     * @param int $quoteId
     * @return float
     */
    public static function getGlobalDiscount(int $quoteId): float
    {
        $result = \DB::table('ip_quote_item_amounts')
            ->selectRaw('
                SUM(item_subtotal) - (SUM(item_total) - SUM(item_tax_total) + SUM(item_discount)) AS global_discount
            ')
            ->whereIn('item_id', function ($query) use ($quoteId) {
                $query->select('item_id')
                    ->from('ip_quote_items')
                    ->where('quote_id', $quoteId);
            })
            ->first();

        return (float) ($result->global_discount ?? 0.0);
    }

    /**
     * Calculate quote taxes.
     *
     * @param int $quoteId
     * @param int $decimalPlaces
     * @return void
     */
    public static function calculateQuoteTaxes(int $quoteId, int $decimalPlaces = 2): void
    {
        $legacyCalculation = config_item('legacy_calculation');

        // Only applicable in legacy calculation mode
        $quoteTaxRates = $legacyCalculation
            ? QuoteTaxRate::where('quote_id', $quoteId)->get()
            : collect([]);

        if ($quoteTaxRates->isNotEmpty()) {
            // Get current quote amount record
            $quoteAmount = static::where('quote_id', $quoteId)->first();

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
                QuoteTaxRate::where('quote_tax_rate_id', $quoteTaxRate->quote_tax_rate_id)
                    ->update(['quote_tax_rate_amount' => $quoteTaxRateAmount]);
            }

            // Update quote amount with total tax
            \DB::table('ip_quote_amounts')
                ->where('quote_id', $quoteId)
                ->update([
                    'quote_tax_total' => \DB::raw('(
                        SELECT SUM(quote_tax_rate_amount)
                        FROM ip_quote_tax_rates
                        WHERE quote_id = ' . $quoteId . '
                    )')
                ]);

            // Get updated quote amount
            $quoteAmount = static::where('quote_id', $quoteId)->first();

            // Recalculate quote total
            $quoteTotal = $quoteAmount->quote_item_subtotal
                + $quoteAmount->quote_item_tax_total
                + $quoteAmount->quote_tax_total;

            // Apply discount for legacy calculation
            if ($legacyCalculation) {
                $quoteTotal = static::calculateDiscount($quoteId, $quoteTotal, $decimalPlaces);
            }

            // Update quote amount
            static::where('quote_id', $quoteId)
                ->update(['quote_total' => $quoteTotal]);
        } else {
            // No quote taxes applied
            static::where('quote_id', $quoteId)
                ->update(['quote_tax_total' => '0.00']);
        }
    }

    /**
     * Get total quoted amount for a period.
     *
     * @param string|null $period
     * @return float
     */
    public static function getTotalQuoted(?string $period = null): float
    {
        switch ($period) {
            case 'month':
                $result = \DB::table('ip_quote_amounts')
                    ->selectRaw('SUM(quote_total) AS total_quoted')
                    ->whereIn('quote_id', function ($query) {
                        $query->select('quote_id')
                            ->from('ip_quotes')
                            ->whereRaw('MONTH(quote_date_created) = MONTH(NOW())')
                            ->whereRaw('YEAR(quote_date_created) = YEAR(NOW())');
                    })
                    ->first();
                break;

            case 'last_month':
                $result = \DB::table('ip_quote_amounts')
                    ->selectRaw('SUM(quote_total) AS total_quoted')
                    ->whereIn('quote_id', function ($query) {
                        $query->select('quote_id')
                            ->from('ip_quotes')
                            ->whereRaw('MONTH(quote_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                            ->whereRaw('YEAR(quote_date_created) = YEAR(NOW() - INTERVAL 1 MONTH)');
                    })
                    ->first();
                break;

            case 'year':
                $result = \DB::table('ip_quote_amounts')
                    ->selectRaw('SUM(quote_total) AS total_quoted')
                    ->whereIn('quote_id', function ($query) {
                        $query->select('quote_id')
                            ->from('ip_quotes')
                            ->whereRaw('YEAR(quote_date_created) = YEAR(NOW())');
                    })
                    ->first();
                break;

            case 'last_year':
                $result = \DB::table('ip_quote_amounts')
                    ->selectRaw('SUM(quote_total) AS total_quoted')
                    ->whereIn('quote_id', function ($query) {
                        $query->select('quote_id')
                            ->from('ip_quotes')
                            ->whereRaw('YEAR(quote_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                    })
                    ->first();
                break;

            default:
                $result = \DB::table('ip_quote_amounts')
                    ->selectRaw('SUM(quote_total) AS total_quoted')
                    ->first();
                break;
        }

        return (float) ($result->total_quoted ?? 0.0);
    }

    /**
     * Get status totals for a period.
     *
     * @param string $period
     * @return array
     */
    public static function getStatusTotals(string $period = 'this-month'): array
    {
        switch ($period) {
            case 'last-month':
                $results = \DB::table('ip_quote_amounts')
                    ->selectRaw('
                        quote_status_id,
                        SUM(quote_total) AS sum_total,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_quotes', function ($join) {
                        $join->on('ip_quotes.quote_id', '=', 'ip_quote_amounts.quote_id')
                            ->whereRaw('MONTH(ip_quotes.quote_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                            ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_quotes.quote_status_id')
                    ->get()
                    ->toArray();
                break;

            case 'this-quarter':
                $results = \DB::table('ip_quote_amounts')
                    ->selectRaw('
                        quote_status_id,
                        SUM(quote_total) AS sum_total,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_quotes', function ($join) {
                        $join->on('ip_quotes.quote_id', '=', 'ip_quote_amounts.quote_id')
                            ->whereRaw('QUARTER(ip_quotes.quote_date_created) = QUARTER(NOW())')
                            ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_quotes.quote_status_id')
                    ->get()
                    ->toArray();
                break;

            case 'last-quarter':
                $results = \DB::table('ip_quote_amounts')
                    ->selectRaw('
                        quote_status_id,
                        SUM(quote_total) AS sum_total,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_quotes', function ($join) {
                        $join->on('ip_quotes.quote_id', '=', 'ip_quote_amounts.quote_id')
                            ->whereRaw('QUARTER(ip_quotes.quote_date_created) = QUARTER(NOW() - INTERVAL 1 QUARTER)')
                            ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_quotes.quote_status_id')
                    ->get()
                    ->toArray();
                break;

            case 'this-year':
                $results = \DB::table('ip_quote_amounts')
                    ->selectRaw('
                        quote_status_id,
                        SUM(quote_total) AS sum_total,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_quotes', function ($join) {
                        $join->on('ip_quotes.quote_id', '=', 'ip_quote_amounts.quote_id')
                            ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_quotes.quote_status_id')
                    ->get()
                    ->toArray();
                break;

            case 'last-year':
                $results = \DB::table('ip_quote_amounts')
                    ->selectRaw('
                        quote_status_id,
                        SUM(quote_total) AS sum_total,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_quotes', function ($join) {
                        $join->on('ip_quotes.quote_id', '=', 'ip_quote_amounts.quote_id')
                            ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                    })
                    ->groupBy('ip_quotes.quote_status_id')
                    ->get()
                    ->toArray();
                break;

            default: // 'this-month'
                $results = \DB::table('ip_quote_amounts')
                    ->selectRaw('
                        quote_status_id,
                        SUM(quote_total) AS sum_total,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_quotes', function ($join) {
                        $join->on('ip_quotes.quote_id', '=', 'ip_quote_amounts.quote_id')
                            ->whereRaw('MONTH(ip_quotes.quote_date_created) = MONTH(NOW())')
                            ->whereRaw('YEAR(ip_quotes.quote_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_quotes.quote_status_id')
                    ->get()
                    ->toArray();
                break;
        }

        $return = [];
        $statuses = Quote::statuses();

        foreach ($statuses as $key => $status) {
            $return[$key] = [
                'quote_status_id' => $key,
                'class' => $status['class'],
                'label' => $status['label'],
                'href' => $status['href'],
                'sum_total' => 0,
                'num_total' => 0,
            ];
        }

        foreach ($results as $result) {
            $resultArray = (array) $result;
            $statusId = $resultArray['quote_status_id'];
            $return[$statusId] = array_merge($return[$statusId], $resultArray);
        }

        return $return;
    }
}
