<?php

namespace Modules\Products\Services;

use App\Services\BaseService;
use Modules\Products\Models\Family;

/**
 * FamilyService.
 *
 * Service class for managing product family business logic
 */
class FamilyService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Family::class;
    }
}
