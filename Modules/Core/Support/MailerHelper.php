<?php



namespace Modules\Core\Support;

use Modules\Core\Models\Setting;

/**
 * MailerHelper
 *
 * Static helper class converted from procedural functions.
 */
class MailerHelper
{
    /**
     * Check if mail sending is configured in the settings.
     */
    public static function mailer_configured(): bool
    {
        $emailMethod = Setting::getValue('email_send_method');

        return ($emailMethod == 'phpmail') ||
            ($emailMethod == 'sendmail') ||
            (($emailMethod == 'smtp') && Setting::getValue('smtp_server_address'));
    }

    /**
     * Send an email if the status of an email changed.
     *
     * @param        $quote_id
     * @param string $status   string "accepted" or "rejected"
     *
     * @return bool if the email was sent
     *
     * @todo This method requires full migration of Quote model and mail sending system
     */
    public static function email_quote_status(string $quote_id, $status)
    {
        // TODO: Implement using Laravel Mail and Eloquent models
        throw new \RuntimeException('email_quote_status requires migration to Laravel Mail system');
    }

    /**
     * Validate email address syntax
     * $email string can be a single email or a list of emails.
     * The emails list must be comma separated.
     *
     * @param string $email
     *
     * @return bool returns true if all emails are valid otherwise false
     */
    public static function validate_email_address(string $email): bool
    {
        $emails[] = $email;
        if (str_contains($email, ',')) {
            $emails = explode(',', $email);
        }

        foreach ($emails as $emailItem) {
            if ( ! filter_var($emailItem, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param []  $errors
     * @param string $redirect
     *
     * @todo This method requires Laravel session/flash system
     */
    public static function check_mail_errors(array $errors = [], $redirect = ''): void
    {
        if ($errors) {
            foreach ($errors as $i => $e) {
                $errors[$i] = strtr(trans('form_validation_valid_email'), ['{field}' => trans($e)]);
            }

            // TODO: Use Laravel flash messages
            session()->flash('alert_error', implode('<br>', $errors));

            $redirect = empty($redirect) ? (empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER']) : $redirect;
            redirect($redirect);
        }
    }

}
