<?php

namespace Modules\Crm\Controllers;

use Modules\Crm\Models\Client;

/**
 * AjaxController (CRM).
 *
 * Handles AJAX requests for CRM operations
 *
 * @legacy-file application/modules/clients/controllers/Ajax.php
 */
class AjaxController
{
    public function modalClientLookup()
    {
        $clients = Client::query()->where('client_active', 1)->orderBy('client_name')->get();

        return view('crm::modal_client_lookup', ['clients' => $clients]);
    }

    public function getClientDetails(int $clientId)
    {
        $client = Client::query()->findOrFail($clientId);

        return response()->json($client);
    }
}
