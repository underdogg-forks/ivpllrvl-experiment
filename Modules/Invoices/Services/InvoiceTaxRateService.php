<?php

namespace Modules\Invoices\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\InvoiceTaxRate;

class InvoiceTaxRateService extends BaseService
{
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
        return InvoiceTaxRate::query()->where('invoice_id', $invoiceId)->get();
    }

    /**
     * Get invoice tax rates by invoice ID.
     * Alias for getTaxRatesByInvoiceId for consistency.
     *
     * @param int $invoiceId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByInvoiceId(int $invoiceId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getTaxRatesByInvoiceId($invoiceId);
    }

    public function saveTaxRate(array $data): ?InvoiceTaxRate
    {
        if ( ! config_item('legacy_calculation')) {
            return null;
        }

        if (isset($data['invoice_tax_rate_id']) && $data['invoice_tax_rate_id']) {
            $taxRate = InvoiceTaxRate::findOrFail($data['invoice_tax_rate_id']);
            $taxRate->update($data);
        } else {
            $taxRate = InvoiceTaxRate::create($data);
        }

        if (isset($data['invoice_id'])) {
            $service        = app(InvoiceAmountService::class);
            $globalDiscount = [
                'item' => $service->getGlobalDiscount($data['invoice_id']),
            ];
            $service->calculate($data['invoice_id'], $globalDiscount);
        }

        return $taxRate;
    }

    protected function getModelClass(): string
    {
        return InvoiceTaxRate::class;
    }
}
