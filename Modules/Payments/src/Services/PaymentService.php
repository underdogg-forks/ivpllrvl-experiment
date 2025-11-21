<?php

namespace Modules\Payments\Services;

use Modules\Core\Services\BaseService;
use Modules\Payments\Models\Payment;

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
/*
        $amount     = (float) standardize_amount($amount);
        $invoice_id = $this->input->post('invoice_id');
        $payment_id = $this->input->post('payment_id');

        $invoice = $this->db->where('invoice_id', $invoice_id)->get('ip_invoice_amounts')->row();

        if ($invoice == null) {
            return false;
        }

        $invoice_balance = (float) $invoice->invoice_balance;

        if ($payment_id) {
            $payment = $this->db->where('payment_id', $payment_id)->get('ip_payments')->row();

            $invoice_balance += (float) $payment->payment_amount;
        }

        if ($amount > $invoice_balance) {
            $this->form_validation->set_message('validate_payment_amount', trans('payment_cannot_exceed_balance'));

            return false;
        }

        return true;
*/
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
/*
        $db_array = ($db_array) ? $db_array : $this->db_array();
        $this->load->model('invoices/invoice_amount');

        // Save the payment
        $id = parent::save($id, $db_array);

        $global_discount['item'] = $this->mdl_invoice_amounts->get_global_discount($db_array['invoice_id']);
        // Recalculate invoice amounts
        $this->mdl_invoice_amounts->calculate($db_array['invoice_id'], $global_discount);

        // Set proper status for the invoice
        $invoice = $this->db->where('invoice_id', $db_array['invoice_id'])->get('ip_invoice_amounts')->row();

        if ($invoice == null) {
            return false;
        }

        // Calculate sum for payments
        $paid  = (float) $invoice->invoice_paid;
        $total = (float) $invoice->invoice_total;

        if ($paid >= $total) {
            $this->db->where('invoice_id', $db_array['invoice_id']);
            $this->db->set('invoice_status_id', 4);
            $this->db->update('ip_invoices');
        }

        $global_discount['item'] = $this->mdl_invoice_amounts->get_global_discount($db_array['invoice_id']);
        // Recalculate invoice amounts
        $this->mdl_invoice_amounts->calculate($db_array['invoice_id'], $global_discount);

        return $id;
*/
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
/*
        // Get the invoice id before deleting payment
        $this->db->select('invoice_id');
        $this->db->where('payment_id', $id);

        $invoice_id = $this->db->get('ip_payments')->row()->invoice_id;

        // Delete the payment
        parent::delete($id);

        $this->load->model('invoices/invoice_amount');
        $global_discount['item'] = $this->mdl_invoice_amounts->get_global_discount($invoice_id);
        // Recalculate invoice amounts
        $this->mdl_invoice_amounts->calculate($invoice_id, $global_discount);

        // Change invoice status back to sent
        $this->db->select('invoice_status_id');
        $this->db->where('invoice_id', $invoice_id);

        $invoice = $this->db->get('ip_invoices')->row();

        if ($invoice->invoice_status_id == 4) {
            $this->db->where('invoice_id', $invoice_id);
            $this->db->set('invoice_status_id', 2);
            $this->db->update('ip_invoices');
        }

        $this->load->helper('orphan');
        delete_orphans();
*/
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
/*
        if ( ! parent::prep_form($id)) {
            return false;
        }

        if ( ! $id) {
            parent::set_form_value('payment_date', date('Y-m-d'));
        }

        return true;
*/
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
/*
        $this->filter_where('ip_clients.client_id', $client_id);

        return $this;
*/
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Payment::class;
    }
}
