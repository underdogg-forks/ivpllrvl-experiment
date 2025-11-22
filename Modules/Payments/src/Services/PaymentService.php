<?php

namespace Modules\Payments\Services;

use Modules\Core\Services\BaseService;
use Modules\Payments\Models\Payment;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Invoices\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * PaymentService.
 *
 * Service class for managing payment business logic
 */
class PaymentService extends BaseService
{
    public function findWithRelations(int $id, array $relations = ['invoice', 'paymentMethod']): ?Payment
    {
        return Payment::query()->with($relations)->find($id);
    }

    public function getAllWithRelations(array $relations = ['invoice', 'paymentMethod'], int $perPage = 15): LengthAwarePaginator
    {
        return Payment::query()->with($relations)
            ->orderBy('payment_date', 'desc')
            ->paginate($perPage);
    }

    public function getByClientId(int $clientId): Collection
    {
        return Payment::query()->where('client_id', $clientId)->get();
    }

    /**
     * @param $amount
     *
     * @return bool
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/payments/models/Mdl_payment.php
     *
     * @legacy-function validate_payment_amount()
     */
    public function validatePaymentAmount($amount): bool
    {
        $amount = (float) $amount;

        $invoiceId = request()->post('invoice_id');
        $paymentId = request()->post('payment_id');

        $invoice = InvoiceAmount::query()->find($invoiceId);

        if (! $invoice) {
            return false;
        }

        $invoiceBalance = (float) $invoice->invoice_balance;

        if ($paymentId) {
            $payment = Payment::query()->find($paymentId);
            $invoiceBalance += $payment ? (float) $payment->payment_amount : 0;
        }

        if ($amount > $invoiceBalance) {
            session()->flash('error', trans('payment_cannot_exceed_balance'));
            return false;
        }

        return true;
    }

    /**
     * @return bool|int|null
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/payments/models/Mdl_payment.php
     *
     * @legacy-function save()
     */
    public function save($id = null, $db_array = null)
    {
        $db_array = $db_array ?: $this->db_array();

        $id = parent::save($id, $db_array);

        $globalDiscount = InvoiceAmount::getGlobalDiscount($db_array['invoice_id']);
        InvoiceAmount::calculate($db_array['invoice_id'], ['item' => $globalDiscount]);

        $invoice = InvoiceAmount::query()->find($db_array['invoice_id']);
        if (! $invoice) {
            return false;
        }

        $paid  = (float) $invoice->invoice_paid;
        $total = (float) $invoice->invoice_total;

        if ($paid >= $total) {
            Invoice::query()->where('invoice_id', $db_array['invoice_id'])
                ->update(['invoice_status_id' => 4]);
        }

        InvoiceAmount::calculate($db_array['invoice_id'], ['item' => $globalDiscount]);

        return $id;
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/payments/models/Mdl_payment.php
     *
     * @legacy-function delete()
     */
    public function delete($id = null)
    {
        $payment = Payment::query()->find($id);
        if (! $payment) {
            return;
        }

        $invoiceId = $payment->invoice_id;

        parent::delete($id);

        $globalDiscount = InvoiceAmount::getGlobalDiscount($invoiceId);
        InvoiceAmount::calculate($invoiceId, ['item' => $globalDiscount]);

        $invoice = Invoice::query()->find($invoiceId);
        if ($invoice && $invoice->invoice_status_id === 4) {
            $invoice->update(['invoice_status_id' => 2]);
        }

        // Delete orphaned records
        \App\Helpers\Orphan::deleteOrphans();
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/payments/models/Mdl_payment.php
     *
     * @legacy-function prep_form()
     */
    public function prep_form($id = null): bool
    {
        if (! parent::prep_form($id)) {
            return false;
        }

        if (! $id) {
            parent::set_form_value('payment_date', date('Y-m-d'));
        }

        return true;
    }

    /**
     * @param $client_id
     *
     * @return $this
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/payments/models/Mdl_payment.php
     *
     * @legacy-function by_client()
     */
    public function by_client($client_id)
    {
        $this->query()->whereHas('invoice.client', function ($query) use ($client_id) {
            $query->where('client_id', $client_id);
        });

        return $this;
    }

    protected function getModelClass(): string
    {
        return Payment::class;
    }
}
