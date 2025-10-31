<?php

namespace Modules\Products\Services;

/**
 * FamilyService.
 *
 * Service class for managing product family business logic
 */
class FamilyService
{
    /**
     * Get validation rules for product families.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'family_name' => 'required|string|max:255',
        ];
    }
}
