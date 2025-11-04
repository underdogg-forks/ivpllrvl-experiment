<?php

namespace Modules\Crm\Services;

use Exception;
use InvalidArgumentException;
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
     * @param array $data Data to validate
     *
     * @return bool Returns true if validation passes
     *
     * @throws InvalidArgumentException When validation fails
     *
     * @legacy-function validate
     */
    public function validate(array $data): bool
    {
        $errors = [];

        // Validate client_id exists and is an integer
        if (empty($data['client_id']) || ! is_numeric($data['client_id'])) {
            $errors[] = 'Client ID is required and must be a valid integer';
        }

        // Validate note text is present
        if (empty($data['client_note']) || mb_trim($data['client_note']) === '') {
            $errors[] = 'Note text is required';
        }

        // Validate note text length (assuming max 65535 for TEXT field)
        if ( ! empty($data['client_note']) && mb_strlen($data['client_note']) > 65535) {
            $errors[] = 'Note text exceeds maximum length of 65535 characters';
        }

        // Validate date format if provided
        if ( ! empty($data['client_note_date'])) {
            $timestamp = strtotime($data['client_note_date']);
            if ($timestamp === false) {
                $errors[] = 'Invalid date format for client_note_date';
            }
        }

        if ( ! empty($errors)) {
            throw new InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        return true;
    }

    /**
     * Save client note.
     *
     * @param array $data Note data to save
     *
     * @return ClientNote The saved client note
     *
     * @throws Exception When save operation fails
     *
     * @legacy-function save
     */
    public function save(array $data): ClientNote
    {
        // Validate input
        $this->validate($data);

        try {
            // If client_note_id is present, update existing note
            if ( ! empty($data['client_note_id'])) {
                $note = $this->findOrFail($data['client_note_id']);
                $note->update($data);
            } else {
                // Create new note
                $note = $this->create($data);
            }

            return $note;
        } catch (Exception $e) {
            throw new Exception('Failed to save client note: ' . $e->getMessage());
        }
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return ClientNote::class;
    }
}
