<?php

namespace Modules\Payments\Services;

use Modules\Core\Services\BaseService;
use Modules\Payments\Models\Payment;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceAmountService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
    public function getByClientId(int $clientId): \Illuminate\Database\Eloquent\Collection
    {
        return Payment::query()->where('client_id', $clientId)->get();
    }

    /**
     * Validate a payment amount does not exceed invoice balance.
     *
     * @param float $amount
     * @param int   $invoiceId
     * @param int|null $paymentId
     *
     * @return bool
     */
    public function validatePaymentAmount(float $amount, int $invoiceId, ?int $paymentId = null): bool
    {
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return false;
        }

        $invoiceBalance = (float) $invoice->balance;

        if ($paymentId) {
            $existingPayment = Payment::find($paymentId);
            if ($existingPayment) {
                $invoiceBalance += (float) $existingPayment->amount;
            }
        }

        if ($amount > $invoiceBalance) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('amount', __('payment_cannot_exceed_balance'));
            })->validate();

            return false;
        }

        return true;
    }

    /**
     * Save a payment and recalculate invoice totals/status.
     *
     * @param int|null   $id
     * @param array|null $data
     *
     * @return Payment|null
     */
    public function save(?int $id = null, ?array $data = null): ?Payment
    {
        $data = $data ?? [];

        return DB::transaction(function () use ($id, $data) {
            $payment = $id ? Payment::find($id) : new Payment();
            $payment->fill($data);
            $payment->save();

            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                InvoiceAmountService::recalculate($invoice->id);

                $paid  = $invoice->paid;
                $total = $invoice->total;

                if ($paid >= $total) {
                    $invoice->status_id = 4; // Paid
                    $invoice->save();
                }
            }

            return $payment;
        });
    }

    /**
     * Delete a payment and recalculate invoice totals/status.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $payment = Payment::find($id);
            if (!$payment) {
                return false;
            }

            $invoiceId = $payment->invoice_id;
            $payment->delete();

            InvoiceAmountService::recalculate($invoiceId);

            $invoice = Invoice::find($invoiceId);
            if ($invoice && $invoice->status_id === 4 && $invoice->paid < $invoice->total) {
                $invoice->status_id = 2; // Sent
                $invoice->save();
            }

            return true;
        });
    }

    /**
     * Prepare a new payment form.
     *
     * @param int|null $id
     *
     * @return Payment
     */
    public function prep_form(?int $id = null): Payment
    {
        $payment = $id ? Payment::findOrFail($id) : new Payment();
        if (!$id) {
            $payment->payment_date = now()->toDateString();
        }
        return $payment;
    }

    /**
     * Filter payments by client ID.
     *
     * @param int $clientId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function by_client(int $clientId)
    {
        return Payment::query()->whereHas('client', fn($q) => $q->where('id', $clientId));
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Payment::class;
    }
}
