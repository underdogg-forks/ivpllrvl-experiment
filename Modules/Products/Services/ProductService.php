<?php

namespace Modules\Products\Services;

use Modules\Core\Services\BaseService;
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

    /**
     * Get products by IDs.
     *
     * @param array $productIds
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByIds(array $productIds)
    {
        return Product::query()->whereIn('product_id', $productIds)->get();
    }
}
