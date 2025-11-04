<?php

namespace Modules\Core\Support;

use Modules\Core\Models\Setting;
use RuntimeException;

/**
 * MailerHelper.
 *
 * Static helper class converted from procedural functions.
 */
class MailerHelper
{
    /**
     * Check if mail sending is configured in the settings.
     *
     * @origin Modules/Core/Helpers/mailer_helper.php
     */
    public static function mailer_configured(): bool
    {
        $emailMethod = Setting::getValue('email_send_method');

        return ($emailMethod == 'phpmail')
            || ($emailMethod == 'sendmail')
            || (($emailMethod == 'smtp') && Setting::getValue('smtp_server_address'));
    }

    /**
     * Send an email if the status of an email changed.
     *
     *
     * @origin Modules/Core/Helpers/mailer_helper.php
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
        throw new RuntimeException('email_quote_status requires migration to Laravel Mail system');
    }

    /**
     * Validate email address syntax
     * $email string can be a single email or a list of emails.
     * The emails list must be comma separated.
     *
     *
     * @origin Modules/Core/Helpers/mailer_helper.php
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
     *
     * @origin Modules/Core/Helpers/mailer_helper.php
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

    /**
     * Send an invoice via email.
     *
     * @origin Modules/Core/Helpers/mailer_helper.php
     * @param string $invoice_id
     * @param mixed $invoice_template
     * @param array $from
     * @param mixed $to
     * @param mixed $subject
     * @param string $body
     * @param mixed $cc
     * @param mixed $bcc
     * @param mixed $attachments
     *
     * @return bool
     */
    public static function email_invoice(
        string $invoice_id,
        $invoice_template,
        array $from,
        $to,
        $subject,
        $body,
        $cc = null,
        $bcc = null,
        $attachments = null
    ): bool {
        $db_invoice = \Modules\Invoices\Models\Invoice::query()->where('invoice_id', $invoice_id)->first();

        if ($db_invoice === null) {
            throw new \RuntimeException("Invoice with ID {$invoice_id} not found");
        }

        if ($db_invoice->sumex_id == null) {
            $invoice = generate_invoice_pdf($invoice_id, false, $invoice_template);
        } else {
            $invoice = generate_invoice_sumex($invoice_id, false, $invoice_template, true);
        }

        // Need Specific eInvoice filename?
        if (! empty($_SERVER['CIIname'])) {
            // Use $options['CIIname' => '{{{tags}}}'] in your config (helpers/XMLconfigs)
            // Or set $_SERVER['CIIname'] in your generator (libraries/XMLtemplates)
            $_SERVER['CIIname'] = parse_template($db_invoice, $_SERVER['CIIname']);
        }

        $message = parse_template($db_invoice, $body);
        $subject = parse_template($db_invoice, $subject);
        $cc = parse_template($db_invoice, $cc);
        $bcc = parse_template($db_invoice, $bcc);
        $from = [parse_template($db_invoice, $from[0]), parse_template($db_invoice, $from[1])];

        $errors = [];
        if (! self::validate_email_address($to)) {
            $errors[] = 'to_email';
        }

        if (! self::validate_email_address($from[0])) {
            $errors[] = 'from_email';
        }

        if ($cc && ! self::validate_email_address($cc)) {
            $errors[] = 'cc_email';
        }

        if ($bcc && ! self::validate_email_address($bcc)) {
            $errors[] = 'bcc_email';
        }

        self::check_mail_errors($errors, 'mailer/invoice/' . $invoice_id);

        $message = (empty($message) ? ' ' : $message);

        // Use the namespaced phpmail_send function
        return \Modules\Sessions\Controllers\phpmail_send($from, $to, $subject, $message, $invoice, $cc, $bcc, $attachments);
    }

    /**
     * Send a quote via email.
     *
     * @origin Modules/Core/Helpers/mailer_helper.php
     * @param string $quote_id
     * @param mixed $quote_template
     * @param array $from
     * @param mixed $to
     * @param mixed $subject
     * @param string $body
     * @param mixed $cc
     * @param mixed $bcc
     * @param mixed $attachments
     *
     * @return bool
     */
    public static function email_quote(
        string $quote_id,
        $quote_template,
        array $from,
        $to,
        $subject,
        $body,
        $cc = null,
        $bcc = null,
        $attachments = null
    ): bool {
        $quote = generate_quote_pdf($quote_id, false, $quote_template);

        $db_quote = \Modules\Quotes\Models\Quote::query()->where('quote_id', $quote_id)->first();

        if ($db_quote === null) {
            throw new \RuntimeException("Quote with ID {$quote_id} not found");
        }

        $message = parse_template($db_quote, $body);
        $subject = parse_template($db_quote, $subject);
        $cc = parse_template($db_quote, $cc);
        $bcc = parse_template($db_quote, $bcc);
        $from = [parse_template($db_quote, $from[0]), parse_template($db_quote, $from[1])];

        $errors = [];
        if (! self::validate_email_address($to)) {
            $errors[] = 'to_email';
        }

        if (! self::validate_email_address($from[0])) {
            $errors[] = 'from_email';
        }

        if ($cc && ! self::validate_email_address($cc)) {
            $errors[] = 'cc_email';
        }

        if ($bcc && ! self::validate_email_address($bcc)) {
            $errors[] = 'bcc_email';
        }

        self::check_mail_errors($errors, 'mailer/quote/' . $quote_id);

        $message = (empty($message) ? ' ' : $message);

        // Use the namespaced phpmail_send function
        return \Modules\Sessions\Controllers\phpmail_send($from, $to, $subject, $message, $quote, $cc, $bcc, $attachments);
    }
}
