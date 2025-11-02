<?php

namespace Modules\Invoices\Services;

use App\Services\BaseService;
use Modules\Invoices\Models\InvoiceTaxRate;

class InvoiceTaxRateService extends BaseService
{
    protected function getModelClass(): string
    {
        return InvoiceTaxRate::class;
    }

    public function getValidationRules(): array
    {
        return [
            'invoice_id'       => 'required|integer',
            'tax_rate_id'      => 'required|integer',
            'include_item_tax' => 'required|integer',
        ];
    }

    public function getTaxRatesByInvoiceId(int $invoiceId): \Illuminate\Database\Eloquent\Collection
    {
        return InvoiceTaxRate::where('invoice_id', $invoiceId)->get();
    }

    public function saveTaxRate(array $data): ?InvoiceTaxRate
    {
        if (! config_item('legacy_calculation')) {
            return null;
        }

        if (isset($data['invoice_tax_rate_id']) && $data['invoice_tax_rate_id']) {
            $taxRate = InvoiceTaxRate::findOrFail($data['invoice_tax_rate_id']);
            $taxRate->update($data);
        } else {
            $taxRate = InvoiceTaxRate::create($data);
        }

        if (isset($data['invoice_id'])) {
            $service = app(InvoiceAmountService::class);
            $globalDiscount = [
                'item' => $service->getGlobalDiscount($data['invoice_id']),
            ];
            $service->calculate($data['invoice_id'], $globalDiscount);
        }

        return $taxRate;
    }
}
