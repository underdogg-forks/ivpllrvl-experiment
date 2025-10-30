<?php

namespace Modules\Crm\Controllers;

use Modules\Crm\Models\UserClient;

class UserClientsController
{
    /** @legacy-file application/modules/user_clients/controllers/User_clients.php */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $userClients = UserClient::with(['user', 'client'])->paginate(15, ['*'], 'page', $page);

        return view('crm::user_clients_index', ['user_clients' => $userClients]);
    }

    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('user_clients.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'user_id'   => 'required|integer|exists:ip_users,user_id',
                'client_id' => 'required|integer|exists:ip_clients,client_id',
            ]);
            if ($id) {
                UserClient::query()->findOrFail($id)->update($validated);
            } else {
                UserClient::query()->create($validated);
            }

            return redirect()->route('user_clients.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $userClient = $id ? UserClient::query()->findOrFail($id) : new UserClient();

        return view('crm::user_clients_form', ['user_client' => $userClient]);
    }

    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        UserClient::query()->findOrFail($id)->delete();

        return redirect()->route('user_clients.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
