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
        return TaxRate::all();
    }
}
