<?php

namespace Modules\Core\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Services\ReportsService;

/**
 * ReportsController
 *
 * Manages various report generation operations
 *
 * @legacy-file application/modules/reports/controllers/Reports.php
 */
class ReportsController
{    /**
     * Initialize the ReportsController with dependency injection.
     *
     * @param ReportsService $reportsService
     */
    public function __construct(
        protected ReportsService $reportsService
    ) {
    }

    /**
     * Display sales by client report.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @legacy-function salesByClient
     * @legacy-file application/modules/reports/controllers/Reports.php
     */
    public function salesByClient(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = $this->reportsService->salesByClient(
                $request->input('from_date'),
                $request->input('to_date')
            );
            $data = [
                'results'   => $results,
                'from_date' => $request->input('from_date'),
                'to_date'   => $request->input('to_date'),
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
            // PDF::loadView('reports.sales_by_client', $data)->download('sales_by_client.pdf');
            // return response()->download(...);
        }

        return view('core::reports_sales_by_client_index');
    }

    /**
     * Display invoices per client report.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @legacy-function invoicesPerClient
     * @legacy-file application/modules/reports/controllers/Reports.php
     */
    public function invoicesPerClient(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = $this->reportsService->invoicesPerClient(
                $request->input('from_date'),
                $request->input('to_date')
            );
            $data = [
                'results'   => $results,
                'from_date' => $request->input('from_date'),
                'to_date'   => $request->input('to_date'),
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
        }

        return view('core::reports_invoices_per_client_index');
    }

    /**
     * Display payment history report.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @legacy-function paymentHistory
     * @legacy-file application/modules/reports/controllers/Reports.php
     */
    public function paymentHistory(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = $this->reportsService->paymentHistory(
                $request->input('from_date'),
                $request->input('to_date')
            );
            $data = [
                'results'   => $results,
                'from_date' => $request->input('from_date'),
                'to_date'   => $request->input('to_date'),
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
        }

        return view('core::reports_payment_history_index');
    }

    /**
     * Display invoice aging report.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @legacy-function invoiceAging
     * @legacy-file application/modules/reports/controllers/Reports.php
     */
    public function invoiceAging(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = $this->reportsService->invoiceAging();
            $data = [
                'results' => $results,
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
        }

        return view('core::reports_invoice_aging_index');
    }

    /**
     * Display sales by year report.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @legacy-function salesByYear
     * @legacy-file application/modules/reports/controllers/Reports.php
     */
    public function salesByYear(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = $this->reportsService->salesByYear(
                $request->input('from_date'),
                $request->input('to_date'),
                $request->input('minQuantity'),
                $request->input('maxQuantity'),
                $request->input('checkboxTax')
            );
            $data = [
                'results'   => $results,
                'from_date' => $request->input('from_date'),
                'to_date'   => $request->input('to_date'),
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
        }

        return view('core::reports_sales_by_year_index');
    }
}
