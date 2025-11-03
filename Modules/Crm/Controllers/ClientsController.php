<?php

namespace Modules\Crm\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Crm\Services\ClientService;
use Modules\Crm\Services\ClientNoteService;
use Modules\Invoices\Services\InvoiceService;
use Modules\Quotes\Services\QuoteService;
use Modules\Payments\Services\PaymentService;
use Modules\Core\Services\CustomFieldService;

/**
 * ClientsController
 *
 * Handles client management including CRUD operations, viewing, and eInvoicing integration
 *
 * @legacy-file application/modules/clients/controllers/Clients.php
 */
class ClientsController
{
    private const CLIENT_TITLE = 'client_title';

    protected ClientService $clientService;
    protected ClientNoteService $clientNoteService;
    protected InvoiceService $invoiceService;
    protected QuoteService $quoteService;
    protected PaymentService $paymentService;
    protected CustomFieldService $customFieldService;

    /**
     * Initialize the ClientsController with dependency injection.
     *
     * @param ClientService $clientService
     * @param ClientNoteService $clientNoteService
     * @param InvoiceService $invoiceService
     * @param QuoteService $quoteService
     * @param PaymentService $paymentService
     * @param CustomFieldService $customFieldService
     */
    public function __construct(
        ClientService $clientService,
        ClientNoteService $clientNoteService,
        InvoiceService $invoiceService,
        QuoteService $quoteService,
        PaymentService $paymentService,
        CustomFieldService $customFieldService
    ) {
        $this->clientService = $clientService;
        $this->clientNoteService = $clientNoteService;
        $this->invoiceService = $invoiceService;
        $this->quoteService = $quoteService;
        $this->paymentService = $paymentService;
        $this->customFieldService = $customFieldService;
    }

    /**
     * Redirect to the default client status view (active clients).
     *
     * @param Request $request
     *
     * @return void
     *
     * @legacy-function index
     * @legacy-file application/modules/clients/controllers/Clients.php
     */
    public function index(Request $request): void
    {
        // Display active clients by default
        redirect('clients/status/active');
    }

    /**
     * Display clients filtered by status with pagination.
     *
     * @param Request $request
     * @param string $status
     * @param int $page
     *
     * @return void
     *
     * @legacy-function status
     * @legacy-file application/modules/clients/controllers/Clients.php
     */
    public function status(Request $request, string $status = 'active', $page = 0): void
    {
        if (is_numeric(array_search($status, ['active', 'inactive'], true))) {
            $function = 'is_' . $status;
            $this->mdl_clients->{$function}();
        }

        $this->mdl_clients->with_total_balance()->paginate(site_url('clients/status/' . $status), $page);
        $clients = $this->mdl_clients->result();

        $req_einvoicing = get_setting('einvoicing');
        if ($req_einvoicing) {
            $this->load->helper('e-invoice'); // eInvoicing++

            foreach ($clients as &$client) {
                // Get a check of filled Required (client and users) fields for eInvoicing
                $req_einvoicing = get_req_fields_einvoice($client);

                $client = $this->check_client_einvoice_active($client, $req_einvoicing);
            }
            unset($client);
        }

        return view('crm::clients_index', [
            'records'            => $clients,
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_clients'),
            'filter_method'      => 'filter_clients',
            'einvoicing'         => get_setting('einvoicing'),
        ]);
    }

    /**
     * Handle the client form for creating or editing a client.
     *
     * @param Request $request
     * @param int|null $id
     *
     * @return void
     *
     * @legacy-function form
     * @legacy-file application/modules/clients/controllers/Clients.php
     */
    public function form(Request $request, $id = null): void
    {
        if ($this->input->post('btn_cancel')) {
            redirect('clients');
        }

        $new_client = false;
        $this->filter_input();  // <<<--- filters _POST array for nastiness

        // Set validation rule based on is_update
        if ($this->input->post('is_update') == 0 && $this->input->post('client_name') != '') {
            $check = $this->db->get_where('ip_clients', [
                'client_name'    => $this->input->post('client_name'),
                'client_surname' => $this->input->post('client_surname'),
            ])->result();

            if ( ! empty($check)) {
                $this->session->set_flashdata('alert_error', trans('client_already_exists'));
                redirect('clients/form');
            } else {
                $new_client = true;
            }
        }

        if ($this->mdl_clients->run_validation()) {
            $client_title_custom = $this->input->post('client_title_custom');
            // Custom title selected
            if ($_POST[self::CLIENT_TITLE] == ClientTitleEnum::CUSTOM) {
                $_POST[self::CLIENT_TITLE] = $client_title_custom;
                $this->mdl_clients->set_form_value(self::CLIENT_TITLE, $client_title_custom);
            }

            // fix e-invoice reset
            if ($this->input->post('client_start_einvoicing') == '0') {
                $_POST['client_einvoicing_version'] = '';
                $this->mdl_clients->set_form_value('client_einvoicing_version', '');
            }

            $id = $this->mdl_clients->save($id);

            if ($new_client) {
                $this->load->model('user_clients/mdl_user_clients');
                $this->mdl_user_clients->get_users_all_clients();
            }

            $this->load->model('custom_fields/mdl_client_custom');
            $result = $this->mdl_client_custom->save_custom($id, $this->input->post('custom'));

            $where = 'view';
            if ($result !== true) {
                $this->session->set_flashdata('alert_error', $result);
                $this->session->set_flashdata('alert_success', null);
                $where = 'form';
            }

            redirect('clients/' . $where . '/' . $id);
        }

        $req_einvoicing = get_setting('einvoicing');
        if ($req_einvoicing) {
            $this->load->helper('e-invoice'); // eInvoicing++
            // Get a check of filled Required (client and users) fields for eInvoicing
            $req_einvoicing = get_req_fields_einvoice(($new_client || ! $id) ? null : $this->db->from('ip_clients')->where('client_id', $id)->get()->row());
        }

        if ($id && ! $this->input->post('btn_submit')) {
            if ( ! $this->mdl_clients->prep_form($id)) {
                show_404();
            }

            $this->load->model('custom_fields/mdl_client_custom');
            $this->mdl_clients->set_form_value('is_update', true);

            $client_custom = $this->mdl_client_custom->where('client_id', $id)->get();

            if ($client_custom->num_rows()) {
                $client_custom = $client_custom->row();

                unset($client_custom->client_id, $client_custom->client_custom_id);

                foreach ($client_custom as $key => $val) {
                    $this->mdl_clients->set_form_value('custom[' . $key . ']', $val);
                }
            }
        } elseif ($this->input->post('btn_submit')) {
            if ($this->input->post('custom')) {
                foreach ($this->input->post('custom') as $key => $val) {
                    $this->mdl_clients->set_form_value('custom[' . $key . ']', $val);
                }
            }
        }

        $this->load->model([
            'custom_fields/mdl_custom_fields',
            'custom_values/mdl_custom_values',
            'custom_fields/mdl_client_custom',
        ]);

        $custom_fields = $this->mdl_custom_fields->by_table('ip_client_custom')->get()->result();
        $custom_values = [];
        foreach ($custom_fields as $custom_field) {
            if (in_array($custom_field->custom_field_type, $this->mdl_custom_values->custom_value_fields())) {
                $values                                        = $this->mdl_custom_values->get_by_fid($custom_field->custom_field_id)->result();
                $custom_values[$custom_field->custom_field_id] = $values;
            }
        }

        $fields = $this->mdl_client_custom->get_by_clid($id);

        foreach ($custom_fields as $cfield) {
            foreach ($fields as $fvalue) {
                if ($fvalue->client_custom_fieldid == $cfield->custom_field_id) {
                    // TODO: Hackish, may need a better optimization
                    $this->mdl_clients->set_form_value(
                        'custom[' . $cfield->custom_field_id . ']',
                        $fvalue->client_custom_fieldvalue
                    );
                    break;
                }
            }
        }

        $this->load->helper(['custom_values', 'e-invoice']); // e-invoice - since 1.6.3

        return view('crm::clients_form', [
            'client_id'            => $id,
            'custom_fields'        => $custom_fields,
            'custom_values'        => $custom_values,
            'countries'            => get_country_list(trans('cldr')),
            'selected_country'     => $this->mdl_clients->form_value('client_country') ?: get_setting('default_country'),
            'languages'            => get_available_languages(),
            'client_title_choices' => $this->get_client_title_choices(),
            'xml_templates'        => get_xml_template_files(), // eInvoicing
            'req_einvoicing'       => $req_einvoicing,
        ]);
    }

    /**
     * Display detailed client information with related invoices, quotes, and payments.
     *
     * @param Request $request
     * @param int $client_id
     * @param string $activeTab
     * @param int $page
     *
     * @return void
     *
     * @legacy-function view
     * @legacy-file application/modules/clients/controllers/Clients.php
     */
    public function view(Request $request, $client_id, $activeTab = 'detail', $page = 0): void
    {
        $client = $this->mdl_clients
            ->with_total()
            ->with_total_balance()
            ->with_total_paid()
            ->where('ip_clients.client_id', $client_id)
            ->get()->row();

        if ( ! $client) {
            show_404();
        }

        $this->load->model(
            [
                'clients/mdl_client_notes',
                'invoices/mdl_invoices',
                'quotes/mdl_quotes',
                'payments/mdl_payments',
                'custom_fields/mdl_custom_fields',
                'custom_fields/mdl_client_custom',
            ]
        );

        $req_einvoicing = get_setting('einvoicing');
        if ($req_einvoicing) {
            $this->load->helper('e-invoice'); // eInvoicing++

            // Get a check of filled Required (client and users) fields for eInvoicing
            $req_einvoicing = get_req_fields_einvoice($client);

            $client = $this->check_client_einvoice_active($client, $req_einvoicing);
        }

        // Change page only for one url (tab) system
        $p = ['invoices' => 0, 'quotes' => 0, 'payments' => 0]; // Default
        // Session key
        $key = 'clientview';
        // When detail (from menu)
        if ($activeTab == 'detail') {
            // Clear temp + session
            $this->session->unmark_temp($key);
            unset($_SESSION[$key]);
        } else {
            // Set pages saved in session
            if (isset($_SESSION[$key])) {
                $p = $_SESSION[$key];
            }

            // Up Actual page num
            $p[$activeTab] = $page;
            // Save in session
            $_SESSION[$key] = $p;
            // For 300 seconds
            $this->session->mark_as_temp($key);
        }

        $base_url = site_url('clients/view/' . $client_id);
        $this->mdl_invoices->by_client($client_id)->paginate($base_url . '/invoices', $p['invoices'], 5);
        $this->mdl_quotes->by_client($client_id)->paginate($base_url . '/quotes', $p['quotes'], 5);
        $this->mdl_payments->by_client($client_id)->paginate($base_url . '/payments', $p['payments'], 5);

        $custom_fields = $this->mdl_client_custom->get_by_client($client_id)->result();
        $this->mdl_client_custom->prep_form($client_id);

        return view('crm::clients_view', [
            'client'           => $client,
            'client_notes'     => $this->mdl_client_notes->where('client_id', $client_id)->get()->result(),
            'invoices'         => $this->mdl_invoices->result(),
            'quotes'           => $this->mdl_quotes->result(),
            'payments'         => $this->mdl_payments->result(),
            'custom_fields'    => $custom_fields,
            'quote_statuses'   => $this->mdl_quotes->statuses(),
            'invoice_statuses' => $this->mdl_invoices->statuses(),
            'activeTab'        => $activeTab,
            'req_einvoicing'   => $req_einvoicing,
        ]);
    }

    /**
     * Delete a client by ID.
     *
     * @param Request $request
     * @param int $client_id
     *
     * @return void
     *
     * @legacy-function delete
     * @legacy-file application/modules/clients/controllers/Clients.php
     */
    public function delete(Request $request, $client_id): void
    {
        $this->mdl_clients->delete($client_id);
        redirect('clients');
    }

    /**
     * Get client title choices for form selection.
     *
     * @return array
     *
     * @legacy-function get_client_title_choices
     * @legacy-file application/modules/clients/controllers/Clients.php
     */
    private function get_client_title_choices(): array
    {
        return array_map(
            fn ($clientTitleEnum) => $clientTitleEnum->value,
            ClientTitleEnum::cases()
        );
    }

    /**
     * Check and update client eInvoicing active status.
     *
     * @param object $client
     * @param object $req_einvoicing
     *
     * @return object
     *
     * @legacy-function check_client_einvoice_active
     * @legacy-file application/modules/clients/controllers/Clients.php
     */
    private function check_client_einvoice_active($client, $req_einvoicing) {
        // Update active eInvoicing client
        $o = $client->client_einvoicing_active;
        if ( ! empty($client->client_einvoicing_version) && $req_einvoicing->clients[$client->client_id]->einvoicing_empty_fields == 0) {
            $client->client_einvoicing_active = 1; // update view
        } else {
            $client->client_einvoicing_active = 0; // update view
        }

        // Update db if need
        if ($o != $client->client_einvoicing_active) {
            $this->db->where('client_id', $client->client_id);
            $this->db->set('client_einvoicing_active', $client->client_einvoicing_active);
            $this->db->update('ip_clients');
        }

        return $client;
    }
}
