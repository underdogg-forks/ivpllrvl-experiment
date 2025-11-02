<?php

namespace Modules\Crm\app\Http\Controllers;

use AllowDynamicProperties;
use Illuminate\Http\Request;

use function Modules\Clients\Controllers\abort;
use function Modules\Clients\Controllers\config;
use function Modules\Clients\Controllers\redirect;
use function Modules\Clients\Controllers\view;

use Modules\Clients\Models\tmpClient;
use Modules\Core\Controllers\AdminController;
use Modules\Crm\app\Services\ClientsService;

#[AllowDynamicProperties]
class ClientsController extends AdminController
{
    private const CLIENT_TITLE = 'client_title';

    /**
     * Initialize the ClientsController and perform required AdminController setup.
     *
     * Ensures the base controller's initialization runs so the clients controller
     * inherits common admin behaviour and dependencies.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @originalName index
     *
     * @originalFile ClientsController.php
     */
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('clients.status', ['status' => 'active']);
    }

    /**
     * @originalName status
     *
     * @originalFile ClientsController.php
     */
    public function status(Request $request, string $status = 'active', int $page = 0): \Illuminate\Contracts\View\View
    {
        $clientsQuery = tmpClient::query();
        if ($status === 'active') {
            $clientsQuery->where('active', 1);
        } elseif ($status === 'inactive') {
            $clientsQuery->where('active', 0);
        }
        $clients    = $clientsQuery->get();
        $einvoicing = config('settings.einvoicing');

        // Skipping e-invoicing logic for brevity
        return view('clients.index', [
            'records'            => $clients,
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_clients'),
            'filter_method'      => 'filter_clients',
            'einvoicing'         => $einvoicing,
        ]);
    }

    /**
     * @originalName form
     *
     * @originalFile ClientsController.php
     */
    public function form(Request $request, $id = null): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        if ($request->has('btn_cancel')) {
            return redirect()->route('clients.index');
        }

        // Validation and save logic would go here
        // Skipping custom fields and e-invoicing logic for brevity
        return view('clients.form', [
            'client_id'            => $id,
            'custom_fields'        => [],
            'custom_values'        => [],
            'countries'            => [],
            'selected_country'     => null,
            'languages'            => [],
            'client_title_choices' => [],
            'xml_templates'        => [],
            'req_einvoicing'       => null,
        ]);
    }

    /**
     * Display the client detail view for a given client.
     *
     * Aborts with a 404 response if the client cannot be found.
     *
     * @param int|string $client_id the ID of the client to display
     * @param string     $activeTab the tab to mark active in the view (defaults to 'detail')
     * @param int        $page      optional page index for tabbed subviews or pagination
     *
     * @return \Illuminate\Contracts\View\View The rendered 'clients.view' with the client and active tab.
     */
    public function view(Request $request, $client_id, $activeTab = 'detail', $page = 0): \Illuminate\Contracts\View\View
    {
        $client = tmpClient::find($client_id);
        if ( ! $client) {
            abort(404);
        }

        // Skipping tab/session logic for brevity
        return view('clients.view', [
            'client'    => $client,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Delete the specified client and redirect to the clients index.
     *
     * @param int $client_id the identifier of the client to delete
     *
     * @return \Illuminate\Http\RedirectResponse a redirect response to the clients index route
     */
    public function delete($client_id): \Illuminate\Http\RedirectResponse
    {
        (new ClientsService())->delete($client_id);

        return redirect()->route('clients.index');
    }

    /**
     * @originalName getClientTitleChoices
     *
     * @originalFile ClientsController.php
     */
    private function getClientTitleChoices(): array
    {
        return [];
    }

    /**
     * Ensure a client's e-invoicing activation matches the requested e-invoicing setting.
     *
     * This is currently a no-op that returns the provided client unchanged; intended to
     * validate or adjust the client's e-invoicing state when implemented.
     *
     * @param mixed $client         the client entity to check or update (typically a Client model instance)
     * @param mixed $req_einvoicing The requested e-invoicing setting (e.g., boolean, null, or config identifier).
     *
     * @return mixed the (possibly updated) client entity
     */
    private function checkClientEinvoiceActive($client, $req_einvoicing)
    {
        return $client;
    }
}
