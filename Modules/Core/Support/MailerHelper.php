<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Services\LegacyBridge;

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
        $bridge = LegacyBridge::getInstance();
    
        return ($bridge->settings()->setting('email_send_method') == 'phpmail') ||
            ($bridge->settings()->setting('email_send_method') == 'sendmail') ||
            (($bridge->settings()->setting('email_send_method') == 'smtp') && ($bridge->settings()->setting('smtp_server_address')));
    }

    /**
     * Send an email if the status of an email changed.
     *
     * @param        $quote_id
     * @param string $status   string "accepted" or "rejected"
     *
     * @return bool if the email was sent
     */
    public static function email_quote_status(string $quote_id, $status)
    {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);
    
        if ( ! mailer_configured()) {
            return false;
        }
    
        $bridge = LegacyBridge::getInstance();
        $bridge->getRawInstance()->load->helper('mailer/phpmailer');
    
        $quote    = $CI->mdl_quotes->where('ip_quotes.quote_id', $quote_id)->get()->row();
        $index    = env('REMOVE_INDEXPHP', true) ? '' : 'index.php';
        $base_url = base_url('/' . $index . '/quotes/view/' . $quote_id);
    
        $user_email = $quote->user_email;
        $subject    = sprintf(
            trans('quote_status_email_subject'),
            $quote->client_name,
            mb_strtolower(lang($status)),
            $quote->quote_number
        );
        $body = sprintf(
            nl2br(trans('quote_status_email_body')),
            $quote->client_name,
            mb_strtolower(lang($status)),
            $quote->quote_number,
            '<a href="' . $base_url . '">' . $base_url . '</a>'
        );
    
        return phpmail_send($user_email, $user_email, $subject, $body);
    }

    /**
     * Validate email address syntax
     * $email string can be a single email or a list of emails.
     * The emails list must be comma separated.
     *
     * @param string $email
     *
     * @return bool returs true if all emails are valid otherwise false
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
     */
    public static function check_mail_errors(array $errors = [], $redirect = ''): void
    {
        if ($errors) {
            $bridge = LegacyBridge::getInstance();
            foreach ($errors as $i => $e) {
                $errors[$i] = strtr(trans('form_validation_valid_email'), ['{field}' => trans($e)]);
            }
    
            $bridge->session()->set_flashdata('alert_error', implode('<br>', $errors));
            $redirect = empty($redirect) ? (empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER']) : $redirect;
            redirect($redirect);
        }
    }

}
