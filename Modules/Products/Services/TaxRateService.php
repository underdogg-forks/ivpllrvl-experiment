<?php

namespace Modules\Products\Services;

use App\Services\BaseService;
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
}
