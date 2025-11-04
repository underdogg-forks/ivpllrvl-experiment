<?php

namespace Modules\Products\Services;

use Modules\Core\Services\BaseService;
use Modules\Products\Models\TaxRate;

/**
 * TaxRateService.
 *
 * Service class for managing tax rate business logic
 */
class TaxRateService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return TaxRate::class;
    }

    /**
     * Get all tax rates.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return TaxRate::query()->get();
    }

    /**
     * Get all tax rates ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllOrdered()
    {
        return TaxRate::query()->orderBy('tax_rate_name')->get();
    }
}
