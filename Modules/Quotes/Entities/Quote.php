<?php

namespace Modules\Quotes\Entities;

use App\Models\BaseModel;
use Modules\Invoices\Entities\InvoiceGroup;
use Modules\Core\Entities\QuoteCustom;

/**
 * Quote Model
 * 
 * Eloquent model for managing ip_quotes
 * Migrated from CodeIgniter model
 */
class Quote extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_quotes';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'quote_id';

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
        'quote_number',
        'quote_date_created',
        'quote_date_modified',
        'quote_date_expires',
        'quote_status_id',
        'quote_password',
        'client_id',
        'user_id',
        'invoice_group_id',
        'quote_discount_amount',
        'quote_discount_percent',
        'quote_terms',
        'quote_url_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quote_id' => 'integer',
        'client_id' => 'integer',
        'user_id' => 'integer',
        'invoice_group_id' => 'integer',
        'quote_status_id' => 'integer',
        'quote_discount_amount' => 'decimal:2',
        'quote_discount_percent' => 'decimal:2',
    ];

    /**
     * Get the client that owns the quote.
     */
    public function client()
    {
        return $this->belongsTo('Modules\Crm\Entities\Client', 'client_id', 'client_id');
    }

    /**
     * Get the user that created the quote.
     */
    public function user()
    {
        return $this->belongsTo('Modules\Users\Entities\User', 'user_id', 'user_id');
    }

    /**
     * Get the invoice group.
     */
    public function invoiceGroup()
    {
        return $this->belongsTo('Modules\Invoices\Entities\InvoiceGroup', 'invoice_group_id', 'invoice_group_id');
    }

    /**
     * Get the quote amounts.
     */
    public function amounts()
    {
        return $this->hasOne('Modules\Quotes\Entities\QuoteAmount', 'quote_id', 'quote_id');
    }

    /**
     * Get the quote items.
     */
    public function items()
    {
        return $this->hasMany('Modules\Quotes\Entities\QuoteItem', 'quote_id', 'quote_id');
    }

    /**
     * Get the quote tax rates.
     */
    public function taxRates()
    {
        return $this->hasMany('Modules\Quotes\Entities\QuoteTaxRate', 'quote_id', 'quote_id');
    }

    /**
     * Scope a query to only include quotes with a given status.
     */
    public function scopeByStatus($query, int $statusId)
    {
        return $query->where('quote_status_id', $statusId);
    }

    /**
     * Scope a query to only include draft quotes.
     */
    public function scopeDraft($query)
    {
        return $query->where('quote_status_id', 1);
    }

    /**
     * Scope a query to only include sent quotes.
     */
    public function scopeSent($query)
    {
        return $query->where('quote_status_id', 2);
    }

    /**
     * Scope a query to only include approved quotes.
     */
    public function scopeApproved($query)
    {
        return $query->where('quote_status_id', 4);
    }

    /**
     * Scope a query to only include viewed quotes.
     */
    public function scopeViewed($query)
    {
        return $query->where('quote_status_id', 3);
    }

    /**
     * Scope a query to only include rejected quotes.
     */
    public function scopeRejected($query)
    {
        return $query->where('quote_status_id', 5);
    }

    /**
     * Scope a query to only include canceled quotes.
     */
    public function scopeCanceled($query)
    {
        return $query->where('quote_status_id', 6);
    }

    /**
     * Scope for open quotes (sent or viewed - used by guest module).
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('quote_status_id', [2, 3]);
    }

    /**
     * Scope for guest-visible quotes.
     */
    public function scopeGuestVisible($query)
    {
        return $query->whereIn('quote_status_id', [2, 3, 4, 5]);
    }

    /**
     * Scope to filter quotes by client.
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Get quote statuses.
     *
     * @return array
     */
    public static function statuses(): array
    {
        return [
            '1' => [
                'label' => trans('draft'),
                'class' => 'draft',
                'href'  => 'quotes/status/draft',
            ],
            '2' => [
                'label' => trans('sent'),
                'class' => 'sent',
                'href'  => 'quotes/status/sent',
            ],
            '3' => [
                'label' => trans('viewed'),
                'class' => 'viewed',
                'href'  => 'quotes/status/viewed',
            ],
            '4' => [
                'label' => trans('approved'),
                'class' => 'approved',
                'href'  => 'quotes/status/approved',
            ],
            '5' => [
                'label' => trans('rejected'),
                'class' => 'rejected',
                'href'  => 'quotes/status/rejected',
            ],
            '6' => [
                'label' => trans('canceled'),
                'class' => 'canceled',
                'href'  => 'quotes/status/canceled',
            ],
        ];
    }

    /**
     * Get validation rules for creating a quote.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'client_id' => 'required|integer',
            'quote_date_created' => 'required|date',
            'invoice_group_id' => 'required|integer',
            'quote_password' => 'nullable|string',
            'user_id' => 'required|integer',
        ];
    }

    /**
     * Get validation rules for saving a quote.
     *
     * @param int|null $quoteId
     * @return array
     */
    public static function validationRulesSaveQuote(?int $quoteId = null): array
    {
        $uniqueRule = 'unique:ip_quotes,quote_number';
        if ($quoteId) {
            $uniqueRule .= ',' . $quoteId . ',quote_id';
        }

        return [
            'quote_number' => $uniqueRule,
            'quote_date_created' => 'required|date',
            'quote_date_expires' => 'required|date',
            'quote_password' => 'nullable|string',
        ];
    }

    /**
     * Create a new quote with associated records.
     *
     * @param array $data
     * @return Quote
     */
    public static function createQuote(array $data): Quote
    {
        // Create the quote
        $quote = static::create($data);

        // Create quote amount record
        QuoteAmount::create([
            'quote_id' => $quote->quote_id,
        ]);

        // Create default quote tax rate if applicable
        $defaultTaxRate = get_setting('default_invoice_tax_rate');
        if ($defaultTaxRate) {
            QuoteTaxRate::create([
                'quote_id' => $quote->quote_id,
                'tax_rate_id' => $defaultTaxRate,
                'include_item_tax' => get_setting('default_include_item_tax'),
                'quote_tax_rate_amount' => 0,
            ]);
        }

        return $quote;
    }

    /**
     * Copy quote items, tax rates, and custom fields from source to target.
     *
     * @param int $sourceId
     * @param int $targetId
     * @return void
     */
    public static function copyQuote(int $sourceId, int $targetId): void
    {
        $sourceQuote = static::with(['items', 'taxRates'])->findOrFail($sourceId);

        // Calculate global discount
        $itemsSubtotal = QuoteItem::where('quote_id', $sourceId)->sum('item_subtotal');
        $globalDiscount = [
            'amount' => $sourceQuote->quote_discount_amount,
            'percent' => $sourceQuote->quote_discount_percent,
            'item' => 0.0,
            'items_subtotal' => $itemsSubtotal,
        ];

        // Update target quote with discount
        static::where('quote_id', $targetId)->update([
            'quote_discount_percent' => $globalDiscount['percent'],
            'quote_discount_amount' => $globalDiscount['amount'],
        ]);

        // Copy quote items
        foreach ($sourceQuote->items as $item) {
            QuoteItem::create([
                'quote_id' => $targetId,
                'item_tax_rate_id' => $item->item_tax_rate_id,
                'item_product_id' => $item->item_product_id,
                'item_name' => $item->item_name,
                'item_description' => $item->item_description,
                'item_quantity' => $item->item_quantity,
                'item_price' => $item->item_price,
                'item_discount_amount' => $item->item_discount_amount,
                'item_order' => $item->item_order,
                'item_product_unit' => $item->item_product_unit,
                'item_product_unit_id' => $item->item_product_unit_id,
            ]);
        }

        // Copy tax rates
        foreach ($sourceQuote->taxRates as $taxRate) {
            QuoteTaxRate::create([
                'quote_id' => $targetId,
                'tax_rate_id' => $taxRate->tax_rate_id,
                'include_item_tax' => $taxRate->include_item_tax,
                'quote_tax_rate_amount' => $taxRate->quote_tax_rate_amount,
            ]);
        }

        // Copy custom fields
        $sourceCustom = QuoteCustom::where('quote_id', $sourceId)->first();
        if ($sourceCustom) {
            $customData = $sourceCustom->toArray();
            unset($customData['quote_custom_id']);
            $customData['quote_id'] = $targetId;
            QuoteCustom::create($customData);
        }
    }

    /**
     * Get the due date based on creation date.
     *
     * @param string $quoteDateCreated
     * @return string
     */
    public static function getDateDue(string $quoteDateCreated): string
    {
        $expiresAfter = get_setting('quotes_expire_after');
        $expiryDate = new \DateTime($quoteDateCreated);
        $expiryDate->add(new \DateInterval('P' . $expiresAfter . 'D'));

        return $expiryDate->format('Y-m-d');
    }

    /**
     * Generate a quote number.
     *
     * @param int $invoiceGroupId
     * @return string
     */
    public static function getQuoteNumber(int $invoiceGroupId): string
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
     * Get invoice group ID for a quote.
     *
     * @param int $quoteId
     * @return int
     */
    public static function getInvoiceGroupId(int $quoteId): int
    {
        $quote = static::findOrFail($quoteId);
        return $quote->invoice_group_id;
    }

    /**
     * Delete quote and cleanup orphans.
     *
     * @param int $quoteId
     * @return bool|null
     */
    public static function deleteQuote(int $quoteId): ?bool
    {
        $quote = static::findOrFail($quoteId);
        $deleted = $quote->delete();

        // Cleanup orphaned records
        QuoteAmount::where('quote_id', $quoteId)->delete();
        QuoteItem::where('quote_id', $quoteId)->delete();
        QuoteTaxRate::where('quote_id', $quoteId)->delete();
        QuoteCustom::where('quote_id', $quoteId)->delete();

        return $deleted;
    }

    /**
     * Approve quote by URL key.
     *
     * @param string $quoteUrlKey
     * @return int
     */
    public static function approveQuoteByKey(string $quoteUrlKey): int
    {
        return static::whereIn('quote_status_id', [2, 3])
            ->where('quote_url_key', $quoteUrlKey)
            ->update(['quote_status_id' => 4]);
    }

    /**
     * Reject quote by URL key.
     *
     * @param string $quoteUrlKey
     * @return int
     */
    public static function rejectQuoteByKey(string $quoteUrlKey): int
    {
        return static::whereIn('quote_status_id', [2, 3])
            ->where('quote_url_key', $quoteUrlKey)
            ->update(['quote_status_id' => 5]);
    }

    /**
     * Approve quote by ID.
     *
     * @param int $quoteId
     * @return int
     */
    public static function approveQuoteById(int $quoteId): int
    {
        return static::whereIn('quote_status_id', [2, 3])
            ->where('quote_id', $quoteId)
            ->update(['quote_status_id' => 4]);
    }

    /**
     * Reject quote by ID.
     *
     * @param int $quoteId
     * @return int
     */
    public static function rejectQuoteById(int $quoteId): int
    {
        return static::whereIn('quote_status_id', [2, 3])
            ->where('quote_id', $quoteId)
            ->update(['quote_status_id' => 5]);
    }

    /**
     * Mark quote as viewed (only if currently sent).
     *
     * @param int $quoteId
     * @return bool
     */
    public static function markViewed(int $quoteId): bool
    {
        $quote = static::select('quote_status_id')
            ->where('quote_id', $quoteId)
            ->first();

        if ($quote && $quote->quote_status_id == 2) {
            return static::where('quote_id', $quoteId)
                ->update(['quote_status_id' => 3]) > 0;
        }

        return false;
    }

    /**
     * Mark quote as sent (only if currently draft).
     *
     * @param int $quoteId
     * @return bool
     */
    public static function markSent(int $quoteId): bool
    {
        $quote = static::select('quote_status_id')
            ->where('quote_id', $quoteId)
            ->first();

        if ($quote && $quote->quote_status_id == 1) {
            return static::where('quote_id', $quoteId)
                ->update(['quote_status_id' => 2]) > 0;
        }

        return false;
    }

    /**
     * Generate quote number if applicable.
     *
     * @param int $quoteId
     * @return void
     */
    public static function generateQuoteNumberIfApplicable(int $quoteId): void
    {
        $quote = static::findOrFail($quoteId);

        // Generate new quote number if draft with no number and setting is off
        $generateForDraft = get_setting('generate_quote_number_for_draft');
        if ($quote->quote_status_id == 1 && empty($quote->quote_number) && $generateForDraft == 0) {
            $quoteNumber = static::getQuoteNumber($quote->invoice_group_id);
            static::where('quote_id', $quoteId)
                ->update(['quote_number' => $quoteNumber]);
        }
    }
}
