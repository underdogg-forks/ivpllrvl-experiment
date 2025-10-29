<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\User;

/**
 * UsersController
 * 
 * Manages user accounts
 */
class UsersController
{
    /**
     * @legacy-function index
     * @legacy-file application/modules/users/controllers/Users.php
     * @legacy-line 32
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $users = User::query()->orderBy('user_name')->paginate(15, ['*'], 'page', $page);
        return view('users::index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_users'),
            'filter_method' => 'filter_users',
            'users' => $users,
            'user_types' => User::USER_TYPES,
        ]);
    }

    /**
     * @legacy-function form
     * @legacy-file application/modules/users/controllers/Users.php
     * @legacy-line 50
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('users.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $rules = $id ? User::validationRulesExisting() : User::validationRules();
            $validated = request()->validate($rules);

            if ($id) {
                $user = User::query()->findOrFail($id);
                $user->update($validated);
            } else {
                $user = User::query()->create($validated);
                $id = $user->user_id;
            }

            return redirect()->route('users.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        if ($id) {
            $user = User::query()->find($id);
            if (!$user) abort(404);
        } else {
            $user = new User();
        }

        return view('users::form', ['user' => $user]);
    }

    /**
     * @legacy-function delete
     * @legacy-file application/modules/users/controllers/Users.php
     * @legacy-line 204
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $user = User::query()->findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
