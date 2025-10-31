<?php

namespace Modules\Crm\Services;

/**
 * UserClientService.
 *
 * Service class for managing user-client relationship business logic
 */
class UserClientService
{
    /**
     * Get validation rules for user-client relationships.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'user_id'   => 'required|integer',
            'client_id' => 'required|integer',
        ];
    }
}
