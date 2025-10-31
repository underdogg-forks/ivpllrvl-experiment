<?php

/**
 * Additional mailer helper functions.
 * 
 * These complex functions still use CodeIgniter dependencies and are kept here
 * until they can be migrated to Laravel's mail system.
 * All simple functions have been migrated to MailerHelper class.
 */

/**
 * Send an invoice via email.
 *
 * @param        $invoice_id
 * @param        $invoice_template
 * @param        $from
 * @param        $to
 * @param        $subject
 * @param string $body
 *
 * @return bool
 */
if ( ! function_exists('email_invoice')) {
    function email_invoice(
    string $invoice_id,
    $invoice_template,
    array $from,
    $to,
    $subject,
    $body,
    $cc = null,
    $bcc = null,
    $attachments = null
) {
    $db_invoice = \Modules\Invoices\Models\Invoice::where('invoice_id', $invoice_id)->first();

    if ($db_invoice->sumex_id == null) {
        $invoice = generate_invoice_pdf($invoice_id, false, $invoice_template);
    } else {
        $invoice = generate_invoice_sumex($invoice_id, false, $invoice_template, true);
    }

    // Need Specific eInvoice filename?
    if ( ! empty($_SERVER['CIIname'])) {
        // Use $options['CIIname' => '{{{tags}}}'] in your config (helpers/XMLconfigs)
        // Or set $_SERVER['CIIname'] in your generator (libraries/XMLtemplates)
        $_SERVER['CIIname'] = parse_template($db_invoice, $_SERVER['CIIname']);
    }

    $message = parse_template($db_invoice, $body);
    $subject = parse_template($db_invoice, $subject);
    $cc      = parse_template($db_invoice, $cc);
    $bcc     = parse_template($db_invoice, $bcc);
    $from    = [parse_template($db_invoice, $from[0]), parse_template($db_invoice, $from[1])];

    $errors = [];
    if ( ! validate_email_address($to)) {
        $errors[] = 'to_email';
    }

    if ( ! validate_email_address($from[0])) {
        $errors[] = 'from_email';
    }

    if ($cc && ! validate_email_address($cc)) {
        $errors[] = 'cc_email';
    }

    if ($bcc && ! validate_email_address($bcc)) {
        $errors[] = 'bcc_email';
    }

    check_mail_errors($errors, 'mailer/invoice/' . $invoice_id);

    $message = (empty($message) ? ' ' : $message);

    return phpmail_send($from, $to, $subject, $message, $invoice, $cc, $bcc, $attachments);
    }
}

/**
 * Send a quote via email.
 *
 * @param        $quote_id
 * @param        $quote_template
 * @param        $from
 * @param        $to
 * @param        $subject
 * @param string $body
 *
 * @return bool
 */
if ( ! function_exists('email_quote')) {
    function email_quote(
        string $quote_id,
        $quote_template,
        array $from,
        $to,
        $subject,
        $body,
        $cc = null,
        $bcc = null,
    $attachments = null
) {
    $quote = generate_quote_pdf($quote_id, false, $quote_template);

    $db_quote = \Modules\Quotes\Models\Quote::where('quote_id', $quote_id)->first();

    $message = parse_template($db_quote, $body);
    $subject = parse_template($db_quote, $subject);
    $cc      = parse_template($db_quote, $cc);
    $bcc     = parse_template($db_quote, $bcc);
    $from    = [parse_template($db_quote, $from[0]), parse_template($db_quote, $from[1])];

    $errors = [];
    if ( ! validate_email_address($to)) {
        $errors[] = 'to_email';
    }

    if ( ! validate_email_address($from[0])) {
        $errors[] = 'from_email';
    }

    if ($cc && ! validate_email_address($cc)) {
        $errors[] = 'cc_email';
    }

    if ($bcc && ! validate_email_address($bcc)) {
        $errors[] = 'bcc_email';
    }

    check_mail_errors($errors, 'mailer/quote/' . $quote_id);

    $message = (empty($message) ? ' ' : $message);

    return phpmail_send($from, $to, $subject, $message, $quote, $cc, $bcc, $attachments);
    }
}

// Note: email_quote_status, validate_email_address, and check_mail_errors
// are now defined in MailerHelper class and wrapped in bc_helper.php.
// They have been removed from this file to avoid conflicts.

