<?php

namespace Modules\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Core\Services\CustomValueService;
use Modules\Core\Services\UserService;
use Modules\Crm\Services\UserClientService;
use Modules\Crm\Services\ClientService;
use Modules\Core\Services\CustomFieldService;

/**
 * UsersController
 *
 * Manages user account operations and administration
 *
 * @legacy-file application/modules/users/controllers/Users.php
 */
class UsersController
{
    protected UserService $userService;
    protected CustomFieldService $customFieldService;
    protected CustomValueService $customValueService;
    protected UserClientService $userClientService;
    protected ClientService $clientService;

    /**
     * Initialize the UsersController with dependency injection.
     *
     * @param UserService $userService
     * @param CustomFieldService $customFieldService
     * @param CustomValueService $customValueService
     * @param UserClientService $userClientService
     * @param ClientService $clientService
     */
    public function __construct(
        UserService $userService,
        CustomFieldService $customFieldService,
        CustomValueService $customValueService,
        UserClientService $userClientService,
        ClientService $clientService
    ) {
        $this->userService = $userService;
        $this->customFieldService = $customFieldService;
        $this->customValueService = $customValueService;
        $this->userClientService = $userClientService;
        $this->clientService = $clientService;
    }

    /**
     * Display a paginated list of users.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/users/controllers/Users.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        // TODO: Implement pagination logic
        $users = $this->userService->getAll();

        return view('core::users_index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_users'),
            'filter_method' => 'filter_users',
            'users' => $users,
            'user_types' => $this->userService->getUserTypes(),
        ]);
    }

    /**
     * Display form for creating or editing a user.
     *
     * @param Request $request
     * @param int|null $id User ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/users/controllers/Users.php
     */
    public function form(Request $request, ?int $id = null)
    {
        if ($request->has('btn_cancel')) {
            return redirect()->route('users.index');
        }

        if ($request->isMethod('post') && $request->has('btn_submit')) {
            $validated = $request->validate([
                'user_name' => 'required|string|max:255',
                'user_email' => 'required|email|max:255|unique:ip_users,user_email' . ($id ? ',' . $id . ',user_id' : ''),
                'user_password' => $id ? 'nullable|string|min:6' : 'required|string|min:6',
                'user_type' => 'required|integer',
            ]);

            if ($id) {
                $this->userService->update($id, $validated);
            } else {
                $id = $this->userService->create($validated)->user_id;
            }

            // Update the session details if the logged in user edited their account
            if (Session::get('user_id') == $id) {
                $user = $this->userService->find($id);
                $session_data = [
                    'user_type' => $user->user_type,
                    'user_id' => $user->user_id,
                    'user_name' => $user->user_name,
                    'user_email' => $user->user_email,
                    'user_company' => $user->user_company ?? '',
                    'user_language' => $user->user_language ?? 'system',
                ];
                Session::put($session_data);
            }
            Session::forget('user_clients');

            return redirect()->route('users.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        $user = $id ? $this->userService->find($id) : null;
        if ($id && !$user) {
            abort(404);
        }

        // TODO: Implement custom fields logic
        $custom_fields = [];
        $custom_values = [];

        return view('core::users_form', [
            'id' => $id,
            'user' => $user,
            'user_types' => $this->userService->getUserTypes(),
            'user_clients' => $this->userClientService->getByUserId($id ?? 0),
            'custom_fields' => $custom_fields,
            'custom_values' => $custom_values,
            'countries' => get_country_list(trans('cldr')),
            'selected_country' => $user->user_country ?? get_setting('default_country'),
            'clients' => $this->clientService->getActiveClients(),
            'languages' => get_available_languages(),
            'einvoicing' => get_setting('einvoicing'),
        ]);
    }

    /**
     * Change user password.
     *
     * @param string $user_id User ID
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function changePassword
     * @legacy-file application/modules/users/controllers/Users.php
     */
    public function changePassword(string $user_id)
    {
        if (request()->input('btn_cancel')) {
            return redirect()->route('users.index');
        }

        if (request()->isMethod('post') && request()->input('btn_submit')) {
            $validated = request()->validate([
                'user_password' => 'required|string|min:6|confirmed',
            ]);

            $this->userService->update((int) $user_id, [
                'user_password' => bcrypt($validated['user_password']),
            ]);

            return redirect()->route('users.form', ['id' => $user_id])
                ->with('alert_success', trans('password_successfully_changed'));
        }

        return view('core::users_form_change_password', [
            'user_id' => $user_id,
        ]);
    }

    /**
     * Delete a user.
     *
     * @param int|string $id User ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/users/controllers/Users.php
     */
    public function delete($id): \Illuminate\Http\RedirectResponse
    {
        // Don't delete the primary administrator
        if ($id != 1) {
            $this->userService->delete((int) $id);
        }

        return redirect()->route('users.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }

    /**
     * Delete a user-client association.
     *
     * @param string $user_id User ID
     * @param mixed $user_client_id User-client relation ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function deleteUserClient
     * @legacy-file application/modules/users/controllers/Users.php
     */
    public function deleteUserClient(string $user_id, $user_client_id): \Illuminate\Http\RedirectResponse
    {
        $this->userClientService->delete((int) $user_client_id);

        return redirect()->route('users.form', ['id' => $user_id])
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
