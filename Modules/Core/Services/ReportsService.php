<?php

namespace Modules\Core\Services;

use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;

/**
 * ReportsService.
 *
 * Service class for generating various reports
 *
 * @legacy-file application/modules/reports/models/Mdl_reports.php (inferred)
 */
class ReportsService extends BaseService
{
    /**
     * Get the model class for this service.
     * Reports don't have a dedicated model, so return null.
     */
    protected function getModelClass(): ?string
    {
        return null;
    }

    /**
     * Generate sales by client report.
     *
     * @param string $fromDate Start date
     * @param string $toDate End date
     *
     * @return array Report results
     *
     * @legacy-function salesByClient
     */
    public function salesByClient(string $fromDate, string $toDate): array
    {
        // TODO: Implement sales by client report logic
        return Invoice::query()
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->with('client')
            ->get()
            ->toArray();
    }

    /**
     * Generate invoices per client report.
     *
     * @param string $fromDate Start date
     * @param string $toDate End date
     *
     * @return array Report results
     *
     * @legacy-function invoicesPerClient
     */
    public function invoicesPerClient(string $fromDate, string $toDate): array
    {
        // TODO: Implement invoices per client report logic
        return Invoice::query()
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->with('client')
            ->get()
            ->toArray();
    }

    /**
     * Generate payment history report.
     *
     * @param string $fromDate Start date
     * @param string $toDate End date
     *
     * @return array Report results
     *
     * @legacy-function paymentHistory
     */
    public function paymentHistory(string $fromDate, string $toDate): array
    {
        // TODO: Implement payment history report logic
        return Payment::query()
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->with(['invoice', 'paymentMethod'])
            ->get()
            ->toArray();
    }

    /**
     * Generate invoice aging report.
     *
     * @return array Report results
     *
     * @legacy-function invoiceAging
     */
    public function invoiceAging(): array
    {
        // TODO: Implement invoice aging report logic
        return Invoice::query()
            ->where('invoice_status_id', '!=', 4) // Not paid
            ->with('client')
            ->get()
            ->toArray();
    }

    /**
     * Generate sales by year report.
     *
     * @param string $fromDate Start date
     * @param string $toDate End date
     * @param int|null $minQuantity Minimum quantity filter
     * @param int|null $maxQuantity Maximum quantity filter
     * @param bool|null $checkboxTax Include tax in calculations
     *
     * @return array Report results
     *
     * @legacy-function salesByYear
     */
    public function salesByYear(
        string $fromDate,
        string $toDate,
        ?int $minQuantity = null,
        ?int $maxQuantity = null,
        ?bool $checkboxTax = null
    ): array {
        // TODO: Implement sales by year report logic
        $query = Invoice::query()
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->with(['client', 'items']);

        return $query->get()->toArray();
    }
}
