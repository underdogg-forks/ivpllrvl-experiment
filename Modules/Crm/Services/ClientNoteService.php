<?php

namespace Modules\Crm\Services;

use Modules\Core\Services\BaseService;
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

    /**
     * Get client notes by client ID.
     *
     * @param int $clientId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @legacy-function getByClientId
     */
    public function getByClientId(int $clientId)
    {
        return ClientNote::query()
            ->where('client_id', $clientId)
            ->orderBy('client_note_date', 'desc')
            ->get();
    }

    /**
     * Validate client note data.
     *
     * @return bool
     *
     * @legacy-function validate
     */
    public function validate(): bool
    {
        // TODO: Implement validation logic
        return true;
    }

    /**
     * Save client note.
     *
     * @return void
     *
     * @legacy-function save
     */
    public function save(): void
    {
        // TODO: Implement save logic
    }
}
