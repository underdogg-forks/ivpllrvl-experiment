<?php

namespace Modules\Payments\Services;

use App\Services\BaseService;
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
}
