<?php

namespace Modules\Core\Controllers;

use AllowDynamicProperties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Core\Services\CustomValuesService;
use Modules\Core\Services\UserClientsService;
use Modules\Core\Services\UsersService;
use Modules\Crm\app\Services\ClientsService;
use Modules\CustomFields\Services\CustomFieldsService;
use Modules\Users\Controllers\UserCustomService;

#[AllowDynamicProperties]
class UsersController extends AdminController
{
    /**
     * UsersController constructor.
     */
    public function __construct(
        protected UsersService $usersService,
        protected UserCustomService $userCustomService,
        protected CustomFieldsService $customFieldsService,
        protected CustomValuesService $customValuesService,
        protected UserClientsService $userClientsService,
        protected ClientsService $clientsService
    ) {
        parent::__construct();
    }

    /**
     * @originalName index
     *
     * @originalFile UsersController.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $this->usersService->paginate(route('users.index'), $page);
        $users = $this->usersService->result();

        return view('users.index', [
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_users'),
            'filter_method'      => 'filter_users',
            'users'              => $users,
            'user_types'         => $this->usersService->userTypes(),
        ]);
    }

    /**
     * @originalName form
     *
     * @originalFile UsersController.php
     */
    public function form(Request $request, ?int $id = null): \Illuminate\View\View
    {
        if ($request->has('btn_cancel')) {
            return redirect()->route('users');
        }
        $this->filterInput();
        if ($this->usersService->runValidation($id ? 'validation_rules_existing' : 'validation_rules')) {
            $id = $this->usersService->save($id);
            $this->userCustomService->saveCustom($id, $request->input('custom'));
            // Update the session details if the logged in user edited his account
            if (Session::get('user_id') == $id) {
                $new_details  = $this->usersService->getById($id);
                $session_data = [
                    'user_type'     => $new_details->user_type,
                    'user_id'       => $new_details->user_id,
                    'user_name'     => $new_details->user_name,
                    'user_email'    => $new_details->user_email,
                    'user_company'  => $new_details->user_company,
                    'user_language' => $new_details->user_language ?? 'system',
                ];
                Session::put($session_data);
            }
            Session::forget('user_clients');

            return redirect()->route('users');
        }
        if ($id && ! $request->has('btn_submit')) {
            if ( ! $this->usersService->prepForm($id)) {
                abort(404);
            }
            $user_custom = $this->userCustomService->where('user_id', $id)->get();
            if ($user_custom->count()) {
                $user_custom = $user_custom->first();
                foreach ($user_custom->toArray() as $key => $val) {
                    if ( ! in_array($key, ['user_id', 'user_custom_id'])) {
                        $this->usersService->setFormValue('custom[' . $key . ']', $val);
                    }
                }
            }
        } elseif ($request->has('btn_submit')) {
            if ($request->input('custom')) {
                foreach ($request->input('custom') as $key => $val) {
                    $this->usersService->setFormValue('custom[' . $key . ']', $val);
                }
            }
        }
        $custom_fields['ip_user_custom'] = $this->customFieldsService->byTable('ip_user_custom')->get()->all();
        $custom_values                   = [];
        foreach ($custom_fields['ip_user_custom'] as $custom_field) {
            if (in_array($custom_field->custom_field_type, $this->customValuesService->customValueFields())) {
                $values                                        = $this->customValuesService->getByFid($custom_field->custom_field_id)->get()->all();
                $custom_values[$custom_field->custom_field_id] = $values;
            }
        }
        $fields = $this->userCustomService->getByUseid($id);
        foreach ($custom_fields['ip_user_custom'] as $cfield) {
            foreach ($fields as $fvalue) {
                if ($fvalue->user_custom_fieldid == $cfield->custom_field_id) {
                    $this->usersService->setFormValue('custom[' . $cfield->custom_field_id . ']', $fvalue->user_custom_fieldvalue);
                    break;
                }
            }
        }
        $custom_fields['ip_invoice_custom'] = $this->customFieldsService->byTable('ip_invoice_custom')->get()->all();

        return view('users.form', [
            'id'               => $id,
            'user_types'       => $this->usersService->userTypes(),
            'user_clients'     => $this->userClientsService->where('ip_user_clients.user_id', $id)->get()->all(),
            'custom_fields'    => $custom_fields,
            'custom_values'    => $custom_values,
            'countries'        => get_country_list(trans('cldr')),
            'selected_country' => $this->usersService->formValue('user_country') ?: get_setting('default_country'),
            'clients'          => $this->clientsService->where('client_active', 1)->get()->all(),
            'languages'        => get_available_languages(),
            'einvoicing'       => get_setting('einvoicing'),
        ]);
    }

    /**
     * @originalName changePassword
     *
     * @originalFile UsersController.php
     */
    public function changePassword(string $user_id)
    {
        if (request()->input('btn_cancel')) {
            redirect()->route('users');
        }
        if ((new UsersService())->runValidation('validation_rules_change_password')) {
            (new UsersService())->saveChangePassword($user_id, request()->input('user_password'));
            redirect('users/form/' . $user_id);
        }
        $this->layout->buffer('content', 'users/form_change_password');
        $this->layout->render();
    }

    /**
     * Delete a user by id and redirect to the users list.
     *
     * Does not delete the primary administrator with id 1; in all cases the request is redirected to the users route.
     *
     * @param int|string $id the identifier of the user to delete
     */
    public function delete($id)
    {
        if ($id != 1) {
            (new UsersService())->delete($id);
        }
        redirect()->route('users');
    }

    /**
     * Delete a user-client association and redirect back to the user's form.
     *
     * @param string $user_id        the ID of the user whose form to return to after deletion
     * @param mixed  $user_client_id the identifier of the user-client linkage to delete
     */
    public function deleteUserClient(string $user_id, $user_client_id)
    {
        (new UserClientsService())->delete($user_client_id);
        redirect('users/form/' . $user_id);
    }
}
