<?php

namespace Modules\Crm\Controllers;

use Modules\Crm\Http\Requests\ClientRequest;
use Modules\Crm\Models\Client;
use Modules\Crm\Services\ClientService;

class ClientsController
{
    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('clients.status', ['status' => 'active']);
    }

    public function status(string $status = 'active', int $page = 0): \Illuminate\View\View
    {
        $query = Client::query();

        if ($status === 'active') {
            $query->where('client_active', 1);
        } elseif ($status === 'inactive') {
            $query->where('client_active', 0);
        }

        $clients = $query->with('invoices')->orderBy('client_name')->paginate(15, ['*'], 'page', $page);

        return view('crm::clients_index', [
            'records'            => $clients,
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_clients'),
            'filter_method'      => 'filter_clients',
            'einvoicing'         => get_setting('einvoicing'),
        ]);
    }

    public function create(): \Illuminate\View\View
    {
        $client = new Client();
        return view('crm::clients_form', ['client' => $client]);
    }

    public function store(ClientRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->clientService->create($request->validated());
        return redirect()->route('clients.index')->with('alert_success', trans('record_successfully_saved'));
    }

    public function edit(Client $client): \Illuminate\View\View
    {
        return view('crm::clients_form', ['client' => $client]);
    }

    public function update(ClientRequest $request, Client $client): \Illuminate\Http\RedirectResponse
    {
        $this->clientService->update($client->client_id, $request->validated());
        return redirect()->route('clients.index')->with('alert_success', trans('record_successfully_saved'));
    }

    public function destroy(Client $client): \Illuminate\Http\RedirectResponse
    {
        $this->clientService->delete($client->client_id);
        return redirect()->route('clients.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
