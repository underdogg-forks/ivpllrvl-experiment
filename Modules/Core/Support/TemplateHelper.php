<?php

namespace Modules\Core\Support;

/**
 * TemplateHelper.
 *
 * Static helper class converted from procedural functions.
 */
class TemplateHelper
{
    /**
     * Parse a template by predefined template tags.
     *
     *
     * @origin Modules/Core/Helpers/template_helper.php
     * @param $object
     * @param $body
     * @param $model_id
     *
     * @return mixed
     */
    public static function parse_template($object, $body)
    {
        if (preg_match_all('/{{{([^{|}]*)}}}/', $body, $template_vars)) {
            foreach ($template_vars[1] as $var) {
                switch ($var) {
                    case 'invoice_guest_url':
                        $replace = site_url('guest/view/invoice/' . $object->invoice_url_key);
                        break;
                    case 'invoice_date_due':
                        $replace = date_from_mysql($object->invoice_date_due, true);
                        break;
                    case 'invoice_date_created':
                        $replace = date_from_mysql($object->invoice_date_created, true);
                        break;
                    case 'invoice_item_subtotal':
                        $replace = format_currency($object->invoice_item_subtotal);
                        break;
                    case 'invoice_item_tax_total':
                        $replace = format_currency($object->invoice_item_tax_total);
                        break;
                    case 'invoice_total':
                        $replace = format_currency($object->invoice_total);
                        break;
                    case 'invoice_paid':
                        $replace = format_currency($object->invoice_paid);
                        break;
                    case 'invoice_balance':
                        $replace = format_currency($object->invoice_balance);
                        break;
                    case 'invoice_status':
                        $replace = get_invoice_status($object->invoice_status_id);
                        break;
                    case 'quote_item_subtotal':
                        $replace = format_currency($object->quote_item_subtotal);
                        break;
                    case 'quote_tax_total':
                        $replace = format_currency($object->quote_tax_total);
                        break;
                    case 'quote_item_discount':
                        $replace = format_currency($object->quote_item_discount);
                        break;
                    case 'quote_total':
                        $replace = format_currency($object->quote_total);
                        break;
                    case 'quote_date_created':
                        $replace = date_from_mysql($object->quote_date_created, true);
                        break;
                    case 'quote_date_expires':
                        $replace = date_from_mysql($object->quote_date_expires, true);
                        break;
                    case 'quote_guest_url':
                        $replace = site_url('guest/view/quote/' . $object->quote_url_key);
                        break;
                    case 'sumex_casedate':
                        if (isset($object->sumex_casedate)) {
                            $replace = date_from_mysql($object->sumex_casedate, true);
                        }

                        break;
                    default:
                        // Check if it's a custom field
                        if (preg_match('/ip_cf_(\d.*)/', $var, $cf_id)) {
                            // Get the custom field
                            $cf = \Modules\Core\Models\CustomField::find($cf_id[1]);

                            if ($cf) {
                                // Get the values for the custom field
                                $cf_table = $cf->custom_field_table;
                                $cf_column = $cf->custom_field_column;
                                
                                // Determine which model to use based on table name
                                $modelClass = match($cf_table) {
                                    'ip_invoice_custom' => \Modules\Invoices\Models\InvoiceCustom::class,
                                    'ip_quote_custom' => \Modules\Quotes\Models\QuoteCustom::class,
                                    'ip_client_custom' => \Modules\Crm\Models\ClientCustom::class,
                                    'ip_user_custom' => \Modules\Core\Models\UserCustom::class,
                                    'ip_payment_custom' => \Modules\Payments\Models\PaymentCustom::class,
                                    default => null,
                                };
                                
                                if ($modelClass) {
                                    // Get the ID field name from the table
                                    $idField = str_replace('_custom', '_id', str_replace('ip_', '', $cf_table));
                                    $record = $modelClass::where($idField, $object->{$idField})->first();
                                    $replace = $record ? $record->{$cf_column} : '';
                                    
                                    if ($cf->custom_field_type == 'SINGLE-CHOICE' && $replace) {
                                        $el = \Modules\Core\Models\CustomValue::find($replace);
                                        $replace = $el ? $el->custom_values_value : '';
                                    }
                                } else {
                                    $replace = '';
                                }
                            } else {
                                $replace = '';
                            }
                        } else {
                            $replace = $object->{$var} ?? $var;
                        }
                }

                $body = str_replace('{{{' . $var . '}}}', $replace, $body);
            }
        }

        return $body;
    }

    /**
     * Returns the translated invoice status.
     *
     *
     * @origin Modules/Core/Helpers/template_helper.php
     * @param $invoice->invoice_status_id
     *
     * @return string
     */
    public static function get_invoice_status($id)
    {
        // Invoice statuses - should eventually move to a Status enum or config
        $statuses = [
            1 => ['label' => trans('draft')],
            2 => ['label' => trans('sent')],
            3 => ['label' => trans('viewed')],
            4 => ['label' => trans('paid')],
            5 => ['label' => trans('overdue')],
            6 => ['label' => trans('canceled')],
        ];

        return $statuses[$id]['label'] ?? trans('unknown');
    }

    /**
     * Returns the appropriate PDF template for the given invoice.
     *
     *
     * @origin Modules/Core/Helpers/template_helper.php
     * @param $invoice
     *
     * @return mixed
     */
    public static function select_pdf_invoice_template($invoice)
    {
        // TODO: Migrate remaining CodeIgniter dependencies to Laravel

        if ($invoice->is_overdue) {
            // Use the overdue template
            return $bridge->settings()->setting('pdf_invoice_template_overdue');
        }

        if ($invoice->invoice_status_id == 4) {
            // Use the paid template
            return $bridge->settings()->setting('pdf_invoice_template_paid');
        }

        // Use the default template
        return $bridge->settings()->setting('pdf_invoice_template');
    }

    /**
     * Returns the appropriate email template for the given invoice.
     *
     *
     * @origin Modules/Core/Helpers/template_helper.php
     * @param $invoice
     *
     * @return mixed
     */
    public static function select_email_invoice_template($invoice)
    {
        // TODO: Migrate remaining CodeIgniter dependencies to Laravel

        if ($invoice->is_overdue) {
            // Use the overdue template
            return $bridge->settings()->setting('email_invoice_template_overdue');
        }

        if ($invoice->invoice_status_id == 4) {
            // Use the paid template
            return $bridge->settings()->setting('email_invoice_template_paid');
        }

        // Use the default template
        return $bridge->settings()->setting('email_invoice_template');
    }
}
