<?php

namespace Modules\Invoices\Services;

use Illuminate\Support\Collection;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Invoices\Models\InvoiceTaxRate;
use Modules\Invoices\Models\Item;
use Modules\Invoices\Models\ItemAmount;
use Modules\Payments\Models\Payment;

class InvoiceAmountService
{
    public function calculate(int $invoiceId, array $globalDiscount = []): void
    {
        $decimalPlaces = (int) get_setting('tax_rate_decimal_places');

        // Get all item IDs for this invoice
        $itemIds = Item::query()->where('invoice_id', $invoiceId)->pluck('item_id');

        // Get the basic totals from invoice item amounts using Eloquent
        $invoiceAmounts = ItemAmount::query()->whereIn('item_id', $itemIds)
            ->selectRaw('
                SUM(item_subtotal) AS invoice_item_subtotal,
                SUM(item_tax_total) AS invoice_item_tax_total,
                SUM(item_subtotal) + SUM(item_tax_total) AS invoice_total,
                SUM(item_discount) AS invoice_item_discount
            ')
            ->first();

        $invoiceAmounts = $invoiceAmounts ?: (object) [
            'invoice_item_subtotal'  => 0.0,
            'invoice_item_tax_total' => 0.0,
            'invoice_total'          => 0.0,
            'invoice_item_discount'  => 0.0,
        ];

        $legacyCalculation = config_item('legacy_calculation');

        if ($legacyCalculation) {
            $invoiceItemSubtotal = $invoiceAmounts->invoice_item_subtotal - $invoiceAmounts->invoice_item_discount;
            $invoiceSubtotal     = $invoiceItemSubtotal + $invoiceAmounts->invoice_item_tax_total;
            $invoiceTotal        = $this->calculateDiscount($invoiceId, $invoiceSubtotal, $decimalPlaces);
        } else {
            $globalDiscountItem  = $globalDiscount['item'] ?? 0.0;
            $invoiceItemSubtotal = $invoiceAmounts->invoice_item_subtotal - $invoiceAmounts->invoice_item_discount - $globalDiscountItem;
            $invoiceTotal        = $invoiceItemSubtotal + $invoiceAmounts->invoice_item_tax_total;
        }

        // Get total paid using Payment model
        $invoicePaid = Payment::query()->where('invoice_id', $invoiceId)
            ->sum('payment_amount');
        $invoicePaid = $invoicePaid ? (float) $invoicePaid : 0.0;

        $dbArray = [
            'invoice_id'             => $invoiceId,
            'invoice_item_subtotal'  => $invoiceItemSubtotal,
            'invoice_item_tax_total' => $invoiceAmounts->invoice_item_tax_total,
            'invoice_total'          => $invoiceTotal,
            'invoice_paid'           => $invoicePaid,
            'invoice_balance'        => $invoiceTotal - $invoicePaid,
        ];

        InvoiceAmount::updateOrCreate(
            ['invoice_id' => $invoiceId],
            $dbArray
        );

        $this->calculateInvoiceTaxes($invoiceId, $decimalPlaces);
    }

    public function calculateDiscount(int $invoiceId, float $invoiceTotal, int $decimalPlaces = 2): float
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $total           = (float) number_format((float) $invoiceTotal, $decimalPlaces, '.', '');
        $discountAmount  = (float) number_format((float) $invoice->invoice_discount_amount, $decimalPlaces, '.', '');
        $discountPercent = (float) number_format((float) $invoice->invoice_discount_percent, $decimalPlaces, '.', '');

        $total -= $discountAmount;

        return $total - round(($total / 100 * $discountPercent), $decimalPlaces);
    }

    public function getGlobalDiscount(int $invoiceId): float
    {
        // Get all item IDs for this invoice
        $itemIds = Item::query()->where('invoice_id', $invoiceId)->pluck('item_id');

        // Calculate global discount using Eloquent
        $result = ItemAmount::query()->whereIn('item_id', $itemIds)
            ->selectRaw('
                SUM(item_subtotal) - (SUM(item_total) - SUM(item_tax_total) + SUM(item_discount)) AS global_discount
            ')
            ->first();

        return (float) ($result->global_discount ?? 0.0);
    }

    public function calculateInvoiceTaxes(int $invoiceId, int $decimalPlaces = 2): void
    {
        $legacyCalculation = config_item('legacy_calculation');

        $invoiceTaxRates = $legacyCalculation
            ? InvoiceTaxRate::query()->where('invoice_id', $invoiceId)->get()
            : collect();

        if ($invoiceTaxRates->isEmpty()) {
            InvoiceAmount::query()->where('invoice_id', $invoiceId)
                ->update(['invoice_tax_total' => '0.00']);

            return;
        }

        $invoiceAmount = InvoiceAmount::query()->where('invoice_id', $invoiceId)->first();

        $invoiceTaxRates->each(function ($invoiceTaxRate) use ($invoiceAmount) {
            if ($invoiceTaxRate->include_item_tax) {
                $invoiceTaxRateAmount = ($invoiceAmount->invoice_item_subtotal + $invoiceAmount->invoice_item_tax_total)
                    * ($invoiceTaxRate->invoice_tax_rate_percent / 100);
            } else {
                $invoiceTaxRateAmount = $invoiceAmount->invoice_item_subtotal
                    * ($invoiceTaxRate->invoice_tax_rate_percent / 100);
            }

            InvoiceTaxRate::query()->where('invoice_tax_rate_id', $invoiceTaxRate->invoice_tax_rate_id)
                ->update(['invoice_tax_rate_amount' => $invoiceTaxRateAmount]);
        });

        // Update invoice amount with total tax using Eloquent sum
        $invoiceTaxTotal = InvoiceTaxRate::query()->where('invoice_id', $invoiceId)
            ->sum('invoice_tax_rate_amount');

        InvoiceAmount::query()->where('invoice_id', $invoiceId)
            ->update(['invoice_tax_total' => $invoiceTaxTotal]);

        $invoiceAmount = InvoiceAmount::query()->where('invoice_id', $invoiceId)->first();

        $invoiceTotal = $invoiceAmount->invoice_item_subtotal
            + $invoiceAmount->invoice_item_tax_total
            + $invoiceAmount->invoice_tax_total;

        if ($legacyCalculation) {
            $invoiceTotal = $this->calculateDiscount($invoiceId, $invoiceTotal, $decimalPlaces);
        }

        $invoicePaid = $invoiceAmount->invoice_paid ?? 0;
        InvoiceAmount::query()->where('invoice_id', $invoiceId)
            ->update([
                'invoice_total'   => $invoiceTotal,
                'invoice_balance' => $invoiceTotal - $invoicePaid,
            ]);
    }

    public function getTotalInvoiced(?string $period = null): float
    {
        return $this->sumByPeriod('invoice_total', $period);
    }

    public function getTotalPaid(?string $period = null): float
    {
        return $this->sumByPeriod('invoice_paid', $period);
    }

    public function getTotalBalance(?string $period = null): float
    {
        return $this->sumByPeriod('invoice_balance', $period);
    }

    public function getStatusTotals(string $period = 'this-month'): array
    {
        $results = match ($period) {
            'last-month'   => $this->statusTotalsForPeriod('MONTH(NOW() - INTERVAL 1 MONTH)', 'YEAR(NOW())'),
            'this-quarter' => $this->statusTotalsForPeriod('QUARTER(NOW())', 'YEAR(NOW())', 'QUARTER'),
            'last-quarter' => $this->statusTotalsForPeriod('QUARTER(NOW() - INTERVAL 1 QUARTER)', 'YEAR(NOW())', 'QUARTER'),
            'this-year'    => $this->statusTotalsForPeriod(null, 'YEAR(NOW())'),
            'last-year'    => $this->statusTotalsForPeriod(null, 'YEAR(NOW() - INTERVAL 1 YEAR)'),
            default        => $this->statusTotalsForPeriod('MONTH(NOW())', 'YEAR(NOW())'),
        };

        $return   = [];
        $statuses = InvoiceService::STATUSES;

        foreach ($statuses as $key => $status) {
            $return[$key] = [
                'invoice_status_id' => $key,
                'class'             => $status['class'],
                'label'             => $status['label'],
                'href'              => $status['href'],
                'sum_total'         => 0,
                'sum_paid'          => 0,
                'sum_balance'       => 0,
                'num_total'         => 0,
            ];
        }

        foreach ($results as $result) {
            $resultArray = (array) $result;
            $statusId    = $resultArray['invoice_status_id'];
            if (isset($return[$statusId])) {
                $return[$statusId] = array_merge($return[$statusId], $resultArray);
            }
        }

        return $return;
    }

    private function sumByPeriod(string $column, ?string $period = null): float
    {
        $query = InvoiceAmount::query();

        $this->applyPeriodFilter($query, $period);

        $result = $query->sum($column);

        return (float) ($result ?? 0.0);
    }

    private function applyPeriodFilter($query, ?string $period): void
    {
        switch ($period) {
            case 'month':
                $query->whereHas('invoice', function ($q) {
                    $q->whereRaw('MONTH(invoice_date_created) = MONTH(NOW())')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW())');
                });
                break;
            case 'last_month':
                $query->whereHas('invoice', function ($q) {
                    $q->whereRaw('MONTH(invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)')
                        ->whereRaw('YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 MONTH)');
                });
                break;
            case 'year':
                $query->whereHas('invoice', function ($q) {
                    $q->whereRaw('YEAR(invoice_date_created) = YEAR(NOW())');
                });
                break;
            case 'last_year':
                $query->whereHas('invoice', function ($q) {
                    $q->whereRaw('YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)');
                });
                break;
        }
    }

    private function statusTotalsForPeriod(?string $firstExpression, string $yearExpression, string $type = 'MONTH'): Collection
    {
        $query = InvoiceAmount::query()
            ->join('ip_invoices', 'ip_invoices.invoice_id', '=', 'ip_invoice_amounts.invoice_id')
            ->selectRaw('
                ip_invoices.invoice_status_id,
                SUM(ip_invoice_amounts.invoice_total) AS sum_total,
                SUM(ip_invoice_amounts.invoice_paid) AS sum_paid,
                SUM(ip_invoice_amounts.invoice_balance) AS sum_balance,
                COUNT(*) AS num_total
            ')
            ->whereRaw($yearExpression)
            ->groupBy('ip_invoices.invoice_status_id');

        if ($firstExpression) {
            $column = $type === 'QUARTER' ? 'QUARTER(ip_invoices.invoice_date_created)' : 'MONTH(ip_invoices.invoice_date_created)';
            $query->whereRaw($column . ' = ' . $firstExpression);
        }

        return $query->get();
    }
}
