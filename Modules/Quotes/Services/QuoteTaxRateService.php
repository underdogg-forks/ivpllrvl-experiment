<?php

namespace Modules\Quotes\Services;

use Modules\Quotes\Models\QuoteTaxRate;

/**
 * QuoteTaxRateService.
 *
 * Service class for managing quote tax rates business logic
 * Extracted from QuoteTaxRate model
 */
class QuoteTaxRateService
{
    /**
     * QuoteAmountService instance.
     *
     * @var QuoteAmountService
     */
    protected QuoteAmountService $quoteAmountService;

    /**
     * Constructor.
     *
     * @param QuoteAmountService $quoteAmountService
     */
    public function __construct(QuoteAmountService $quoteAmountService)
    {
        $this->quoteAmountService = $quoteAmountService;
    }

    /**
     * Get validation rules for quote tax rates.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'quote_id'         => 'required|integer',
            'tax_rate_id'      => 'required|integer',
            'include_item_tax' => 'required|integer',
        ];
    }

    /**
     * Save quote tax rate and trigger calculations.
     * Only applicable in legacy calculation mode.
     *
     * @param array $data
     *
     * @return QuoteTaxRate|null
     */
    public function saveTaxRate(array $data): ?QuoteTaxRate
    {
        // Only applicable in legacy calculation mode
        if (! config_item('legacy_calculation')) {
            return null;
        }

        // Create or update the tax rate
        if (isset($data['quote_tax_rate_id']) && $data['quote_tax_rate_id']) {
            $taxRate = QuoteTaxRate::findOrFail($data['quote_tax_rate_id']);
            $taxRate->update($data);
        } else {
            $taxRate = QuoteTaxRate::create($data);
        }

        // Recalculate quote amounts if quote_id is provided
        if (isset($data['quote_id'])) {
            $globalDiscount = [
                'item' => $this->quoteAmountService->getGlobalDiscount($data['quote_id']),
            ];
            $this->quoteAmountService->calculate($data['quote_id'], $globalDiscount);
        }

        return $taxRate;
    }
}
