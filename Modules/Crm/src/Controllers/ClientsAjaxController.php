<?php

namespace Modules\Crm\Controllers;

use Modules\Core\Services\SettingsService;
use Modules\Core\Support\ClientHelper;
use Modules\Core\Support\EchoHelper;
use Modules\Crm\Models\Client;
use Modules\Crm\Services\ClientNoteService;
use Modules\Crm\Services\ClientService;

/**
 * ClientsAjaxController.
 *
 * Handles AJAX requests for client-related operations
 *
 * @legacy-file application/modules/clients/controllers/Ajax.php
 */
class ClientsAjaxController
{
    /**
     * Initialize the ClientsAjaxController with dependency injection.
     * Initialize the ClientsAjaxController with dependency injection.
     *
     * @param ClientService     $clientService
     * @param ClientNoteService $clientNoteService
     * @param SettingsService   $settingsService
     */
    public function __construct(
        protected ClientService $clientService,
        protected ClientNoteService $clientNoteService,
        protected SettingsService $settingsService
    ) {}

    /**
     * Search for clients by name query (AJAX endpoint).
     *
     * @return void Outputs JSON response
     *
     * @legacy-file application/modules/clients/controllers/Ajax.php
     *
     * @legacy-function name_query
     */
    public function nameQuery(): void
    {
        $response                = [];
        $query                   = request()->query('query');
        $permissiveSearchClients = request()->query('permissive_search_clients');

        if (empty($query)) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        // Search for chars "in the middle" of clients names
        $moreClientsQuery = $permissiveSearchClients ? '%' : '';
        $escapedQuery     = str_replace('%', '', $query);

        $clients = Client::query()
            ->where('client_active', 1)
            ->where(function ($q) use ($escapedQuery, $moreClientsQuery) {
                $q->where('client_name', 'LIKE', $moreClientsQuery . $escapedQuery . '%')
                    ->orWhere('client_surname', 'LIKE', $moreClientsQuery . $escapedQuery . '%')
                    ->orWhere('client_fullname', 'LIKE', $moreClientsQuery . $escapedQuery . '%');
            })
            ->orderBy('client_name')
            ->get();

        foreach ($clients as $client) {
            $response[] = ['id' => $client->client_id, 'text' => EchoHelper::htmlsc(ClientHelper::format_client($client, false))];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get latest active clients (AJAX endpoint).
     *
     * @return void Outputs JSON response
     *
     * @legacy-file application/modules/clients/controllers/Ajax.php
     *
     * @legacy-function get_latest
     */
    public function getLatest(): void
    {
        // Load the model & helper
        $this->load->model('clients/client');

        $response = [];
        $clients  = Client::query()
            ->where('client_active', 1)
            ->limit(5)
            ->orderBy('client_date_created')
            ->get();

        foreach ($clients as $client) {
            $response[] = ['id' => $client->client_id, 'text' => EchoHelper::htmlsc(ClientHelper::format_client($client, false))];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Save user's permissive search preference (AJAX endpoint).
     *
     * @return void
     *
     * @legacy-file application/modules/clients/controllers/Ajax.php
     *
     * @legacy-function save_preference_permissive_search_clients
     */
    public function savePreferencePermissiveSearchClients(): void
    {
        $permissiveSearchClients = request()->query('permissive_search_clients');
        if ( ! preg_match('!^[0-1]{1}$!', $permissiveSearchClients)) {
            exit;
        }
        $this->settingsService->save('enable_permissive_search_clients', $permissiveSearchClients);
    }

    /**
     * Delete client note (AJAX endpoint).
     *
     * @return void Outputs JSON response
     *
     * @legacy-file application/modules/clients/controllers/Ajax.php
     *
     * @legacy-function delete_client_note
     */
    public function deleteClientNote(): void
    {
        $success        = 0;
        $client_note_id = request()->input('client_note_id');

        if ($this->clientNoteService->find($client_note_id) || empty($client_note_id)) {
            $result = $this->clientNoteService->delete($client_note_id);
            if ($result) {
                $success = 1;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }

    /**
     * Save client note (AJAX endpoint).
     *
     * @return void Outputs JSON response
     *
     * @legacy-file application/modules/clients/controllers/Ajax.php
     *
     * @legacy-function save_client_note
     */
    public function saveClientNote(): void
    {
        // TODO: Implement validation
        if ($this->clientNoteService->validate()) {
            $this->clientNoteService->save();
            $response = ['success' => 1, 'new_token' => csrf_token()];
        } else {
            $response = ['success' => 0, 'new_token' => csrf_token(), 'validation_errors' => json_errors()];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Load client notes partial (AJAX endpoint).
     *
     * @return \Illuminate\View\View
     *
     * @legacy-file application/modules/clients/controllers/Ajax.php
     *
     * @legacy-function load_client_notes
     */
    public function loadClientNotes(): \Illuminate\View\View
    {
        $client_id    = request()->input('client_id');
        $client_notes = $this->clientNoteService->getByClientId($client_id);

        return view('crm::clients_partial_notes', [
            'client_notes' => $client_notes,
        ]);
    }
}
