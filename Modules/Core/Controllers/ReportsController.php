<?php

namespace Modules\Core\Models;

use AllowDynamicProperties;
use Illuminate\Http\Request;
use Modules\Core\Controllers\AdminController;
use Modules\Reports\Controllers\ReportsService;

#[AllowDynamicProperties]
class ReportsController extends AdminController
{
    /**
     * Initialize the ReportsController.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @originalName salesByClient
     *
     * @originalFile ReportsController.php
     */
    public function salesByClient(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = (new ReportsService())->salesByClient($request->input('from_date'), $request->input('to_date'));
            $data    = [
                'results'   => $results,
                'from_date' => $request->input('from_date'),
                'to_date'   => $request->input('to_date'),
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
            // PDF::loadView('reports.sales_by_client', $data)->download('sales_by_client.pdf');
            // return response()->download(...);
        }

        return view('reports.sales_by_client_index');
    }

    /**
     * @originalName invoicesPerClient
     *
     * @originalFile ReportsController.php
     */
    public function invoicesPerClient(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = (new ReportsService())->invoicesPerClient($request->input('from_date'), $request->input('to_date'));
            $data    = [
                'results'   => $results,
                'from_date' => $request->input('from_date'),
                'to_date'   => $request->input('to_date'),
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
        }

        return view('reports.invoices_per_client_index');
    }

    /**
     * @originalName paymentHistory
     *
     * @originalFile ReportsController.php
     */
    public function paymentHistory(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = (new ReportsService())->paymentHistory($request->input('from_date'), $request->input('to_date'));
            $data    = [
                'results'   => $results,
                'from_date' => $request->input('from_date'),
                'to_date'   => $request->input('to_date'),
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
        }

        return view('reports.payment_history_index');
    }

    /**
     * @originalName invoiceAging
     *
     * @originalFile ReportsController.php
     */
    public function invoiceAging(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = (new ReportsService())->invoiceAging();
            $data    = [
                'results' => $results,
            ];
            // TODO: Use Laravel PDF package to generate PDF from view
        }

        return view('reports.invoice_aging_index');
    }

    /**
     * Display the Sales by Year report page and prepare report data when the form is submitted.
     *
     * When the request includes 'btn_submit', prepares report data (results, from_date, to_date)
     * from the request inputs for rendering or PDF generation.
     *
     * @return \Illuminate\Contracts\View\View the view for the sales by year report
     */
    public function salesByYear(Request $request): \Illuminate\Contracts\View\View
    {
        if ($request->input('btn_submit')) {
            $results = (new ReportsService())->salesByYear(
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

        return view('reports.sales_by_year_index');
    }
}
