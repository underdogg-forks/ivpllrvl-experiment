<?php

namespace Modules\Crm\Services;

/**
 * ClientNoteService.
 *
 * Service class for managing client note business logic
 */
class ClientNoteService
{
    /**
     * Get validation rules for client notes.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'client_id'   => 'required|integer',
            'note_text'   => 'required|string',
            'note_date'   => 'required|date',
        ];
    }
}
