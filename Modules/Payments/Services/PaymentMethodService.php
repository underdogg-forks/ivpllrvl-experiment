<?php

namespace Modules\Payments\Services;

use Modules\Core\Services\BaseService;
use Modules\Payments\Models\PaymentMethod;

/**
 * PaymentMethodService.
 *
 * Service class for managing payment method business logic
 */
class PaymentMethodService extends BaseService
{
    /**
     * Get all payment methods ordered by name.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15, int $page = 0): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return PaymentMethod::query()->orderBy('payment_method_name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all payment methods ordered by name (not paginated).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllOrdered()
    {
        return PaymentMethod::query()->orderBy('payment_method_name')->get();
    }

    /**
     * Find payment method by ID.
     *
     * @param int $paymentMethodId
     *
     * @return PaymentMethod|null
     */
    public function findByMethodId(int $paymentMethodId): ?PaymentMethod
    {
        return PaymentMethod::query()->where('payment_method_id', $paymentMethodId)->first();
    }

    /**
     * Check if payment method can be deleted.
     *
     * @param int $id Payment method ID
     *
     * @return bool True if payment method can be deleted
     */
    public function canDelete(int $id): bool
    {
        $blockers = $this->getDeletionBlockers($id);

        return $blockers['payments'] === 0;
    }

    /**
     * Get deletion blockers for payment method.
     *
     * @param int $id Payment method ID
     *
     * @return array Array of blocker counts
     */
    public function getDeletionBlockers(int $id): array
    {
        return [
            'payments' => \Modules\Payments\Models\Payment::query()
                ->where('payment_method_id', $id)
                ->count(),
        ];
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return PaymentMethod::class;
    }
}
