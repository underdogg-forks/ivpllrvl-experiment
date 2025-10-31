<?php

namespace Modules\Crm\Models;

use Modules\Core\Models\BaseModel;

/**
 * Client Model.
 *
 * Eloquent model for managing clients
 * Migrated from CodeIgniter Mdl_Clients
 */
class Client extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
        'client_id'     => 'integer',
        'client_active' => 'boolean',
    ];

    /**
     * Get validation rules for clients.
     *
     * @return array
     */

    /**
     * Get the invoices for the client.
     */
    public function invoices()
    {
        return $this->hasMany('Modules\Invoices\Models\Invoice', 'client_id', 'client_id');
    }

    /**
     * Get the quotes for the client.
     */
    public function quotes()
    {
        return $this->hasMany('Modules\Quotes\Models\Quote', 'client_id', 'client_id');
    }

    /**
     * Get the payments for the client.
     */
    public function payments()
    {
        return $this->hasManyThrough(
            'Modules\Payments\Models\Payment',
            'Modules\Invoices\Models\Invoice',
            'client_id',
            'invoice_id',
            'client_id',
            'invoice_id'
        );
    }

    /**
     * Scope a query to only include active clients.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
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
        return mb_trim($this->client_name . ' ' . $this->client_surname);
    }
}
