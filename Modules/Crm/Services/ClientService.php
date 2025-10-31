<?php

namespace Modules\Crm\Services;

/**
 * ClientService.
 *
 * Service class for managing client business logic
 */
class ClientService
{
    /**
     * Get validation rules for clients.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'client_name'           => 'required|string|max:255',
            'client_surname'        => 'nullable|string|max:255',
            'client_email'          => 'nullable|email|max:255',
            'client_phone'          => 'nullable|string|max:50',
            'client_mobile'         => 'nullable|string|max:50',
            'client_address_1'      => 'nullable|string|max:255',
            'client_address_2'      => 'nullable|string|max:255',
            'client_city'           => 'nullable|string|max:255',
            'client_state'          => 'nullable|string|max:255',
            'client_zip'            => 'nullable|string|max:20',
            'client_country'        => 'nullable|string|max:255',
            'client_vat_id'         => 'nullable|string|max:50',
            'client_tax_code'       => 'nullable|string|max:50',
            'client_language'       => 'nullable|string|max:10',
            'client_active'         => 'nullable|boolean',
        ];
    }
}
