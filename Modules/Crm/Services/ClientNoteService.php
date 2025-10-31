<?php

namespace Modules\Crm\Services;

use App\Services\BaseService;
use Modules\Crm\Models\ClientNote;

/**
 * ClientNoteService.
 *
 * Service class for managing client note business logic
 */
class ClientNoteService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return ClientNote::class;
    }
}
