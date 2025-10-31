<?php

namespace Modules\Payments\Services;

use App\Services\BaseService;
use Modules\Payments\Models\PaymentMethod;

/**
 * PaymentMethodService.
 *
 * Service class for managing payment method business logic
 */
class PaymentMethodService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return PaymentMethod::class;
    }
}
