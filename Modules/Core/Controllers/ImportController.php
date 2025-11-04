<?php

namespace Modules\Core\Controllers;

use Modules\Core\Services\ImportService;
use Modules\Core\Support\TranslationHelper;

/**
 * ImportController.
 *
 * Manages data import operations from CSV files
 *
 * @legacy-file application/modules/import/controllers/Import.php
 */
class ImportController
{
    private array $allowed_files = ['clients.csv', 'invoices.csv', 'invoice_items.csv', 'payments.csv'];

    /**
     * Initialize the ImportController with dependency injection.
     *
     * @param ImportService $importService
     */
    public function __construct(
        protected ImportService $importService
    ) {}

    /**
     * Display a paginated list of import records.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/import/controllers/Import.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $this->importService->paginate(route('import.index'), $page);
        $imports = $this->importService->result();

        return view('core::import_index', [
            'imports' => $imports,
        ]);
    }

    /**
     * Display the import form or process a submitted import.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     *
     * @legacy-file application/modules/import/controllers/Import.php
     */
    public function form()
    {
        if ( ! request()->input('btn_submit')) {
            $files = directory_map('./uploads/import');
            foreach ($files as $key => $file) {
                if ( ! is_numeric(array_search($file, $this->allowed_files, true))) {
                    unset($files[$key]);
                }
            }

            return view('core::import_form', [
                'files' => $files,
            ]);
        }

        // Process import submission
        $import_id = $this->importService->startImport();

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
                        $ids = $this->importService->importData($file, 'ip_clients');
                        $this->importService->recordImportDetails($import_id, 'ip_clients', 'clients', $ids);
                        break;
                    case 'invoices.csv':
                        $ids = $this->importService->importInvoices();
                        $this->importService->recordImportDetails($import_id, 'ip_invoices', 'invoices', $ids);
                        break;
                    case 'invoice_items.csv':
                        $ids = $this->importService->importInvoiceItems();
                        $this->importService->recordImportDetails($import_id, 'ip_invoice_items', 'invoice_items', $ids);
                        break;
                    case 'payments.csv':
                        $ids = $this->importService->importPayments();
                        $this->importService->recordImportDetails($import_id, 'ip_payments', 'payments', $ids);
                        break;
                }
            }
        }

        return redirect()->route('import.index');
    }

    /**
     * Delete an import record.
     *
     * @param int $id Import ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     *
     * @legacy-file application/modules/import/controllers/Import.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->importService->delete($id);

        return redirect()->route('import.index')
            ->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
