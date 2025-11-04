<?php

namespace Modules\Payments\Services;

use Modules\Core\Services\BaseService;
use Modules\Payments\Models\Payment;

/**
 * PaymentService.
 *
 * Service class for managing payment business logic
 */
class PaymentService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Payment::class;
    }

    /**
     * Get a payment with its relationships.
     *
     * @param int $id Payment ID
     * @param array $relations Relations to eager load (default: invoice, paymentMethod)
     *
     * @return Payment|null
     */
    public function findWithRelations(int $id, array $relations = ['invoice', 'paymentMethod']): ?Payment
    {
        return Payment::query()->with($relations)->find($id);
    }

    /**
     * Get all payments with relationships, ordered by date descending.
     *
     * @param array $relations Relations to eager load (default: invoice, paymentMethod)
     * @param int $perPage Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['invoice', 'paymentMethod'], int $perPage = 15)
    {
        return Payment::query()->with($relations)
            ->orderBy('payment_date', 'desc')
            ->paginate($perPage);
    }
}
