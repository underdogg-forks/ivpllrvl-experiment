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
     * Get a payment with its relationships.
     *
     * @param int   $id        Payment ID
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
     * @param int   $perPage   Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['invoice', 'paymentMethod'], int $perPage = 15)
    {
        return Payment::query()->with($relations)
            ->orderBy('payment_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all payments for a specific client.
     *
     * @param int $clientId Client ID
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByClientId(int $clientId): \Illuminate\Database\Eloquent\Collection
    {
        return Payment::query()->where('client_id', $clientId)->get();
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Payment::class;
    }
}
