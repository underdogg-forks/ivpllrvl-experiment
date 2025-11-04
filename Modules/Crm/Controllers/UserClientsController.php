<?php

namespace Modules\Crm\Controllers;

use Modules\Crm\Models\UserClient;
use Modules\Crm\Services\UserClientService;

class UserClientsController
{
    /**
     * UserClient service instance.
     *
     * @var UserClientService
     */
    /**
     * Constructor.
     *
     * @param UserClientService $userClientService
     */
    public function __construct(
        protected UserClientService $userClientService
    ) {
    }

    /** @legacy-file application/modules/user_clients/controllers/User_clients.php */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $userClients = $this->userClientService->getAllPaginated($page);

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
                $this->userClientService->update($id, $validated);
            } else {
                $this->userClientService->create($validated);
            }

            return redirect()->route('user_clients.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $userClient = $id ? $this->userClientService->findOrFail($id) : new UserClient();

        return view('crm::user_clients_form', ['user_client' => $userClient]);
    }

    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->userClientService->delete($id);

        return redirect()->route('user_clients.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
