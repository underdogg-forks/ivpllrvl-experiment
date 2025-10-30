<?php

namespace Modules\Invoices\Models;

use Modules\Core\Models\BaseModel;

/**
 * Invoice Model
 *
 * Eloquent model for managing invoices
 * Migrated from CodeIgniter Mdl_Invoices
 */
class Invoice extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoices';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_number',
        'invoice_date_created',
        'invoice_date_modified',
        'invoice_date_due',
        'invoice_status_id',
        'invoice_password',
        'client_id',
        'user_id',
        'invoice_group_id',
        'invoice_discount_amount',
        'invoice_discount_percent',
        'invoice_terms',
        'invoice_url_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_id' => 'integer',
        'client_id' => 'integer',
        'user_id' => 'integer',
        'invoice_group_id' => 'integer',
        'invoice_status_id' => 'integer',
        'invoice_discount_amount' => 'decimal:2',
        'invoice_discount_percent' => 'decimal:2',
    ];

    /**
     * Get the client that owns the invoice.
     */
    public function client()
    {
        return $this->belongsTo('Modules\Crm\Models\Client', 'client_id', 'client_id');
    }

    /**
     * Get the user that created the invoice.
     */
    public function user()
    {
        return $this->belongsTo('Modules\Users\Models\User', 'user_id', 'user_id');
    }

    /**
     * Get the invoice group.
     */
    public function invoiceGroup()
    {
        return $this->belongsTo('Modules\Invoices\Models\InvoiceGroup', 'invoice_group_id', 'invoice_group_id');
    }

    /**
     * Get the invoice amounts.
     */
    public function amounts()
    {
        return $this->hasOne('Modules\Invoices\Models\InvoiceAmount', 'invoice_id', 'invoice_id');
    }

    /**
     * Get the invoice items.
     */
    public function items()
    {
        return $this->hasMany('Modules\Invoices\Models\InvoiceItem', 'invoice_id', 'invoice_id');
    }

    /**
     * Get the invoice tax rates.
     */
    public function taxRates()
    {
        return $this->hasMany('Modules\Invoices\Models\InvoiceTaxRate', 'invoice_id', 'invoice_id');
    }

    /**
     * Get the quote associated with this invoice.
     */
    public function quote()
    {
        return $this->hasOne('Modules\Quotes\Models\Quote', 'invoice_id', 'invoice_id');
    }

    /**
     * Scope a query to only include invoices with a given status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $statusId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, int $statusId)
    {
        return $query->where('invoice_status_id', $statusId);
    }

    /**
     * Scope a query to only include draft invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('invoice_status_id', 1);
    }

    /**
     * Scope a query to only include sent invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->where('invoice_status_id', 2);
    }

    /**
     * Scope a query to only include viewed invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeViewed($query)
    {
        return $query->where('invoice_status_id', 3);
    }

    /**
     * Scope a query to only include paid invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('invoice_status_id', 4);
    }

    /**
     * Scope a query to only include overdue invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotIn('invoice_status_id', [1, 4])
            ->whereRaw('DATEDIFF(NOW(), invoice_date_due) > 0');
    }

    /**
     * Get the invoice statuses.
     *
     * @return array
     */
    public static function statuses(): array
    {
        return [
            1 => [
                'label' => 'draft',
                'class' => 'draft',
                'href' => 'invoices/status/draft',
            ],
            2 => [
                'label' => 'sent',
                'class' => 'sent',
                'href' => 'invoices/status/sent',
            ],
            3 => [
                'label' => 'viewed',
                'class' => 'viewed',
                'href' => 'invoices/status/viewed',
            ],
            4 => [
                'label' => 'paid',
                'class' => 'paid',
                'href' => 'invoices/status/paid',
            ],
        ];
    }

    /**
     * Check if the invoice is overdue.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        if (in_array($this->invoice_status_id, [1, 4])) {
            return false;
        }

        $dueDate = new \DateTime($this->invoice_date_due);
        $now = new \DateTime();

        return $now > $dueDate;
    }

    /**
     * Get the number of days overdue.
     *
     * @return int
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $dueDate = new \DateTime($this->invoice_date_due);
        $now = new \DateTime();

        return $now->diff($dueDate)->days;
    }

    /**
     * Get validation rules for creating an invoice.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'client_id' => 'required|integer',
            'invoice_date_created' => 'required|date',
            'invoice_group_id' => 'required|integer',
            'invoice_password' => 'nullable|string',
            'user_id' => 'required|integer',
        ];
    }

    /**
     * Get validation rules for saving an invoice.
     *
     * @param int|null $invoiceId
     * @return array
     */
    public static function validationRulesSaveInvoice(?int $invoiceId = null): array
    {
        $uniqueRule = 'unique:ip_invoices,invoice_number';
        if ($invoiceId) {
            $uniqueRule .= ',' . $invoiceId . ',invoice_id';
        }

        return [
            'invoice_number' => $uniqueRule,
            'invoice_date_created' => 'required|date',
            'invoice_date_due' => 'required|date',
            'invoice_password' => 'nullable|string',
        ];
    }

    /**
     * Get the due date based on creation date.
     *
     * @param string $invoiceDateCreated
     * @return string
     */
    public static function getDateDue(string $invoiceDateCreated): string
    {
        $dueAfter = get_setting('invoices_due_after');
        $dueDate = new \DateTime($invoiceDateCreated);
        $dueDate->add(new \DateInterval('P' . $dueAfter . 'D'));

        return $dueDate->format('Y-m-d');
    }

    /**
     * Generate an invoice number.
     *
     * @param int $invoiceGroupId
     * @return string
     */
    public static function getInvoiceNumber(int $invoiceGroupId): string
    {
        $invoiceGroup = InvoiceGroup::findOrFail($invoiceGroupId);
        return $invoiceGroup->generateInvoiceNumber();
    }

    /**
     * Generate a unique URL key.
     *
     * @return string
     */
    public static function getUrlKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Get invoice group ID for an invoice.
     *
     * @param int $invoiceId
     * @return int
     */
    public static function getInvoiceGroupId(int $invoiceId): int
    {
        $invoice = static::findOrFail($invoiceId);
        return $invoice->invoice_group_id;
    }

    /**
     * Get parent invoice number.
     *
     * @param int $parentInvoiceId
     * @return string
     */
    public static function getParentInvoiceNumber(int $parentInvoiceId): string
    {
        $parentInvoice = static::findOrFail($parentInvoiceId);
        return $parentInvoice->invoice_number;
    }

    /**
     * Delete invoice and cleanup orphans.
     *
     * @param int $invoiceId
     * @return bool|null
     */
    public static function deleteInvoice(int $invoiceId): ?bool
    {
        $invoice = static::findOrFail($invoiceId);
        $deleted = $invoice->delete();

        // Cleanup orphaned records
        InvoiceAmount::where('invoice_id', $invoiceId)->delete();
        Item::where('invoice_id', $invoiceId)->delete();
        InvoiceTaxRate::where('invoice_id', $invoiceId)->delete();

        return $deleted;
    }

    /**
     * Mark invoice as viewed (only if currently sent).
     *
     * @param int $invoiceId
     * @return bool
     */
    public static function markViewed(int $invoiceId): bool
    {
        $invoice = static::select('invoice_status_id')
            ->where('invoice_id', $invoiceId)
            ->first();

        if ($invoice && $invoice->invoice_status_id == 2) {
            return static::where('invoice_id', $invoiceId)
                ->update(['invoice_status_id' => 3]) > 0;
        }

        return false;
    }

    /**
     * Mark invoice as sent (only if currently draft).
     *
     * @param int $invoiceId
     * @return bool
     */
    public static function markSent(int $invoiceId): bool
    {
        $invoice = static::select('invoice_status_id')
            ->where('invoice_id', $invoiceId)
            ->first();

        if ($invoice && $invoice->invoice_status_id == 1) {
            return static::where('invoice_id', $invoiceId)
                ->update(['invoice_status_id' => 2]) > 0;
        }

        return false;
    }

    /**
     * Generate invoice number if applicable.
     *
     * @param int $invoiceId
     * @return void
     */
    public static function generateInvoiceNumberIfApplicable(int $invoiceId): void
    {
        $invoice = static::findOrFail($invoiceId);

        // Generate new invoice number if draft with no number and setting is off
        $generateForDraft = get_setting('generate_invoice_number_for_draft');
        if ($invoice->invoice_status_id == 1 && empty($invoice->invoice_number) && $generateForDraft == 0) {
            $invoiceNumber = static::getInvoiceNumber($invoice->invoice_group_id);
            static::where('invoice_id', $invoiceId)
                ->update(['invoice_number' => $invoiceNumber]);
        }
    }

    /**
     * Scope for guest-visible invoices.
     */
    public function scopeGuestVisible($query)
    {
        return $query->whereIn('invoice_status_id', [2, 3, 4]);
    }

    /**
     * Scope to filter invoices by client.
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for open invoices.
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('invoice_status_id', [1, 4]);
    }

    /**
     * Scope for SUMEX invoices.
     */
    public function scopeSumex($query)
    {
        return $query->whereHas('sumex');
    }
}
