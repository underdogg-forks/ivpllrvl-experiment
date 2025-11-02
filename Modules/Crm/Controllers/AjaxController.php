<?php

namespace Modules\Crm\Controllers;

use Modules\Crm\Models\Client;
use Modules\Crm\Services\ClientService;

/**
 * AjaxController (CRM).
 *
 * Handles AJAX requests for CRM operations
 *
 * @legacy-file application/modules/clients/controllers/Ajax.php
 */
class AjaxController
{
    /**
     * Client service instance.
     *
     * @var ClientService
     */
    protected ClientService $clientService;

    /**
     * Constructor.
     *
     * @param ClientService $clientService
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function modalClientLookup()
    {
        $clients = $this->clientService->getActiveClients();

        return view('crm::modal_client_lookup', ['clients' => $clients]);
    }

    public function getClientDetails(int $clientId)
    {
        $client = $this->clientService->findOrFail($clientId);

        return response()->json($client);
    }
}
