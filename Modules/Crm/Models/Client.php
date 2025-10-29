<?php

namespace Modules\Crm\Entities;

use App\Models\BaseModel;

/**
 * Client Model
 * 
 * Eloquent model for managing clients
 * Migrated from CodeIgniter Mdl_Clients
 */
class Client extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_clients';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'client_id';

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
        'client_name',
        'client_surname',
        'client_email',
        'client_phone',
        'client_mobile',
        'client_fax',
        'client_address_1',
        'client_address_2',
        'client_city',
        'client_state',
        'client_zip',
        'client_country',
        'client_tax_code',
        'client_vat_id',
        'client_active',
        'client_date_created',
        'client_date_modified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'client_id' => 'integer',
        'client_active' => 'boolean',
    ];

    /**
     * Get the invoices for the client.
     */
    public function invoices()
    {
        return $this->hasMany('Modules\Invoices\Entities\Invoice', 'client_id', 'client_id');
    }

    /**
     * Get the quotes for the client.
     */
    public function quotes()
    {
        return $this->hasMany('Modules\Quotes\Entities\Quote', 'client_id', 'client_id');
    }

    /**
     * Get the payments for the client.
     */
    public function payments()
    {
        return $this->hasManyThrough(
            'Modules\Payments\Entities\Payment',
            'Modules\Invoices\Entities\Invoice',
            'client_id',
            'invoice_id',
            'client_id',
            'invoice_id'
        );
    }

    /**
     * Scope a query to only include active clients.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('client_active', 1);
    }

    /**
     * Get the client's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->client_name . ' ' . $this->client_surname);
    }

    /**
     * Get validation rules for clients.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'client_name' => 'required|string|max:255',
            'client_surname' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_address_1' => 'nullable|string|max:255',
            'client_address_2' => 'nullable|string|max:255',
            'client_city' => 'nullable|string|max:255',
            'client_state' => 'nullable|string|max:255',
            'client_zip' => 'nullable|string|max:50',
            'client_country' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_fax' => 'nullable|string|max:50',
            'client_mobile' => 'nullable|string|max:50',
            'client_web' => 'nullable|url|max:255',
            'client_vat_id' => 'nullable|string|max:50',
            'client_tax_code' => 'nullable|string|max:50',
            'client_language' => 'nullable|string|max:10',
            'client_active' => 'required|integer|in:0,1',
        ];
    }
}
