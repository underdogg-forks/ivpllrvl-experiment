<?php

namespace Modules\Users\Controllers;

/**
 * SessionsController
 * 
 * Handles user authentication (login, logout, password reset)
 * 
 * Note: Simplified migration - full auth logic deferred
 */
class SessionsController
{
    /**
     * @legacy-function index
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     * @legacy-line 19
     */
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('sessions.login');
    }

    /**
     * @legacy-function login
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     * @legacy-line 24
     */
    public function login()
    {
        if (request()->post('btn_login')) {
            // TODO: Full auth implementation
            return redirect()->route('dashboard');
        }
        return view('sessions::login', ['login_logo' => get_setting('login_logo')]);
    }

    /**
     * @legacy-function logout
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     * @legacy-line 81
     */
    public function logout(): \Illuminate\Http\RedirectResponse
    {
        session()->flush();
        return redirect()->route('sessions.login');
    }

    /**
     * @legacy-function passwordreset
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     * @legacy-line 91
     */
    public function passwordReset(?string $token = null)
    {
        // TODO: Full password reset implementation
        return view('sessions::passwordreset');
    }
}
