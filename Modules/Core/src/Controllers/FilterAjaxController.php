<?php

namespace Modules\Core\Controllers;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class FilterAjaxController extends AdminController
{
    public $ajax_controller = true;

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_invoices()
     */
    public function filter_invoices()
    {
        $this->load->model('invoices/invoice');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->invoice->like("CONCAT_WS('^',LOWER(invoice_number),invoice_date_created,invoice_date_due,LOWER(client_title),LOWER(client_name),LOWER(client_surname),invoice_total,invoice_balance)", $keyword);
            }
        }

        $data = [
            'invoices'         => $this->invoice->get()->result(),
            'invoice_statuses' => $this->invoice->statuses(),
        ];

        $this->renderViewAsJson('invoices/partial_invoice_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_quotes()
     */
    public function filter_quotes()
    {
        $this->load->model('quotes/quote');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->quote->like("CONCAT_WS('^',LOWER(quote_number),quote_date_created,quote_date_expires,LOWER(client_title),LOWER(client_name),LOWER(client_surname),quote_total)", $keyword);
            }
        }

        $data = [
            'quotes'         => $this->quote->get()->result(),
            'quote_statuses' => $this->quote->statuses(),
        ];

        $this->renderViewAsJson('quotes/partial_quote_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_clients()
     */
    public function filter_clients()
    {
        $this->load->model('clients/client');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_trim(mb_strtolower($keyword));
                $this->client->like("CONCAT_WS('^',LOWER(client_title),LOWER(client_name),LOWER(client_surname),LOWER(client_email),client_phone,client_active)", $keyword);
            }
        }

        $data = [
            'records'    => $this->client->with_total_balance()->get()->result(),
            'einvoicing' => get_setting('einvoicing'),
        ];

        $this->renderViewAsJson('clients/partial_client_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_custom_fields()
     */
    public function filter_custom_fields()
    {
        // custom table option name Normaly always here (it's ajax). Old school but work.
        $name = empty($_SERVER['HTTP_REFERER']) ? 'all' : basename($_SERVER['HTTP_REFERER']); // Todo: With CI?

        $this->load->model('custom_fields/customfield');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                // custom_field_location, custom_field_order
                $this->customfields->like("CONCAT_WS('^',custom_field_id, LOWER(custom_field_table), LOWER(custom_field_label), LOWER(custom_field_type))", $keyword);
            }
        }

        // Determine which name of table custom field to load
        $custom_tables = $this->customfields->custom_tables();
        if ($name != 'all' && in_array($name, $custom_tables)) {
            $this->customfields->by_table_name($name);
        }

        $custom_fields = $this->customfields->get()->result();

        $this->load->model('custom_values/customvalue');
        $data = [
            'custom_fields'       => $custom_fields,
            'custom_tables'       => $custom_tables,
            'custom_value_fields' => $this->customvalues->custom_value_fields(),
            'positions'           => $this->customfields->get_positions(true),
        ];

        $this->renderViewAsJson('custom_fields/partial_custom_fields_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_custom_values()
     */
    public function filter_custom_values()
    {
        // custom values id Normaly always here (it's ajax). Old school but work.
        $id = empty($_SERVER['HTTP_REFERER']) ? 0 : basename($_SERVER['HTTP_REFERER']); // Todo: With CI?

        $this->load->model(
            [
                'custom_values/mdl_custom_value',
                'custom_fields/mdl_custom_field',
            ]
        );

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->customvalues->like("CONCAT_WS('^',LOWER(custom_values_value), LOWER(custom_field_table), LOWER(custom_field_label), LOWER(custom_field_type))", $keyword);
            }
        }

        $this->customvalues->grouped();
        $custom_values = $this->customvalues->get()->result();

        $data = [
            'id'            => $id,
            'custom_values' => $custom_values,
            'custom_tables' => $this->customfields->custom_tables(),
            'positions'     => $this->customfields->get_positions(true),
        ];

        $this->renderViewAsJson('custom_values/partial_custom_values_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_custom_values_field()
     */
    public function filter_custom_values_field()
    {
        $this->load->model('custom_values/customvalue');

        // custom values id Normaly always here (it's ajax). Old school but work.
        $id = empty($_SERVER['HTTP_REFERER']) ? 0 : basename($_SERVER['HTTP_REFERER']); // Todo: With CI?

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->customvalues->like("CONCAT_WS('^',custom_values_id,LOWER(custom_values_value))", $keyword);
            }
        }

        $elements = $this->customvalues->get_by_fid($id)->result();
        $data     = [
            'id'       => $id,
            'elements' => $elements,
        ];

        $this->renderViewAsJson('custom_values/partial_custom_values_field', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_projects()
     */
    public function filter_projects()
    {
        $this->load->model('projects/project');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);
        // client_id : Column 'client_id' in where clause is ambiguous (ip_clients.client_id or ip_project.client_id
        // Not showed in frontend table
        // project_id,client_id,
        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->project->like("CONCAT_WS('^',LOWER(client_title),LOWER(client_name),LOWER(client_surname),LOWER(project_name))", $keyword);
            }
        }

        $data = [
            'projects' => $this->project->get()->result(),
        ];

        $this->renderViewAsJson('projects/partial_projects_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_tasks()
     */
    public function filter_tasks()
    {
        $this->load->model('tasks/task');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);
        // Column 'project_id' in where clause is ambiguous
        // Not showed in frontend table:
        // task_id,ip_tasks.project_id,LOWER(task_description),
        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->task->like("CONCAT_WS('^',LOWER(task_name),LOWER(project_name),LOWER(task_price),task_finish_date,LOWER(task_status),LOWER(tax_rate_id))", $keyword);
            }
        }

        $data = [
            'tasks'         => $this->task->get()->result(),
            'task_statuses' => $this->task->statuses(),
        ];

        $this->renderViewAsJson('tasks/partial_tasks_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_products()
     */
    public function filter_products()
    {
        $this->load->model('products/product');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        // Columns 'tax_rate_id' & 'unit_id' in where clause is ambiguous
        // Not showed in frontend table:
        // product_id,LOWER(family_name),purchase_price,LOWER(provider_name),LOWER(tax_rate_name),LOWER(unit_name_plrl),
        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->product->like("CONCAT_WS('^',product_sku,LOWER(family_name),LOWER(product_name),LOWER(product_description),product_price,product_tariff)", $keyword);
            }
        }

        $data = [
            'products' => $this->product->get()->result(),
        ];

        $this->renderViewAsJson('products/partial_products_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_users()
     */
    public function filter_users()
    {
        $this->load->model('users/user');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        // Not used: user_id    user_type   user_active user_date_modified  user_language   user_password   user_psalt  user_passwordreset_token
        // Not showed in frontend table:
        // user_date_created,LOWER(user_company),LOWER(user_address_1),LOWER(user_address_2),LOWER(user_city),LOWER(user_state),LOWER(user_zip),LOWER(user_country),
        // LOWER(user_invoicing_contact),LOWER(user_phone),LOWER(user_fax),LOWER(user_mobile),LOWER(user_web),
        // LOWER(user_vat_id),LOWER(user_tax_code),LOWER(user_all_clients),LOWER(user_subscribernumber),LOWER(user_bank),LOWER(user_iban),LOWER(user_bic),LOWER(user_remittance_text),LOWER(user_gln),LOWER(user_rcc)

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->user->like("CONCAT_WS('^', LOWER(user_name), LOWER(user_email))", $keyword);
            }
        }

        $data = [
            'users'      => $this->user->get()->result(),
            'user_types' => $this->user->user_types(),
        ];

        $this->renderViewAsJson('users/partial_users_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_families()
     */
    public function filter_families()
    {
        $this->load->model('families/family');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);
        // Not showed in frontend table:
        // family_id,
        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->family->like("CONCAT_WS('^',LOWER(family_name))", $keyword);
            }
        }

        $data = [
            'families' => $this->family->get()->result(),
        ];

        $this->renderViewAsJson('families/partial_families_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_invoices_recuring()
     */
    public function filter_invoices_recuring()
    {
        $this->load->model('invoices/invoicerecurring');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        // invoice_recurring_id invoice_id
        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->invoice_recurring->like("CONCAT_WS('^',recur_start_date,recur_end_date,recur_next_date,recur_frequency,LOWER(invoice_number),LOWER(client_title),LOWER(client_name),LOWER(client_surname))", $keyword);
            }
        }

        $data = [
            'recur_frequencies'  => $this->invoice_recurring->recur_frequencies,
            'recurring_invoices' => $this->invoice_recurring->get()->result(),
        ];

        $this->renderViewAsJson('invoices/partial_invoices_recurring_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_online_logs()
     */
    public function filter_online_logs()
    {
        $this->load->model('payments/paymentlog');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->paymentlogs->like("CONCAT_WS('^',merchant_response_id,LOWER(invoice_number),merchant_response_successful,merchant_response_date,LOWER(merchant_response_driver),LOWER(merchant_response),LOWER(merchant_response_reference))", $keyword);
            }
        }

        $data = [
            'payment_logs' => $this->paymentlogs->get()->result(),
        ];

        $this->renderViewAsJson('payments/partial_online_logs_table', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_archives()
     */
    public function filter_archives()
    {
        $this->load->model('invoices/invoice');

        $data = [
            'invoices_archive' => $this->invoice->get_archives($this->input->post('filter_query')),
        ];

        $this->renderViewAsJson('invoices/partial_invoice_archive', $data);
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/filter/controllers/Ajax.php
     *
     * @legacy-function filter_payments()
     */
    public function filter_payments()
    {
        $this->load->model('payments/payment');

        $query    = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword) {
                $keyword = mb_strtolower($keyword);
                $this->payment->like("CONCAT_WS('^',payment_date,LOWER(invoice_number),LOWER(client_title),LOWER(client_name),LOWER(client_surname),payment_amount,LOWER(payment_method_name),LOWER(payment_note))", $keyword);
            }
        }

        $data = [
            'payments' => $this->payment->get()->result(),
        ];

        $this->renderViewAsJson('payments/partial_payments_table', $data);
    }
}
