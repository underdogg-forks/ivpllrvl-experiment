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
}
