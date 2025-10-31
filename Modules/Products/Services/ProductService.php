<?php

namespace Modules\Products\Services;

use App\Services\BaseService;
use Modules\Products\Models\Product;

/**
 * ProductService.
 *
 * Service class for managing product business logic
 */
class ProductService extends BaseService
{
    /**
     * Get the model class name that this service manages.
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return Product::class;
    }
}
