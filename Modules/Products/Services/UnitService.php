<?php

namespace Modules\Products\Services;

use App\Services\BaseService;
use Modules\Products\Models\Unit;

/**
 * UnitService.
 *
 * Service class for managing unit business logic
 */
class UnitService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Unit::class;
    }

    /**
     * Get unit name with proper pluralization.
     *
     * @param int|null $unitId
     * @param float    $quantity
     *
     * @return string
     */
    public function getUnitName(?int $unitId, float $quantity = 1): string
    {
        if (!$unitId) {
            return '';
        }

        $unit = Unit::find($unitId);

        if (!$unit) {
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
        return Unit::all();
    }
}
