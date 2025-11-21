<?php

namespace Modules\Products\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\Item as InvoiceItem;
use Modules\Products\Models\Product;
use Modules\Products\Models\Unit;
use Modules\Quotes\Models\QuoteItem;

/**
 * UnitService.
 *
 * Service class for managing unit business logic
 */
class UnitService extends BaseService
{
    /**
     * Get unit name with proper pluralization.
     *
     * @param int|null $unitId
     * @param float    $quantity
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/units/models/Mdl_unit.php
     *
     * @legacy-function get_name()
     *
     * @return string
     */
    public function getUnitName(?int $unitId, float $quantity = 1): string
    {
        if ( ! $unitId) {
            return '';
        }

        $unit = Unit::find($unitId);

        if ( ! $unit) {
            return '';
        }

        return ($quantity == 1) ? $unit->unit_name : $unit->unit_name_plrl;
    }

    /**
     * Get all units.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Unit::query()->get();
    }

    /**
     * Check if a unit can be deleted.
     *
     * A unit cannot be deleted if it is used by:
     * - Products
     * - Invoice items
     * - Quote items
     *
     * @param int $unitId
     *
     * @return bool
     */
    public function canDelete(int $unitId): bool
    {
        // Check products
        if (Product::query()->where('unit_id', $unitId)->exists()) {
            return false;
        }

        // Check invoice items
        if (InvoiceItem::query()->where('item_product_unit_id', $unitId)->exists()) {
            return false;
        }

        // Check quote items
        return ! (QuoteItem::query()->where('item_product_unit_id', $unitId)->exists());
    }

    /**
     * Get deletion blocker details for a unit.
     *
     * @param int $unitId
     *
     * @return array
     */
    public function getDeletionBlockers(int $unitId): array
    {
        return [
            'products'      => Product::query()->where('unit_id', $unitId)->count(),
            'invoice_items' => InvoiceItem::query()->where('item_product_unit_id', $unitId)->count(),
            'quote_items'   => QuoteItem::query()->where('item_product_unit_id', $unitId)->count(),
        ];
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Unit::class;
    }
}
