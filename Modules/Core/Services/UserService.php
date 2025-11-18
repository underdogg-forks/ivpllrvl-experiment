<?php

namespace Modules\Core\Services;

use Modules\Core\Models\User;

/**
 * UserService.
 *
 * Service class for managing user business logic
 */
class UserService extends BaseService
{
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
    /**
     * Get active admin users, optionally filtered by user ID.
     *
     * @param string $userId Optional user ID to filter
     *
     * @return array
     */
    public function getActiveAdminUsers(string $userId = ''): array
    {
        $query = User::query()->where('user_type', '1')
            ->where('user_active', '1');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get()->toArray();
    }

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
        return User::query()->get();
    }

    /**
     * Check if user can be deleted.
     *
     * @param int $id User ID
     *
     * @return bool True if user can be deleted
     */
    public function canDelete(int $id): bool
    {
        $blockers = $this->getDeletionBlockers($id);

        return $blockers['invoices'] === 0 
            && $blockers['quotes'] === 0 
            && $blockers['sessions'] === 0;
    }

    /**
     * Get deletion blockers for user.
     *
     * @param int $id User ID
     *
     * @return array Array of blocker counts
     */
    public function getDeletionBlockers(int $id): array
    {
        return [
            'invoices' => \Modules\Invoices\Models\Invoice::query()
                ->where('user_id', $id)
                ->count(),
            'quotes' => \Modules\Quotes\Models\Quote::query()
                ->where('user_id', $id)
                ->count(),
            'sessions' => \Modules\Core\Models\Session::query()
                ->where('user_id', $id)
                ->count(),
        ];
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

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return User::class;
    }
}
