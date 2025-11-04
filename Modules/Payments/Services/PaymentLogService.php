<?php

namespace Modules\Payments\Services;

use Modules\Core\Services\BaseService;
use Modules\Payments\Models\PaymentLog;

/**
 * PaymentLogService.
 *
 * Service class for managing payment log business logic
 */
class PaymentLogService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return PaymentLog::class;
    }

    /**
     * Get all payment logs with relationships, ordered by date descending.
     *
     * @param array $relations Relations to eager load (default: invoice)
     * @param int $perPage Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['invoice'], int $perPage = 15)
    {
        return PaymentLog::query()->with($relations)
            ->orderBy('payment_log_date', 'desc')
            ->paginate($perPage);
    }
}
