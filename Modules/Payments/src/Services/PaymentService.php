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
     * Validate a payment amount for an invoice.
     *
     * $invoiceId is optional to preserve backwards compatibility with older call sites;
     * when omitted the method returns false.
     *
     * @param mixed    $amount
     * @param int|null $invoiceId
     * @param int|null $paymentId
     *
     * @return bool
     */
    public function validatePaymentAmount($amount, ?int $invoiceId = null, ?int $paymentId = null): bool
    {
        $amount = (float) $amount;

        if ($invoiceId === null) {
            return false;
        }

        $invoiceRow = DB::table('ip_invoice_amounts')->where('invoice_id', $invoiceId)->first();
        if (! $invoiceRow) {
            return false;
        }

        $invoiceBalance = (float) $invoiceRow->invoice_balance;

        if ($paymentId !== null) {
            $existingPayment = DB::table('ip_payments')->where('payment_id', $paymentId)->first();
            if ($existingPayment) {
                $invoiceBalance += (float) $existingPayment->payment_amount;
            }
        }

        if ($amount > $invoiceBalance + 0.00001) {
            return false;
        }

        return true;
    }

    /**
     * Save a payment. If $id is provided it updates the existing payment, otherwise creates a new one.
     *
     * After saving it recalculates invoice amounts and flips invoice status to paid when appropriate.
     *
     * @param int|null $id
     * @param array|null $db_array
     *
     * @return int|null
     *
     * @throws Exception
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
     * Delete a payment and recalculate related invoice amounts/status.
     *
     * @param int|null $id
     *
     * @return bool
     *
     * @throws Exception
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
     * Prepare form defaults. Returns true to indicate preparation succeeded.
     *
     * @param int|null $id
     *
     * @return bool
     */
    public function prep_form($id = null): bool
    {
        return true;
    }

    /**
     * Scope convenience: filter payments by client id.
     *
     * @param int $clientId
     *
     * @return Builder
     */
    public function by_client(int $clientId): Builder
    {
        return Payment::query()->where('client_id', $clientId);
    }

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
