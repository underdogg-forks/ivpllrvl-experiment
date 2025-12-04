<?php

namespace Modules\Core\Controllers;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Core\Services\SessionsService;
use Modules\Core\Services\UserService;
use Modules\Core\Support\SettingsHelper;
use Modules\Core\Support\TranslationHelper;

/**
 * SessionsController.
 *
 * Handles user authentication, login, logout, and password reset functionality
 *
 * @legacy-file application/modules/sessions/controllers/Sessions.php
 */
class SessionsController
{
    /**
     * Initialize the SessionsController with dependency injection.
     *
     * @param SessionsService $sessionsService
     * @param UserService     $userService
     */
    public function __construct(
        protected SessionsService $sessionsService,
        protected UserService $userService
    ) {}

    /**
     * Redirect to the login page.
     *
     * @return void
     *
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     *
     * @legacy-function index
     */
    public function index()
    {
        redirect()->route('sessions.login');
    }

    /**
     * Display the login form.
     *
     * @return \Illuminate\View\View
     *
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     *
     * @legacy-function login
     */
    public function login(): \Illuminate\View\View
    {
        $view_data = ['login_logo' => SettingsHelper::getSetting('login_logo')];

        return view('core::users.session_login', $view_data);
    }

    /**
     * Handle an authentication attempt with rate limiting.
     *
     * Validates credentials, enforces rate limiting similar to Laravel's Auth::attempt,
     * verifies password using InvoicePlane's MD5/crypt method, and regenerates session
     * on success. Follows Laravel authentication patterns while preserving InvoicePlane's
     * password hashing mechanism.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     *
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     *
     * @legacy-function authenticate
     */
    public function authenticate(Request $request): RedirectResponse
    {
        // Validate input
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Ensure the request is not rate limited
        $this->ensureIsNotRateLimited($request);

        // Attempt to authenticate using the SessionsService
        if ($this->sessionsService->auth($request->input('email'), $request->input('password'))) {
            // Clear the rate limiter on successful login
            RateLimiter::clear($this->throttleKey($request));

            // Regenerate the session to prevent fixation attacks
            // Note: Laravel's regenerate() preserves session data, only changes the session ID
            // This is the correct behavior - session data set in auth() is maintained
            $request->session()->regenerate();

            // Redirect to the appropriate dashboard based on user type
            if (session('user_type') === 1) {
                return redirect()->intended(route('dashboard.index'));
            } elseif (session('user_type') === 2) {
                return redirect()->intended(route('guest.index'));
            }

            // Default redirect to dashboard
            return redirect()->intended(route('dashboard.index'));
        }

        // Authentication failed - increment rate limiter
        RateLimiter::hit($this->throttleKey($request));

        // Throw validation exception with error message
        throw ValidationException::withMessages([
            'email' => trans('loginalert_credentials_incorrect'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @param Request $request
     *
     * @return void
     *
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }

    /**
     * Log out the current user and redirect to login page.
     *
     * Follows Laravel's logout pattern: flush session data, invalidate session,
     * and regenerate CSRF token for security.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     *
     * @legacy-function logout
     */
    public function logout(Request $request): RedirectResponse
    {
        // Flush all session data
        $request->session()->flush();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('sessions.login');
    }

    /**
     * Handle password reset flows: token verification, new-password submission, and reset-request submission.
     *
     * Processes three distinct actions depending on input:
     * - If a token is provided: validate the token, throttle abuse, locate the user, clear login failures, and render the new-password view.
     * - If the new-password form is submitted: validate input and token, update the user's password, clear the reset token and login failures, and redirect to the login page.
     * - If the password-reset request form is submitted: validate the email, throttle abuse, generate and store a reset token, send the reset email, and redirect to the login page.
     *
     * @param Request     $request
     * @param string|null $token   the password reset token supplied via the URL, or null when not using a token
     *
     * @return mixed a view response for rendering the appropriate password reset page or a redirect response after processing
     *
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     *
     * @legacy-function passwordreset
     */
    public function passwordreset(Request $request, $token = null): mixed
    {
        // Check if a token was provided
        if ($token) {
            if (preg_match('/[^[:alnum:]\-_]/', $token)) {
                Log::error('Incoming token is not alphanumeric ' . $token);
                redirect()->route('/');
            }
            //prevent brute force attacks by counting times a token is used
            $login_log_check = $this->loginLogCheck($token);
            if ( ! empty($login_log_check) && $login_log_check->log_count > 10) {
                redirect()->back();
            } else {
                //the use of a token counts as a failure
                $this->loginLogAddfailure($token);
            }
            DB::where('user_passwordreset_token', $token);
            $user = DB::get('ip_users');
            $user = $user->row();
            if (empty($user)) {
                // Redirect back to the login screen with an alert
                session()->flash('alert_error', TranslationHelper::trans('wrong_passwordreset_token'));
                redirect()->route('sessions/passwordreset');
            } else {
                //if token is valid, delete the failure attempt from
                //the login_log table
                $this->loginLogReset($token);
            }
            $formdata = ['token' => $token, 'user_id' => $user->user_id];

            return view('session_new_password', $formdata);
        }
        // Check if the form for a new password was used
        if (request()->input('btn_new_password')) {
            $new_password = request()->input('new_password', true);
            $user_id      = request()->input('user_id', true);
            if (empty($user_id) || empty($new_password)) {
                session()->flash('alert_error', TranslationHelper::trans('loginalert_no_password'));
                redirect()->back();
            }
            // Check for the reset token
            $user = $this->userService->getById($user_id);
            if (empty($user)) {
                session()->flash('alert_error', TranslationHelper::trans('loginalert_user_not_found'));
                redirect()->back();
            }
            if (empty($user->user_passwordreset_token) || request()->input('token') !== $user->user_passwordreset_token) {
                session()->flash('alert_error', TranslationHelper::trans('loginalert_wrong_auth_code'));
                redirect()->back();
            }
            // Call the save_change_password() function from users model
            $this->userService->saveChangePassword($user_id, $new_password);
            // Update the user and set him active again
            $db_array = ['user_passwordreset_token' => ''];
            //delete failed attempts from login_log table
            $user = DB::where('user_id', $user_id)->get('ip_users')->row();
            $this->loginLogReset($user->user_email);
            DB::where('user_id', $user_id);
            DB::update('ip_users', $db_array);
            // Redirect back to the login form
            redirect()->route('sessions/login');
        }
        // Check if the password reset form was used
        if (request()->input('btn_reset', true)) {
            $email = request()->input('email', true);
            if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::error('Incoming email is not a valid email address in passwordreset ' . $email);
                redirect()->route('/');
            }
            if (empty($email)) {
                session()->flash('alert_error', TranslationHelper::trans('loginalert_user_not_found'));
                redirect()->back();
            }
            //prevent brute force attacks by counting password resets
            $login_log_check = $this->loginLogCheck($email);
            if ( ! empty($login_log_check) && $login_log_check->log_count > 10) {
                redirect()->back();
            } else {
                //a password recovery attempt counts as failed login
                $this->loginLogAddfailure($email);
            }
            // Test if a user with this email exists
            if ($recovery_result = DB::where('user_email', $email)) {
                // Create a passwordreset token.
                $email = request()->input('email', true);
                if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Log::error('Incoming email is not a valid email address in passwordreset ' . $email);
                    redirect()->route('/');
                }
                //use salt to prevent predictability of the reset token (CVE-2021-29023)
                // TODO: Use Laravel services/facades - $this->load->library('crypt');
                $token = md5(time() . $email . $this->crypt->salt());
                // Save the token to the database and set the user to inactive
                $db_array = ['user_passwordreset_token' => $token];
                DB::where('user_email', $email);
                DB::update('ip_users', $db_array);
                // Send the email with reset link
                // Prepare some variables for the email
                $email_resetlink = site_url('sessions/passwordreset/' . $token);
                $email_message   = view('emails/passwordreset', ['resetlink' => $email_resetlink], true);
                $email_from      = SettingsHelper::getSetting('smtp_mail_from');
                if (empty($email_from)) {
                    $email_from = 'system@' . preg_replace('/^[\w]{2,6}:\/\/([\w.\-]+).*$/', '$1', base_url());
                }
                // Mail the invoice with the pre-configured mailer if possible
                if (MailerHelper::mailerConfigured()) {
                    // TODO: Laravel autoloads helpers - $this->load->helper('mailer/phpmailer');
                    if ( ! phpmail_send($email_from, $email, TranslationHelper::trans('password_reset'), $email_message)) {
                        $email_failed = true;
                    }
                } else {
                    // TODO: Use Laravel services/facades - $this->load->library('email');
                    // Set email configuration
                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);
                    // Set the email params
                    $this->email->from($email_from);
                    $this->email->to($email);
                    $this->email->subject(TranslationHelper::trans('password_reset'));
                    $this->email->message($email_message);
                    // Send the reset email
                    if ( ! $this->email->send()) {
                        $email_failed = true;
                        Log::error($this->email->print_debugger());
                    }
                }
                // Redirect back to the login screen with an alert
                if (isset($email_failed)) {
                    session()->flash('alert_error', TranslationHelper::trans('password_reset_failed'));
                } else {
                    session()->flash('alert_success', TranslationHelper::trans('email_successfully_sent'));
                }
                redirect()->route('sessions/login');
            }
        }

        return view('session_passwordreset');
    }

    /**
     * Check login attempt log for a username/email and determine if account is locked.
     *
     * @param string $username
     *
     * @return mixed login log record or null
     *
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     *
     * @legacy-function loginLogCheck
     */
    private function loginLogCheck($username)
    {
        $login_log_query = DB::where('login_name', $username)->get('ip_login_log')->row();
        if ( ! empty($login_log_query) && $login_log_query->log_count > 10) {
            $current_time = new DateTime();
            $interval     = $current_time->diff(new DateTime($login_log_query->log_create_timestamp));
            //if the last recorded failed attempt is over 12 hours ago, then unlock the account
            //the fails are only counted up to 11, this means that the account is also unlocked
            //if the last failed 11th login attempt is over 12 hours ago.
            if ($interval->h > 12) {
                $this->loginLogReset($username);

                return;
            }
        }

        return $login_log_query;
    }

    /**
     * Record a failed login attempt for the given username.
     *
     * @param string $username
     *
     * @return void
     *
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     *
     * @legacy-function loginLogAddfailure
     */
    private function loginLogAddfailure($username)
    {
        if (empty($login_log_check = $this->loginLogCheck($username))) {
            //create the log
            DB::insert('ip_login_log', ['login_name' => $username, 'log_count' => 1, 'log_create_timestamp' => date('c')]);
        } else {
            //update the log
            DB::set(['log_count' => $login_log_check->log_count + 1, 'log_create_timestamp' => date('c')])->where('login_name', $username)->update('ip_login_log');
        }
    }

    /**
     * Reset the login failure log for the given username.
     *
     * @param string $username
     *
     * @return void
     *
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     *
     * @legacy-function loginLogReset
     */
    private function loginLogReset($username)
    {
        DB::delete('ip_login_log', ['login_name' => $username]);
    }
}
