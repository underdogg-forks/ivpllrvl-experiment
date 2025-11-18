<?php

namespace Modules\Products\Services;

use Modules\Core\Services\BaseService;
use Modules\Products\Models\Product;
use Modules\Invoices\Models\Item;

/**
 * ProductService.
 *
 * Service class for managing product business logic
 */
class ProductService extends BaseService
{
    /**
     * Get products by IDs.
     *
     * @param array $productIds
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByIds(array $productIds): \Illuminate\Database\Eloquent\Collection
    {
        return Product::query()->whereIn('product_id', $productIds)->get();
    }

    /**
     * Check if a product can be deleted.
     *
     * A product cannot be deleted if it is referenced by any invoice items.
     *
     * @param int $productId
     *
     * @return bool
     */
    public function canDelete(int $productId): bool
    {
        // Check if product is used in any invoice items
        $itemCount = Item::query()
            ->where('item_product_id', $productId)
            ->count();

        return $itemCount === 0;
    }

    /**
     * Get count of invoice items that reference this product.
     *
     * @param int $productId
     *
     * @return int
     */
    public function getInvoiceItemCount(int $productId): int
    {
        return Item::query()
            ->where('item_product_id', $productId)
            ->count();
    }

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
