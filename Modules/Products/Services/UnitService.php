<?php

namespace Modules\Products\Services;

use Modules\Products\Models\Unit;

/**
 * UnitService.
 *
 * Service class for managing unit business logic
 */
class UnitService
{
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
     * Get validation rules for units.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'unit_name'      => 'required|string|max:255',
            'unit_name_plrl' => 'required|string|max:255',
        ];
    }
}
