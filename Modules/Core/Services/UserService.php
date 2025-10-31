<?php

namespace Modules\Core\Services;

/**
 * UserService.
 *
 * Service class for managing user business logic
 */
class UserService
{
    /**
     * Get validation rules for creating users.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'user_name'     => 'required|string|max:255',
            'user_email'    => 'required|email|max:255|unique:ip_users,user_email',
            'user_password' => 'required|string|min:6',
            'user_type'     => 'required|integer',
        ];
    }

    /**
     * Get validation rules for updating existing users.
     *
     * @param int $userId
     *
     * @return array
     */
    public function getValidationRulesExisting(int $userId): array
    {
        return [
            'user_name'     => 'required|string|max:255',
            'user_email'    => 'required|email|max:255|unique:ip_users,user_email,' . $userId . ',user_id',
            'user_password' => 'nullable|string|min:6',
            'user_type'     => 'required|integer',
        ];
    }
}
