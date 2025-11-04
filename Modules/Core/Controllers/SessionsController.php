<?php

namespace Modules\Core\Controllers;

use Illuminate\Support\Facades\DB;
use App\Helpers\MailerHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Modules\Sessions\Controllers\DateTime;

use function Modules\Sessions\Controllers\phpmail_send;

use Modules\Sessions\Controllers\SessionsService;

use function Modules\Sessions\Controllers\site_url;

use Modules\Sessions\Controllers\UsersService;

/**
 * SessionsController
 *
 * Handles user authentication, login, logout, and password reset functionality
 *
 * @legacy-file application/modules/sessions/controllers/Sessions.php
 */
class SessionsController
{    /**
     * Initialize the SessionsController with dependency injection.
     *
     * @param SessionsService $sessionsService
     * @param UsersService $usersService
     */
    public function __construct(
        protected SessionsService $sessionsService,
        protected UsersService $usersService
    ) {
    }

    /**
     * Redirect to the login page.
     *
     * @return void
     *
     * @legacy-function index
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     */
    public function index()
    {
        redirect()->route('sessions/login');
    }

    /**
     * Handle display and processing of the login form.
     *
     * Processes submitted credentials, sets flash messages for errors, redirects
     * on successful authentication according to user type, and returns the login
     * view when rendering the form.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View a redirect response after form processing or the login view when displaying the form
     *
     * @legacy-function login
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     */
    public function login(Request $request)
    {
        $view_data = ['login_logo' => get_setting('login_logo')];
        if (request()->input('btn_login')) {
            DB::where('user_email', request()->input('email'));
            $query = DB::get('ip_users');
            $user  = $query->row();
            // Check if the user exists
            if (empty($user)) {
                session()->flash('alert_error', trans('loginalert_user_not_found'));
                redirect()->route('sessions/login');
            } elseif ($user->user_active == 0) {
                // Check if the user is marked as active (not implemented: Todo?)
                session()->flash('alert_error', trans('loginalert_user_inactive'));
                redirect()->route('sessions/login');
            } elseif ($this->authenticate(request()->input('email'), request()->input('password'))) {
                if (session('user_type') == 1) {
                    redirect()->route('dashboard');
                } elseif (session('user_type') == 2) {
                    redirect()->route('guest');
                }
            } else {
                session()->flash('alert_error', trans('loginalert_credentials_incorrect'));
                redirect()->route('sessions/login');
            }
        }

        return view('session_login', $view_data);
    }

    /**
     * Validate user credentials while enforcing login-attempt throttling.
     *
     * Attempts authentication only if the recorded failed attempts for the given
     * email are below the configured threshold; on success the failed-attempt
     * log for the email is cleared, on failure a failed-attempt is recorded.
     *
     * @param string $email_address the user's email address used to identify the account
     * @param string $password      the plaintext password to verify for the account
     *
     * @return bool `true` if authentication succeeds and the failure log is reset, `false` otherwise
     *
     * @legacy-function authenticate
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     */
    public function authenticate($email_address, $password): bool
    {
        //check if user is banned
        $login_log = $this->loginLogCheck($email_address);
        if (empty($login_log) || $login_log->log_count < 10) {
            if ($this->sessionsService->auth($email_address, $password)) {
                $this->loginLogReset($email_address);

                return true;
            }
            //track failed attempt
            $this->loginLogAddfailure($email_address);
        }

        return false;
    }

    /**
     * Log out the current user and redirect to login page.
     *
     * @return void
     *
     * @legacy-function logout
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     */
    public function logout()
    {
        session()->flush();
        redirect()->route('sessions/login');
    }

    /**
     * Handle password reset flows: token verification, new-password submission, and reset-request submission.
     *
     * Processes three distinct actions depending on input:
     * - If a token is provided: validate the token, throttle abuse, locate the user, clear login failures, and render the new-password view.
     * - If the new-password form is submitted: validate input and token, update the user's password, clear the reset token and login failures, and redirect to the login page.
     * - If the password-reset request form is submitted: validate the email, throttle abuse, generate and store a reset token, send the reset email, and redirect to the login page.
     *
     * @param Request $request
     * @param string|null $token the password reset token supplied via the URL, or null when not using a token
     *
     * @return mixed a view response for rendering the appropriate password reset page or a redirect response after processing
     *
     * @legacy-function passwordreset
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     */
    public function passwordreset(Request $request, $token = null)
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
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                //the use of a token counts as a failure
                $this->loginLogAddfailure($token);
            }
            DB::where('user_passwordreset_token', $token);
            $user = DB::get('ip_users');
            $user = $user->row();
            if (empty($user)) {
                // Redirect back to the login screen with an alert
                session()->flash('alert_error', trans('wrong_passwordreset_token'));
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
                session()->flash('alert_error', trans('loginalert_no_password'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            // Check for the reset token
            $user = $this->usersService->getById($user_id);
            if (empty($user)) {
                session()->flash('alert_error', trans('loginalert_user_not_found'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            if (empty($user->user_passwordreset_token) || request()->input('token') !== $user->user_passwordreset_token) {
                session()->flash('alert_error', trans('loginalert_wrong_auth_code'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            // Call the save_change_password() function from users model
            $this->usersService->saveChangePassword($user_id, $new_password);
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
                session()->flash('alert_error', trans('loginalert_user_not_found'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            //prevent brute force attacks by counting password resets
            $login_log_check = $this->loginLogCheck($email);
            if ( ! empty($login_log_check) && $login_log_check->log_count > 10) {
                redirect($_SERVER['HTTP_REFERER']);
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
                $email_message   = return view('emails/passwordreset', ['resetlink' => $email_resetlink], true);
                $email_from      = get_setting('smtp_mail_from');
                if (empty($email_from)) {
                    $email_from = 'system@' . preg_replace('/^[\w]{2,6}:\/\/([\w\d\.\-]+).*$/', '$1', base_url());
                }
                // Mail the invoice with the pre-configured mailer if possible
                if (MailerHelper::mailerConfigured()) {
// TODO: Laravel autoloads helpers - $this->load->helper('mailer/phpmailer');
                    if ( ! phpmail_send($email_from, $email, trans('password_reset'), $email_message)) {
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
                    $this->email->subject(trans('password_reset'));
                    $this->email->message($email_message);
                    // Send the reset email
                    if ( ! $this->email->send()) {
                        $email_failed = true;
                        Log::error($this->email->print_debugger());
                    }
                }
                // Redirect back to the login screen with an alert
                if (isset($email_failed)) {
                    session()->flash('alert_error', trans('password_reset_failed'));
                } else {
                    session()->flash('alert_success', trans('email_successfully_sent'));
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
     * @legacy-function loginLogCheck
     * @legacy-file application/modules/sessions/controllers/Sessions.php
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
     * @legacy-function loginLogAddfailure
     * @legacy-file application/modules/sessions/controllers/Sessions.php
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
     * @legacy-function loginLogReset
     * @legacy-file application/modules/sessions/controllers/Sessions.php
     */
    private function loginLogReset($username)
    {
        DB::delete('ip_login_log', ['login_name' => $username]);
    }
}
