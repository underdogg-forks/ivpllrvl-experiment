<?php

namespace Modules\Core\Controllers;

use Illuminate\Support\Facades\DB;

use AllowDynamicProperties;
use App\Http\Controllers\Controller as MXController;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\SetupService;

use const Modules\Setup\Controllers\APPPATH;

use function Modules\Setup\Controllers\env_bool;

use const Modules\Setup\Controllers\IPCONFIG_FILE;
use const Modules\Setup\Controllers\LOGS_FOLDER;

use function Modules\Setup\Controllers\show_error;

use const Modules\Setup\Controllers\UPLOADS_ARCHIVE_FOLDER;
use const Modules\Setup\Controllers\UPLOADS_CFILES_FOLDER;
use const Modules\Setup\Controllers\UPLOADS_FOLDER;
use const Modules\Setup\Controllers\UPLOADS_TEMP_FOLDER;
use const Modules\Setup\Controllers\UPLOADS_TEMP_MPDF_FOLDER;

use Modules\Setup\Controllers\UsersService;
use Modules\Setup\Controllers\VersionsService;

use function Modules\Setup\Controllers\write_file;

#[AllowDynamicProperties]
class SetupController extends BaseController
{
    public $errors = 0;

    /**
     * Enforces setup availability, loads required framework resources, and initializes localization.
     *
     * Aborts with HTTP 403 if the DISABLE_SETUP environment flag is true. Loads the session library,
     * necessary helpers and the layout module, ensures the session key `ip_lang` exists (defaults to
     * `'en'`) and applies that language, then loads the `ip` language file.
     */
    public function __construct()
    {
        if (env_bool('DISABLE_SETUP', false)) {
            show_error('The setup is disabled.', 403);
        }
        parent::__construct();
// TODO: Use Laravel services/facades - $this->load->library('session');
// TODO: Laravel autoloads helpers - $this->load->helper('file');
// TODO: Laravel autoloads helpers - $this->load->helper('directory');
// TODO: Laravel autoloads helpers - $this->load->helper('url');
// TODO: Laravel autoloads helpers - $this->load->helper('lang');
// TODO: Laravel autoloads helpers - $this->load->helper('trans');
// TODO: Laravel autoloads helpers - $this->load->helper('settings');
// TODO: Laravel autoloads helpers - $this->load->helper('echo');
        // For get_setting() in echo_helper
// TODO: Modules handled differently in Laravel - $this->load->module('layout');
        if ( ! session('ip_lang')) {
            session(['ip_lang', 'en');
        } else {
            set_language(session('ip_lang'));
        }
        $this->lang->load('ip', session('ip_lang'));
    }

    /**
     * @originalName index
     *
     * @originalFile SetupController.php
     */
    public function index(): void
    {
        redirect()->route('setup/lang');
    }

    /**
     * @originalName lang
     *
     * @originalFile SetupController.php
     */
    public function language(): void
    {
        if (request()->input('btn_continue')) {
            session(['ip_lang', request()->input('ip_lang'));
            session(['install_step', 'prerequisites');
            redirect()->route('setup/prerequisites');
        }
        // Reset the session cache
        session()->forget('install_step');
        session()->forget('is_upgrade');
        // GetController all languages
        $languages = get_available_languages();
        $this->layout->set('languages', $languages);
        $this->layout->buffer('content', 'setup/lang');
        $this->layout->render('setup');
    }

    /**
     * @originalName prerequisites
     *
     * @originalFile SetupController.php
     */
    public function prerequisites(): void
    {
        if (session('install_step') != 'prerequisites') {
            redirect()->route('setup/lang');
        }
        if (request()->input('btn_continue')) {
            session(['install_step', 'configure_database');
            redirect()->route('setup/configure_database');
        }
        $this->layout->set(['basics' => $this->checkBasics(), 'writables' => $this->checkWritables(), 'errors' => $this->errors]);
        $this->layout->buffer('content', 'setup/prerequisites');
        $this->layout->render('setup');
    }

    /**
     * @originalName configureDatabase
     *
     * @originalFile SetupController.php
     */
    public function configureDatabase(): void
    {
        if (session('install_step') != 'configure_database') {
            redirect()->route('setup/prerequisites');
        }
        if (request()->input('btn_continue')) {
            $this->loadCiDatabase();
            // This might be an upgrade - check if it is
            if ( ! DB::table_exists('ip_versions')) {
                // This appears to be an install
                session(['install_step', 'install_tables');
                redirect()->route('setup/install_tables');
            } else {
                // This appears to be an upgrade
                session(['is_upgrade', true);
                session(['install_step', 'upgrade_tables');
                redirect()->route('setup/upgrade_tables');
            }
        }
        if (request()->input('db_hostname')) {
            // Write a new database configuration to the ipconfig.php file
            $this->writeDatabaseConfig(request()->input('db_hostname'), request()->input('db_username'), request()->input('db_password'), request()->input('db_database'), request()->input('db_port'));
        }
        // Check if the set credentials are correct
        $check_database = $this->checkDatabase();
        $this->layout->set('database', $check_database);
        $this->layout->set('errors', $this->errors);
        $this->layout->buffer('content', 'setup/configure_database');
        $this->layout->render('setup');
    }

    /**
     * @originalName installTables
     *
     * @originalFile SetupController.php
     */
    public function installTables(): void
    {
        if (session('install_step') != 'install_tables') {
            redirect()->route('setup/prerequisites');
        }
        if (request()->input('btn_continue')) {
            session(['install_step', 'upgrade_tables');
            redirect()->route('setup/upgrade_tables');
        }
        $this->loadCiDatabase();
        $this->layout->set(['success' => (new SetupService())->installTables(), 'errors' => (new SetupService())->errors]);
        $this->layout->buffer('content', 'setup/install_tables');
        $this->layout->render('setup');
    }

    /**
     * Handle the database upgrade step of the setup flow.
     *
     * Validates the current install step and redirects to prerequisites if not allowed.
     * On form submission advances the install flow to either the create-user or calculation-info step and redirects.
     * Ensures the database is loaded and an encryption key exists, runs table upgrade operations via the setup service,
     * and renders the upgrade view with the operation results and any errors.
     */
    public function upgradeTables(): void
    {
        if (session('install_step') != 'upgrade_tables') {
            redirect()->route('setup/prerequisites');
        }
        if (request()->input('btn_continue')) {
            if ( ! session('is_upgrade')) {
                session(['install_step', 'create_user');
                redirect()->route('setup/create_user');
            } else {
                session(['install_step', 'calculation_info');
                redirect()->route('setup/calculation_info');
            }
        }
        $this->loadCiDatabase();
        // Set a new encryption key if none exists
        if (env('ENCRYPTION_KEY') === null || env('ENCRYPTION_KEY') === '') {
            $this->setEncryptionKey();
        }
        $this->layout->set(['success' => (new SetupService())->upgradeTables(), 'errors' => (new SetupService())->errors]);
        $this->layout->buffer('content', 'setup/upgrade_tables');
        $this->layout->render('setup');
    }

    /**
     * Handle the "create user" setup step and create the initial admin user when valid.
     *
     * Validates submitted user data; if validation succeeds, creates a user with `user_type` = 1,
     * advances the `install_step` session value to `calculation_info`, and redirects to the calculation info step.
     * If not submitted or validation fails, prepares country and language data for the layout and renders the user creation form.
     */
    public function createUser(): void
    {
        if (session('install_step') != 'create_user') {
            redirect()->route('setup/prerequisites');
        }
        $this->loadCiDatabase();
// TODO: Laravel autoloads helpers - $this->load->helper('country');
        if ((new UsersService())->runValidation()) {
            $db_array              = (new UsersService())->dbArray();
            $db_array['user_type'] = 1;
            (new UsersService())->save(null, $db_array);
            session(['install_step', 'calculation_info');
            redirect()->route('setup/calculation_info');
        }
        $this->layout->set(['countries' => get_country_list(trans('cldr')), 'languages' => get_available_languages()]);
        $this->layout->buffer('content', 'setup/create_user');
        $this->layout->render('setup');
    }

    /**
     * @originalName calculationInfo
     *
     * @originalFile SetupController.php
     */
    public function calculationInfo(): void
    {
        if (session('install_step') != 'calculation_info') {
            redirect()->route('setup/prerequisites');
        }
        if (request()->input('btn_continue')) {
            session(['install_step', 'complete');
            redirect()->route('setup/complete');
        } elseif (request()->input('btn_agree')) {
            $this->writeCalculationConfig();
            session(['install_step', 'complete');
            redirect()->route('setup/complete');
        }
        $checkCalculation = $this->checkCalculationConfig();
        if ($checkCalculation['needs_config'] === false) {
            session(['install_step', 'complete');
            redirect()->route('setup/complete');
        }
        $this->layout->set('calculation_check', $checkCalculation);
        $this->layout->buffer('content', 'setup/calculation_info');
        $this->layout->render('setup');
    }

    /**
     * @originalName complete
     *
     * @originalFile SetupController.php
     */
    public function complete(): void
    {
        if (session('install_step') != 'complete') {
            redirect()->route('setup/prerequisites');
        }
        $this->loadCiDatabase();
        $users = DB::query('SELECT * FROM ip_users');
        if ($users->numRows() === 0) {
            Log::error('there was already one or more users in the database');
            session()->flash('alert_error', 'Something went wrong, check the log file for errors');
            session(['install_step', 'create_user');
            redirect()->route('setup/create_user');
        }
        // Additional tasks after setup is completed
        $this->postSetupTasks();
        // Check if this is an update or the first install
        // First get all version entries from the database and format them
        $versions = DB::query('SELECT * FROM ip_versions');
        if ($versions->numRows() > 0) {
            foreach ($versions->result() as $row) {
                $data[] = $row;
            }
        }
        // Then check if the first version entry is less than 30 minutes old
        // If yes we assume that the user ran the setup a few minutes ago
        $update = $data[0]->version_date_applied < time() - 1800;
        $this->layout->set('update', $update);
        $this->layout->buffer('content', 'setup/complete');
        $this->layout->render('setup');
        session()->flush();
    }

    /**
     * @originalName checkBasics
     *
     * @originalFile SetupController.php
     */
    private function checkBasics(): array
    {
        $checks        = [];
        $php_required  = '5.6';
        $php_installed = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
        if ($php_installed < $php_required) {
            $this->errors += 1;
            $checks[] = ['message' => sprintf(trans('php_version_fail'), $php_installed, $php_required), 'success' => 0];
        } else {
            $checks[] = ['message' => trans('php_version_success'), 'success' => 1];
        }
        if ( ! ini_get('date.timezone')) {
            $checks[] = ['message' => sprintf(trans('php_timezone_fail'), date_default_timezone_get()), 'success' => 1, 'warning' => 1];
        } else {
            $checks[] = ['message' => trans('php_timezone_success'), 'success' => 1];
        }

        return $checks;
    }

    /**
     * @originalName checkWritables
     *
     * @originalFile SetupController.php
     */
    private function checkWritables(): array
    {
        $checks    = [];
        $writables = [IPCONFIG_FILE, UPLOADS_FOLDER, UPLOADS_ARCHIVE_FOLDER, UPLOADS_CFILES_FOLDER, UPLOADS_TEMP_FOLDER, UPLOADS_TEMP_MPDF_FOLDER, LOGS_FOLDER];
        foreach ($writables as $writable) {
            $writable_check = ['message' => '<code>' . str_replace(FCPATH, '', $writable) . '</code>&nbsp;', 'success' => 1];
            if ( ! is_writable($writable)) {
                $writable_check['message'] .= trans('is_not_writable');
                $writable_check['success'] .= 0;
                $this->errors += 1;
            } else {
                $writable_check['message'] .= trans('is_writable');
            }
            $checks[] = $writable_check;
        }

        return $checks;
    }

    /**
     * @originalName loadCiDatabase
     *
     * @originalFile SetupController.php
     */
    private function loadCiDatabase()
    {
// TODO: Database always available in Laravel - $this->load->database();
    }

    /**
     * @originalName writeDatabaseConfig
     *
     * @originalFile SetupController.php
     */
    private function writeDatabaseConfig(string $hostname, string $username, string $password, string $database, $port = 3306)
    {
        $config = file_get_contents(IPCONFIG_FILE);
        $config = preg_replace('/DB_HOSTNAME=(.*)?/', "DB_HOSTNAME='" . $hostname . "'", $config);
        $config = preg_replace('/DB_USERNAME=(.*)?/', "DB_USERNAME='" . $username . "'", $config);
        $config = preg_replace('/DB_PASSWORD=(.*)?/', "DB_PASSWORD='" . $password . "'", $config);
        $config = preg_replace('/DB_DATABASE=(.*)?/', "DB_DATABASE='" . $database . "'", $config);
        $config = preg_replace('/DB_PORT=(.*)?/', 'DB_PORT=' . $port, $config);
        write_file(IPCONFIG_FILE, $config);
    }

    /**
     * @originalName checkDatabase
     *
     * @originalFile SetupController.php
     */
    private function checkDatabase(): array
    {
        // Reload the ipconfig.php file
        global $dotenv;
        $dotenv->load();
        // Load the database config and configure it to test the connection
        include APPPATH . 'config/database.php';
        $db             = $db['default'];
        $db['autoinit'] = false;
        $db['db_debug'] = false;
        // Check if there is some configuration set
        if (empty($db['hostname'])) {
            $this->errors += 1;

            return ['message' => trans('setup_database_message'), 'success' => false];
        }
        // Initialize the database connection, turn off automatic error reporting to display connection issues manually
        error_reporting(0);
// TODO: Database always available in Laravel - $db_object = $this->load->database($db, true);
        // Try to initialize the database connection
        $can_connect = (bool) $db_object->conn_id;
        if ( ! $can_connect) {
            $this->errors += 1;

            return ['message' => trans('setup_db_cannot_connect'), 'success' => false];
        }

        return ['message' => trans('database_properly_configured'), 'success' => true];
    }

    /**
     * @originalName setEncryptionKey
     *
     * @originalFile SetupController.php
     */
    private function setEncryptionKey()
    {
        $length = env('ENCRYPTION_CIPHER') == 'AES-256' ? 32 : 16;
        if (function_exists('random_bytes')) {
            $key = 'base64:' . base64_encode(random_bytes($length));
        } else {
            $key = 'base64:' . base64_encode(openssl_random_pseudo_bytes($length));
        }
        $config = file_get_contents(IPCONFIG_FILE);
        $config = preg_replace('/ENCRYPTION_KEY=(.*)?/', 'ENCRYPTION_KEY=' . $key, $config);
        write_file(IPCONFIG_FILE, $config);
    }

    /**
     * Mark the application's setup as completed in the IPCONFIG_FILE.
     *
     * Updates the SETUP_COMPLETED entry in the configuration file to `true`.
     */
    private function postSetupTasks()
    {
        // Set SETUP_COMPLETED to true
        $config = file_get_contents(IPCONFIG_FILE);
        $config = preg_replace('/SETUP_COMPLETED=(.*)?/', 'SETUP_COMPLETED=true', $config);
        write_file(IPCONFIG_FILE, $config);
    }

    /**
     * Determines whether the legacy calculation setting requires explicit configuration for the installed version.
     *
     * @return array An associative array describing configuration needs:
     *               - `needs_config` (bool): `true` if manual configuration is required, `false` otherwise.
     *               - `current_value` (string): the current `LEGACY_CALCULATION` value (`'not_set'`, `'true'`, or `'false'`).
     *               - `recommended` (string|null): the recommended value when configuration is required (`'false'`), or `null` when not applicable.
     */
    private function checkCalculationConfig(): array
    {
        $this->loadCiDatabase();
        $current_version = (new VersionsService())->getCurrentVersion();
        if (version_compare($current_version, '1.6.3', '>=')) {
            // Reload the ipconfig.php
            global $dotenv;
            $dotenv->load();
            $legacy_calc = env('LEGACY_CALCULATION');
            if ($legacy_calc === null) {
                return ['needs_config' => true, 'current_value' => 'not_set', 'recommended' => 'false'];
            }
            if ($legacy_calc === 'true' || $legacy_calc === true) {
                return ['needs_config' => true, 'current_value' => 'true', 'recommended' => 'false'];
            }

            return ['needs_config' => false, 'current_value' => 'false'];
        }

        return ['needs_config' => false];
    }

    /**
     * Append the LEGACY_CALCULATION setting to the IPCONFIG file.
     *
     * Reads the contents of IPCONFIG_FILE, appends a newline and the line
     * `LEGACY_CALCULATION=false`, and writes the updated content back to the file.
     */
    private function writeCalculationConfig()
    {
        $config = file_get_contents(IPCONFIG_FILE);
        $config .= PHP_EOL . 'LEGACY_CALCULATION=false';
        write_file(IPCONFIG_FILE, $config);
    }
}
