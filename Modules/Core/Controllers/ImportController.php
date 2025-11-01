<?php

namespace Modules\Core\Controllers;

use AllowDynamicProperties;
use Modules\Core\Services\ImportService;

#[AllowDynamicProperties]
class ImportController extends AdminController
{
    private array $allowed_files = ['clients.csv', 'invoices.csv', 'invoice_items.csv', 'payments.csv'];

    /**
     * Initialize the ImportController and perform standard AdminController setup.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a paginated list of import records in the admin layout.
     *
     * Paginates import records using the provided page index, assigns the resulting
     * import collection to the layout as `imports`, buffers the import index view,
     * and renders the layout.
     *
     * @param int $page the page index for pagination (default 0)
     *
     * @return void
     */
    public function index($page = 0)
    {
        (new ImportService())->paginate(site_url('import/index'), $page);
        $imports = (new ImportService())->result();
        $this->layout->set('imports', $imports);
        $this->layout->buffer('content', 'import/index');
        $this->layout->render();
    }

    /**
     * Display the import form or process a submitted import.
     *
     * When accessed without a submission, prepares the list of allowed import files
     * from the uploads/import directory and renders the import form view.
     * When a submission is present, creates a new import record, processes the
     * selected allowed CSV files (clients.csv, invoices.csv, invoice_items.csv,
     * payments.csv), records import details for each processed file, and redirects
     * to the import listing.
     */
    public function form()
    {
        if ( ! request()->input('btn_submit')) {
// TODO: Laravel autoloads helpers - $this->load->helper('directory');
            $files = directory_map('./uploads/import');
            foreach ($files as $key => $file) {
                if ( ! is_numeric(array_search($file, $this->allowed_files, true))) {
                    unset($files[$key]);
                }
            }
            $this->layout->set('files', $files);
            $this->layout->buffer('content', 'import/import_index');
            $this->layout->render();
        } else {
// TODO: Laravel autoloads helpers - $this->load->helper('file');
            $import_id = (new ImportService())->startImport();
            if (request()->input('files')) {
                $files = $this->allowed_files;
                foreach ($files as $key => $file) {
                    if ( ! is_numeric(array_search($file, request()->input('files'), true))) {
                        unset($files[$key]);
                    }
                }
                foreach ($files as $file) {
                    switch ($file) {
                        case 'clients.csv':
                            $ids = (new ImportService())->importData($file, 'ip_clients');
                            (new ImportService())->recordImportDetails($import_id, 'ip_clients', 'clients', $ids);
                            break;
                        case 'invoices.csv':
                            $ids = (new ImportService())->importInvoices();
                            (new ImportService())->recordImportDetails($import_id, 'ip_invoices', 'invoices', $ids);
                            break;
                        case 'invoice_items.csv':
                            $ids = (new ImportService())->importInvoiceItems();
                            (new ImportService())->recordImportDetails($import_id, 'ip_invoice_items', 'invoice_items', $ids);
                            break;
                        case 'payments.csv':
                            $ids = (new ImportService())->importPayments();
                            (new ImportService())->recordImportDetails($import_id, 'ip_payments', 'payments', $ids);
                            break;
                    }
                }
            }
            redirect()->route('import');
        }
    }

    /**
     * Delete the import record identified by the given ID and redirect to the import index.
     *
     * @param int $id the ID of the import to delete
     */
    public function delete($id)
    {
        (new ImportService())->delete($id);
        redirect()->route('import.index');
    }
}
