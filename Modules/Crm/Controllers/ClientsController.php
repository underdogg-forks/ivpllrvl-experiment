<?php

namespace Modules\Crm\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Crm\Models\Client;
use Modules\Crm\Services\ClientService;
use Modules\Crm\Services\ClientNoteService;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;
use Modules\Payments\Models\Payment;
use Modules\Payments\Services\PaymentService;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteService;
use Modules\Core\Services\CustomFieldService;

use Modules\Core\Support\CountryHelper;
use Modules\Core\Support\TranslationHelper;
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
        protected ClientService $clientService,
        protected ClientNoteService $clientNoteService,
        protected InvoiceService $invoiceService,
        protected QuoteService $quoteService,
        protected PaymentService $paymentService,
        protected CustomFieldService $customFieldService
    ) {
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
    public function index(Request $request)
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
    public function status(Request $request, string $status = 'active', $page = 0)
    {
        // Get clients using service based on status filter
        if ($status === 'active') {
            $clients = $this->clientService->getActiveClients();
        } else {
            // For inactive or all, use model directly or add service methods
            $query = Client::query();
            if ($status === 'inactive') {
                $query->where('client_active', 0);
            }
            $clients = $query->get();
        }

        $req_einvoicing = SettingsHelper::getSetting('einvoicing');
        if ($req_einvoicing) {
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
            'filter_placeholder' => TranslationHelper::trans('filter_clients'),
            'filter_method'      => 'filter_clients',
            'einvoicing'         => SettingsHelper::getSetting('einvoicing'),
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
    public function form(Request $request, $id = null)
    {
        if ($request->input('btn_cancel')) {
            redirect('clients');
        }

        $new_client = false;

        // Set validation rule based on is_update
        if ($request->input('is_update') == 0 && $request->input('client_name') != '') {
            $check = Client::where('client_name', $request->input('client_name'))
                ->where('client_surname', $request->input('client_surname'))
                ->first();

            if ($check) {
                session()->flash('alert_error', TranslationHelper::trans('client_already_exists'));
                redirect('clients/form');
            } else {
                $new_client = true;
            }
        }

        // Handle form submission
        if ($request->isMethod('post') && $request->input('btn_submit')) {
            $validated = $request->validate([
                'client_name' => 'required|string|max:255',
                'client_surname' => 'nullable|string|max:255',
                'client_email' => 'nullable|email|max:255',
                'client_phone' => 'nullable|string|max:50',
                'client_mobile' => 'nullable|string|max:50',
                'client_address_1' => 'nullable|string|max:255',
                'client_address_2' => 'nullable|string|max:255',
                'client_city' => 'nullable|string|max:255',
                'client_state' => 'nullable|string|max:255',
                'client_zip' => 'nullable|string|max:20',
                'client_country' => 'nullable|string|max:255',
                'client_vat_id' => 'nullable|string|max:50',
                'client_tax_code' => 'nullable|string|max:50',
            ]);

            // Handle custom title
            if ($request->input('client_title') == 'custom' && $request->input('client_title_custom')) {
                $validated['client_title'] = $request->input('client_title_custom');
            }

            // fix e-invoice reset
            if ($request->input('client_start_einvoicing') == '0') {
                $validated['client_einvoicing_version'] = '';
            }

            if ($id) {
                $this->clientService->update($id, $validated);
            } else {
                $client = $this->clientService->create($validated);
                $id = $client->client_id;
            }

            // TODO: Handle custom fields save
            // $this->customFieldService->saveCustom($id, $request->input('custom'));

            redirect('clients/view/' . $id);
        }

        // Load client for editing
        $client = $id ? $this->clientService->findOrFail($id) : new Client();
        
        $req_einvoicing = SettingsHelper::getSetting('einvoicing');
        if ($req_einvoicing && $id) {
            // Get a check of filled Required (client and users) fields for eInvoicing
            $req_einvoicing = get_req_fields_einvoice($client);
        }

        // Get custom fields
        $custom_fields = $this->customFieldService->byTable('ip_client_custom')->get();
        $custom_values = [];
        
        // TODO: Load custom field values for this client
        // This requires additional service methods

        return view('crm::clients_form', [
            'client_id'            => $id,
            'client'               => $client,
            'custom_fields'        => $custom_fields,
            'custom_values'        => $custom_values,
            'countries'            => CountryHelper::get_country_list(TranslationHelper::trans('cldr')),
            'selected_country'     => $client->client_country ?? SettingsHelper::getSetting('default_country'),
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
    public function view(Request $request, $client_id, $activeTab = 'detail', $page = 0)
    {
        $client = $this->clientService->findOrFail($client_id);

        $req_einvoicing = SettingsHelper::getSetting('einvoicing');
        if ($req_einvoicing) {
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
            session()->forget($key);
        } else {
            // Set pages saved in session
            $sessionData = session($key, $p);
            
            // Up Actual page num
            $sessionData[$activeTab] = $page;
            // Save in session
            session([$key => $sessionData]);
        }

        // Get related data - use service method when available
        $client_notes = $this->clientNoteService->getByClientId($client_id);
        
        // For invoices, quotes, payments - use Eloquent directly until service methods are added
        $invoices = Invoice::where('client_id', $client_id)->get();
        $quotes = Quote::where('client_id', $client_id)->get();
        $payments = Payment::where('client_id', $client_id)->get();
        
        // Get custom fields
        $custom_fields = $this->customFieldService->byTable('ip_client_custom')->get();

        return view('crm::clients_view', [
            'client'           => $client,
            'client_notes'     => $client_notes,
            'invoices'         => $invoices,
            'quotes'           => $quotes,
            'payments'         => $payments,
            'custom_fields'    => $custom_fields,
            'quote_statuses'   => $this->quoteService->getStatuses(),
            'invoice_statuses' => $this->invoiceService->getStatuses(),
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
    public function delete(Request $request, $client_id)
    {
        $this->clientService->delete($client_id);
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
            $this->clientService->update($client->client_id, [
                'client_einvoicing_active' => $client->client_einvoicing_active
            ]);
        }

        return $client;
    }
}
