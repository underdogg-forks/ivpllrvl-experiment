<?php

namespace Modules\Crm\Http\Controllers;

use Modules\Crm\Entities\Client;

class ClientsController
{
    /** @legacy-file application/modules/clients/controllers/Clients.php:31 */
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('clients.status', ['status' => 'active']);
    }

    /** @legacy-file application/modules/clients/controllers/Clients.php:40 */
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
            'records' => $clients,
            'filter_display' => true,
            'filter_placeholder' => trans('filter_clients'),
            'filter_method' => 'filter_clients',
            'einvoicing' => get_setting('einvoicing'),
        ]);
    }

    /** @legacy-file application/modules/clients/controllers/Clients.php:77 */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) return redirect()->route('clients.index');
        
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate(Client::validationRules());
            if ($id) {
                Client::findOrFail($id)->update($validated);
            } else {
                Client::create($validated);
            }
            return redirect()->route('clients.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $client = $id ? Client::findOrFail($id) : new Client();
        return view('crm::clients_form', ['client' => $client]);
    }

    /** @legacy-file application/modules/clients/controllers/Clients.php:205 */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        Client::findOrFail($id)->delete();
        return redirect()->route('clients.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
