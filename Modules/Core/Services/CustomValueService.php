<?php

namespace Modules\Core\Services;

use Modules\Core\Services\BaseService;
use Modules\Core\Models\CustomValue;

/**
 * CustomValueService.
 *
 * Service class for managing custom value business logic
 */
class CustomValueService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return CustomValue::class;
    }

    /**
     * Get validation rules for custom values.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'custom_values_field' => 'required|integer',
            'custom_values_value' => 'required|string|max:255',
            'custom_values_order' => 'nullable|integer',
        ];
    }

    /**
     * Get custom values by field ID.
     *
     * @param int $customFieldId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByFieldId(int $customFieldId)
    {
        return CustomValue::query()->where('custom_field_id', $customFieldId)->get();
    }
}
