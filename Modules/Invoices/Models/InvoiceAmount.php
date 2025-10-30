<?php

namespace Modules\Invoices\Entities;

use Modules\Core\Models\BaseModel;

/**
 * InvoiceAmount Model
 * 
 * Eloquent model for managing ip_invoice_amounts
 * Stores calculated totals for invoices
 * Migrated from CodeIgniter Mdl_Invoice_Amounts
 */
class InvoiceAmount extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoice_amounts';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_amount_id';

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
        'invoice_id',
        'invoice_item_subtotal',
        'invoice_item_tax_total',
        'invoice_tax_total',
        'invoice_total',
        'invoice_paid',
        'invoice_balance',
        'invoice_item_discount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_amount_id' => 'integer',
        'invoice_id' => 'integer',
        'invoice_item_subtotal' => 'decimal:2',
        'invoice_item_tax_total' => 'decimal:2',
        'invoice_tax_total' => 'decimal:2',
        'invoice_total' => 'decimal:2',
        'invoice_paid' => 'decimal:2',
        'invoice_balance' => 'decimal:2',
        'invoice_item_discount' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the amount.
     */
    public function invoice()
    {
        return $this->belongsTo('Modules\Invoices\Entities\Invoice', 'invoice_id', 'invoice_id');
    }

    /**
     * Calculate invoice amounts including items, taxes, discounts, and payments.
     *
     * @param int $invoiceId
     * @param array $globalDiscount
     * @return void
     */
    public static function calculate(int $invoiceId, array $globalDiscount = []): void
    {
        $decimalPlaces = (int) get_setting('tax_rate_decimal_places');

        // Get the basic totals from invoice item amounts
        $invoiceAmounts = \DB::table('ip_invoice_item_amounts')
            ->selectRaw('
                SUM(item_subtotal) AS invoice_item_subtotal,
                SUM(item_tax_total) AS invoice_item_tax_total,
                SUM(item_subtotal) + SUM(item_tax_total) AS invoice_total,
                SUM(item_discount) AS invoice_item_discount
            ')
            ->whereIn('item_id', function ($query) use ($invoiceId) {
                $query->select('item_id')
                    ->from('ip_invoice_items')
                    ->where('invoice_id', $invoiceId);
            })
            ->first();

        // Calculate subtotal and total based on legacy or new calculation mode
        $legacyCalculation = config_item('legacy_calculation');
        
        if ($legacyCalculation) {
            $invoiceItemSubtotal = $invoiceAmounts->invoice_item_subtotal - $invoiceAmounts->invoice_item_discount;
            $invoiceSubtotal = $invoiceItemSubtotal + $invoiceAmounts->invoice_item_tax_total;
            $invoiceTotal = static::calculateDiscount($invoiceId, $invoiceSubtotal, $decimalPlaces);
        } else {
            $globalDiscountItem = $globalDiscount['item'] ?? 0.0;
            $invoiceItemSubtotal = $invoiceAmounts->invoice_item_subtotal - $invoiceAmounts->invoice_item_discount - $globalDiscountItem;
            $invoiceTotal = $invoiceItemSubtotal + $invoiceAmounts->invoice_item_tax_total;
        }

        // Get the amount already paid
        $invoicePaid = \DB::table('ip_payments')
            ->where('invoice_id', $invoiceId)
            ->sum('payment_amount');
        $invoicePaid = $invoicePaid ? (float) $invoicePaid : 0.0;

        // Save or update invoice amounts
        $dbArray = [
            'invoice_id' => $invoiceId,
            'invoice_item_subtotal' => $invoiceItemSubtotal,
            'invoice_item_tax_total' => $invoiceAmounts->invoice_item_tax_total,
            'invoice_total' => $invoiceTotal,
            'invoice_paid' => $invoicePaid,
            'invoice_balance' => $invoiceTotal - $invoicePaid,
        ];

        static::updateOrCreate(
            ['invoice_id' => $invoiceId],
            $dbArray
        );

        // Calculate invoice taxes
        static::calculateInvoiceTaxes($invoiceId, $decimalPlaces);
    }

    /**
     * Calculate discount for legacy calculation mode.
     *
     * @param int $invoiceId
     * @param float $invoiceTotal
     * @param int $decimalPlaces
     * @return float
     */
    public static function calculateDiscount(int $invoiceId, float $invoiceTotal, int $decimalPlaces = 2): float
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $total = (float) number_format((float) $invoiceTotal, $decimalPlaces, '.', '');
        $discountAmount = (float) number_format((float) $invoice->invoice_discount_amount, $decimalPlaces, '.', '');
        $discountPercent = (float) number_format((float) $invoice->invoice_discount_percent, $decimalPlaces, '.', '');

        $total -= $discountAmount;

        return $total - round(($total / 100 * $discountPercent), $decimalPlaces);
    }

    /**
     * Get global discount for an invoice.
     *
     * @param int $invoiceId
     * @return float
     */
    public static function getGlobalDiscount(int $invoiceId): float
    {
        $result = \DB::table('ip_invoice_item_amounts')
            ->selectRaw('
                SUM(item_subtotal) - (SUM(item_total) - SUM(item_tax_total) + SUM(item_discount)) AS global_discount
            ')
            ->whereIn('item_id', function ($query) use ($invoiceId) {
                $query->select('item_id')
                    ->from('ip_invoice_items')
                    ->where('invoice_id', $invoiceId);
            })
            ->first();

        return (float) ($result->global_discount ?? 0.0);
    }

    /**
     * Calculate invoice taxes.
     *
     * @param int $invoiceId
     * @param int $decimalPlaces
     * @return void
     */
    public static function calculateInvoiceTaxes(int $invoiceId, int $decimalPlaces = 2): void
    {
        $legacyCalculation = config_item('legacy_calculation');

        // Only applicable in legacy calculation mode
        $invoiceTaxRates = $legacyCalculation 
            ? InvoiceTaxRate::where('invoice_id', $invoiceId)->get()
            : collect([]);

        if ($invoiceTaxRates->isNotEmpty()) {
            // Get current invoice amount record
            $invoiceAmount = static::where('invoice_id', $invoiceId)->first();

            // Loop through invoice taxes and update amounts
            foreach ($invoiceTaxRates as $invoiceTaxRate) {
                if ($invoiceTaxRate->include_item_tax) {
                    // Include applied item tax
                    $invoiceTaxRateAmount = ($invoiceAmount->invoice_item_subtotal + $invoiceAmount->invoice_item_tax_total) 
                        * ($invoiceTaxRate->invoice_tax_rate_percent / 100);
                } else {
                    // Don't include applied item tax
                    $invoiceTaxRateAmount = $invoiceAmount->invoice_item_subtotal 
                        * ($invoiceTaxRate->invoice_tax_rate_percent / 100);
                }

                // Update the invoice tax rate record
                InvoiceTaxRate::where('invoice_tax_rate_id', $invoiceTaxRate->invoice_tax_rate_id)
                    ->update(['invoice_tax_rate_amount' => $invoiceTaxRateAmount]);
            }

            // Update invoice amount with total tax
            \DB::table('ip_invoice_amounts')
                ->where('invoice_id', $invoiceId)
                ->update([
                    'invoice_tax_total' => \DB::raw('(
                        SELECT SUM(invoice_tax_rate_amount)
                        FROM ip_invoice_tax_rates
                        WHERE invoice_id = ' . $invoiceId . '
                    )')
                ]);

            // Get updated invoice amount
            $invoiceAmount = static::where('invoice_id', $invoiceId)->first();

            // Recalculate invoice total
            $invoiceTotal = $invoiceAmount->invoice_item_subtotal 
                + $invoiceAmount->invoice_item_tax_total 
                + $invoiceAmount->invoice_tax_total;

            // Apply discount for legacy calculation
            if ($legacyCalculation) {
                $invoiceTotal = static::calculateDiscount($invoiceId, $invoiceTotal, $decimalPlaces);
            }

            // Update invoice amount and balance
            $invoicePaid = $invoiceAmount->invoice_paid ?? 0;
            static::where('invoice_id', $invoiceId)
                ->update([
                    'invoice_total' => $invoiceTotal,
                    'invoice_balance' => $invoiceTotal - $invoicePaid,
                ]);
        } else {
            // No invoice taxes applied
            static::where('invoice_id', $invoiceId)
                ->update(['invoice_tax_total' => '0.00']);
        }
    }

    /**
     * Get total invoiced amount for a period.
     *
     * @param string|null $period
     * @return float
     */
    public static function getTotalInvoiced(?string $period = null): float
    {
        $query = \DB::table('ip_invoice_amounts');

        switch ($period) {
            case 'month':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('MONTH(invoice_date_created) = MONTH(NOW())')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW())');
                });
                break;
            case 'last_month':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('MONTH(invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 MONTH)');
                });
                break;
            case 'year':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW())');
                });
                break;
            case 'last_year':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                });
                break;
        }

        $result = $query->sum('invoice_total');
        return (float) ($result ?? 0.0);
    }

    /**
     * Get total paid amount for a period.
     *
     * @param string|null $period
     * @return float
     */
    public static function getTotalPaid(?string $period = null): float
    {
        $query = \DB::table('ip_invoice_amounts');

        switch ($period) {
            case 'month':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('MONTH(invoice_date_created) = MONTH(NOW())')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW())');
                });
                break;
            case 'last_month':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('MONTH(invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 MONTH)');
                });
                break;
            case 'year':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW())');
                });
                break;
            case 'last_year':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                });
                break;
        }

        $result = $query->sum('invoice_paid');
        return (float) ($result ?? 0.0);
    }

    /**
     * Get total balance for a period.
     *
     * @param string|null $period
     * @return float
     */
    public static function getTotalBalance(?string $period = null): float
    {
        $query = \DB::table('ip_invoice_amounts');

        switch ($period) {
            case 'month':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('MONTH(invoice_date_created) = MONTH(NOW())')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW())');
                });
                break;
            case 'last_month':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('MONTH(invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 MONTH)');
                });
                break;
            case 'year':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW())');
                });
                break;
            case 'last_year':
                $query->whereIn('invoice_id', function ($q) {
                    $q->select('invoice_id')->from('ip_invoices')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                });
                break;
        }

        $result = $query->sum('invoice_balance');
        return (float) ($result ?? 0.0);
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
                $results = \DB::table('ip_invoice_amounts')
                    ->selectRaw('
                        invoice_status_id,
                        SUM(invoice_total) AS sum_total,
                        SUM(invoice_paid) AS sum_paid,
                        SUM(invoice_balance) AS sum_balance,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_invoices', function ($join) {
                        $join->on('ip_invoices.invoice_id', '=', 'ip_invoice_amounts.invoice_id')
                            ->whereRaw('MONTH(ip_invoices.invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                            ->whereRaw('YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_invoices.invoice_status_id')
                    ->get()
                    ->toArray();
                break;

            case 'this-quarter':
                $results = \DB::table('ip_invoice_amounts')
                    ->selectRaw('
                        invoice_status_id,
                        SUM(invoice_total) AS sum_total,
                        SUM(invoice_paid) AS sum_paid,
                        SUM(invoice_balance) AS sum_balance,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_invoices', function ($join) {
                        $join->on('ip_invoices.invoice_id', '=', 'ip_invoice_amounts.invoice_id')
                            ->whereRaw('QUARTER(ip_invoices.invoice_date_created) = QUARTER(NOW())')
                            ->whereRaw('YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_invoices.invoice_status_id')
                    ->get()
                    ->toArray();
                break;

            case 'last-quarter':
                $results = \DB::table('ip_invoice_amounts')
                    ->selectRaw('
                        invoice_status_id,
                        SUM(invoice_total) AS sum_total,
                        SUM(invoice_paid) AS sum_paid,
                        SUM(invoice_balance) AS sum_balance,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_invoices', function ($join) {
                        $join->on('ip_invoices.invoice_id', '=', 'ip_invoice_amounts.invoice_id')
                            ->whereRaw('QUARTER(ip_invoices.invoice_date_created) = QUARTER(NOW() - INTERVAL 1 QUARTER)')
                            ->whereRaw('YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_invoices.invoice_status_id')
                    ->get()
                    ->toArray();
                break;

            case 'this-year':
                $results = \DB::table('ip_invoice_amounts')
                    ->selectRaw('
                        invoice_status_id,
                        SUM(invoice_total) AS sum_total,
                        SUM(invoice_paid) AS sum_paid,
                        SUM(invoice_balance) AS sum_balance,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_invoices', function ($join) {
                        $join->on('ip_invoices.invoice_id', '=', 'ip_invoice_amounts.invoice_id')
                            ->whereRaw('YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_invoices.invoice_status_id')
                    ->get()
                    ->toArray();
                break;

            case 'last-year':
                $results = \DB::table('ip_invoice_amounts')
                    ->selectRaw('
                        invoice_status_id,
                        SUM(invoice_total) AS sum_total,
                        SUM(invoice_paid) AS sum_paid,
                        SUM(invoice_balance) AS sum_balance,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_invoices', function ($join) {
                        $join->on('ip_invoices.invoice_id', '=', 'ip_invoice_amounts.invoice_id')
                            ->whereRaw('YEAR(ip_invoices.invoice_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                    })
                    ->groupBy('ip_invoices.invoice_status_id')
                    ->get()
                    ->toArray();
                break;

            default: // 'this-month'
                $results = \DB::table('ip_invoice_amounts')
                    ->selectRaw('
                        invoice_status_id,
                        SUM(invoice_total) AS sum_total,
                        SUM(invoice_paid) AS sum_paid,
                        SUM(invoice_balance) AS sum_balance,
                        COUNT(*) AS num_total
                    ')
                    ->join('ip_invoices', function ($join) {
                        $join->on('ip_invoices.invoice_id', '=', 'ip_invoice_amounts.invoice_id')
                            ->whereRaw('MONTH(ip_invoices.invoice_date_created) = MONTH(NOW())')
                            ->whereRaw('YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())');
                    })
                    ->groupBy('ip_invoices.invoice_status_id')
                    ->get()
                    ->toArray();
                break;
        }

        $return = [];
        $statuses = Invoice::statuses();

        foreach ($statuses as $key => $status) {
            $return[$key] = [
                'invoice_status_id' => $key,
                'class' => $status['class'],
                'label' => $status['label'],
                'href' => $status['href'],
                'sum_total' => 0,
                'sum_paid' => 0,
                'sum_balance' => 0,
                'num_total' => 0,
            ];
        }

        foreach ($results as $result) {
            $resultArray = (array) $result;
            $statusId = $resultArray['invoice_status_id'];
            if (isset($return[$statusId])) {
                $return[$statusId] = array_merge($return[$statusId], $resultArray);
            }
        }

        return $return;
    }
}
