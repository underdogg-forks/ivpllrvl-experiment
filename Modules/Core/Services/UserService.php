<?php

namespace Modules\Core\Services;

use Modules\Core\Services\BaseService;
use Modules\Core\Models\User;

/**
 * UserService.
 *
 * Service class for managing user business logic
 */
class UserService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Get validation rules for creating users (kept for backward compatibility).
     * Validation should be done via FormRequest in controllers.
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
     * Get validation rules for updating existing users (kept for backward compatibility).
     * Validation should be done via FormRequest in controllers.
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

    /**
     * Check if there are multiple active admin users.
     *
     * Used to determine if user change functionality should be enabled.
     *
     * @return bool
     */
    public function hasMultipleActiveAdmins(): bool
    {
        return User::active()->admin()->count() > 1;
    }

    /**
     * Get all users.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return User::all();
    }

    /**
     * Get user types.
     *
     * @return array
     *
     * @legacy-function userTypes
     */
    public function getUserTypes(): array
    {
        return [
            1 => trans('administrator'),
            2 => trans('guest_read_only'),
        ];
    }
}
