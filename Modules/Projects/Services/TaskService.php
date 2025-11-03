<?php

namespace Modules\Projects\Services;

use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Task;

/**
 * TaskService.
 *
 * Service class for managing task business logic
 */
class TaskService extends BaseService
{
    protected function getModelClass(): string
    {
        return Task::class;
    }

    /**
     * Update tasks by invoice ID.
     *
     * @param int   $invoiceId
     * @param array $data
     *
     * @return int
     */
    public function updateByInvoiceId(int $invoiceId, array $data): int
    {
        return $this->query()->where('invoice_id', $invoiceId)->update($data);
    }
}
