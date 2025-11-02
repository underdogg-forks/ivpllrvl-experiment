<?php

namespace Modules\Crm\app\Http\Controllers;

use Illuminate\Support\Facades\DB;

use AllowDynamicProperties;
use Modules\Core\Controllers\AdminController;
use Modules\Crm\app\Services\ClientNotesService;
use Modules\Crm\app\Services\ClientsService;
use src\Services\SettingsService;

#[AllowDynamicProperties]
class ClientsAjaxController extends AdminController
{
    public $ajax_controller = true;

    /**
     * Return a JSON array of matching active clients for client-name autocompletion.
     *
     * Uses the GET parameter `query` to search active clients by name, surname, or fullname.
     * If GET `permissive_search_clients` is truthy, the query may match with a leading wildcard.
     * Echoes a JSON array of objects with `id` (client_id) and `text` (formatted client name).
     */
    public function nameQuery(): void
    {
        $response                = [];
        $query                   = request()->query('query');
        $permissiveSearchClients = request()->query('permissive_search_clients');
        if (empty($query)) {
            echo json_encode($response);
            exit;
        }
        $moreClientsQuery = $permissiveSearchClients ? '%' : '';
        $escapedQuery     = DB::escape_str($query);
        $escapedQuery     = str_replace('%', '', $escapedQuery);
        $clients          = (new ClientsService())->where('client_active', 1)->having("client_name LIKE '" . $moreClientsQuery . $escapedQuery . "%'")->or_having("client_surname LIKE '" . $moreClientsQuery . $escapedQuery . "%'")->or_having("client_fullname LIKE '" . $moreClientsQuery . $escapedQuery . "%'")->orderBy('client_name')->get()->result();
        foreach ($clients as $client) {
            $response[] = ['id' => $client->client_id, 'text' => htmlsc(format_client($client, false))];
        }
        echo json_encode($response);
    }

    /**
     * Return a JSON array of the five most recently created active clients.
     *
     * Each array element is an object with `id` set to the client's ID and
     * `text` set to the client's formatted name (HTML-escaped). Results are ordered
     * by client creation date and limited to five active clients.
     */
    public function getLatest(): void
    {
        $response = [];
        $clients  = (new ClientsService())->where('client_active', 1)->limit(5)->orderBy('client_date_created')->get()->result();
        foreach ($clients as $client) {
            $response[] = ['id' => $client->client_id, 'text' => htmlsc(format_client($client, false))];
        }
        echo json_encode($response);
    }

    /**
     * Persist the user's preference for permissive client search.
     *
     * Validates that the provided value is either "0" or "1"; if valid, saves it under the settings key `enable_permissive_search_clients`. Terminates execution immediately if the input is invalid.
     */
    public function savePreferencePermissiveSearchClients(): void
    {
        $permissiveSearchClients = request()->query('permissive_search_clients');
        if ( ! preg_match('!^[0-1]{1}$!', $permissiveSearchClients)) {
            exit;
        }
        (new SettingsService())->save('enable_permissive_search_clients', $permissiveSearchClients);
    }

    /**
     * Delete a client note identified by the POST parameter `client_note_id` and echo JSON indicating the result.
     *
     * If a note with the provided `client_note_id` exists, or if the ID is empty, the controller attempts deletion and echoes `{"success": 1}` on successful deletion or `{"success": 0}` otherwise.
     */
    public function deleteClientNote(): void
    {
        $success        = 0;
        $client_note_id = request()->input('client_note_id');
        if ((new ClientNotesService())->getById($client_note_id) || empty($client_note_id)) {
            $item = (new ClientNotesService())->delete($client_note_id);
            if ($item) {
                $success = 1;
            }
        }
        echo json_encode(['success' => $success]);
    }

    /**
     * Validate and save a client note, then output a JSON response indicating the result.
     *
     * The response is a JSON object with:
     * - `success`: `1` if the note was saved, `0` otherwise.
     * - `new_token`: a fresh CSRF token string.
     * - `validation_errors`: present only when `success` is `0`, containing validation error details.
     */
    public function saveClientNote(): void
    {
        if ((new ClientNotesService())->runValidation()) {
            (new ClientNotesService())->save();
            $response = ['success' => 1, 'new_token' => $this->security->get_csrf_hash()];
        } else {
            $response = ['success' => 0, 'new_token' => $this->security->get_csrf_hash(), 'validation_errors' => json_errors()];
        }
        echo json_encode($response);
    }

    /**
     * Load notes for the client specified by POST `client_id` and render the `clients/partial_notes` partial.
     *
     * Fetches the client's notes and passes them to the view for rendering.
     *
     * @originalName loadClientNotes
     *
     * @originalFile AjaxController.php
     */
    public function loadClientNotes(): void
    {
        $data = ['client_notes' => (new ClientNotesService())->where('client_id', request()->input('client_id'))->get()->result()];
        $this->layout->loadView('clients/partial_notes', $data);
    }
}
