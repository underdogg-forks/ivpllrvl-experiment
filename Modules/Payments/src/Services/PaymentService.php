<?php

namespace Modules\Payments\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Payments\Models\Payment;
use InvalidArgumentException;
use Exception;

/**
 * PaymentService.
 *
 * Service class for managing payment business logic
 */
class PaymentService extends BaseService
{
    /**
     * Get a payment with its relationships.
     *
     * @param int   $id        Payment ID
     * @param array $relations Relations to eager load (default: invoice, paymentMethod)
     *
     * @return Payment|null
     */
    public function findWithRelations(int $id, array $relations = ['invoice', 'paymentMethod']): ?Payment
    {
        return Payment::query()->with($relations)->find($id);
    }

    /**
     * Get all payments with relationships, ordered by date descending.
     *
     * @param array $relations Relations to eager load (default: invoice, paymentMethod)
     * @param int   $perPage   Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['invoice', 'paymentMethod'], int $perPage = 15)
    {
        return Payment::query()->with($relations)
            ->orderBy('payment_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all payments for a specific client.
     *
     * @param int $clientId Client ID
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
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
    public function validatePaymentAmount($amount)
    {
        // To preserve original signature we accept the single $amount param.
        // If callers supply invoice_id/payment_id, they should use the other helper or we can adapt callers.
        return false;
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
        $data = $db_array ?? [];

        if (empty($data['invoice_id'])) {
            throw new InvalidArgumentException('invoice_id is required when saving a payment.');
        }

        DB::beginTransaction();

        try {
            if ($id !== null) {
                $payment = Payment::query()->findOrFail($id);
                $payment->fill($data);
                $payment->save();
                $savedId = $payment->payment_id;
            } else {
                $payment = Payment::create($data);
                $savedId = $payment->payment_id;
            }

            $this->recalculateInvoiceAmounts((int) $data['invoice_id']);
            DB::commit();

            return $savedId;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to save payment: ' . $e->getMessage());
        }
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
        if ($id === null) {
            return false;
        }

        $payment = Payment::query()->find($id);
        if (! $payment) {
            return false;
        }

        $invoiceId = (int) $payment->invoice_id;

        DB::beginTransaction();

        try {
            $deleted = Payment::query()->where('payment_id', $id)->delete();
            $this->recalculateInvoiceAmounts($invoiceId);
            DB::commit();

            return (bool) $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to delete payment: ' . $e->getMessage());
        }
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
        return Payment::query()->where('client_id', $client_id);
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Payment::class;
    }

    private function recalculateInvoiceAmounts(int $invoiceId): void
    {
        $paid = (float) DB::table('ip_payments')
            ->where('invoice_id', $invoiceId)
            ->sum('payment_amount');

        $amountRow = DB::table('ip_invoice_amounts')
            ->where('invoice_id', $invoiceId)
            ->first();

        $total = $amountRow ? (float) $amountRow->invoice_total : 0.0;
        $balance = max(0.0, $total - $paid);

        if ($amountRow) {
            DB::table('ip_invoice_amounts')
                ->where('invoice_id', $invoiceId)
                ->update([
                    'invoice_paid'    => $paid,
                    'invoice_balance' => $balance,
                ]);
        }

        if ($total > 0) {
            $status = $paid >= $total ? 4 : 2;
            DB::table('ip_invoices')
                ->where('invoice_id', $invoiceId)
                ->update(['invoice_status_id' => $status]);
        }
    }
}
