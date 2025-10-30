<?php

namespace Modules\Users\Entities;

use Modules\Core\Models\BaseModel;

/**
 * User Model
 * 
 * Eloquent model for managing users
 * Migrated from CodeIgniter Mdl_Users
 */
class User extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

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
        'user_type',
        'user_name',
        'user_email',
        'user_psalt',
        'user_password',
        'user_passwordreset_token',
        'user_company',
        'user_address_1',
        'user_address_2',
        'user_city',
        'user_state',
        'user_zip',
        'user_country',
        'user_phone',
        'user_fax',
        'user_mobile',
        'user_web',
        'user_vat_id',
        'user_tax_code',
        'user_language',
        'user_all_clients',
        'user_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'user_password',
        'user_psalt',
        'user_passwordreset_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'user_type' => 'integer',
        'user_all_clients' => 'boolean',
        'user_active' => 'boolean',
    ];

    /**
     * Get the invoices created by the user.
     */
    public function invoices()
    {
        return $this->hasMany('Modules\Invoices\Entities\Invoice', 'user_id', 'user_id');
    }

    /**
     * Get the quotes created by the user.
     */
    public function quotes()
    {
        return $this->hasMany('Modules\Quotes\Entities\Quote', 'user_id', 'user_id');
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('user_active', 1);
    }

    /**
     * Scope a query to only include admin users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmin($query)
    {
        return $query->where('user_type', 1);
    }

    /**
     * Get validation rules for users.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'user_type' => 'required|integer',
            'user_name' => 'required|string|max:255',
            'user_company' => 'nullable|string|max:255',
            'user_address_1' => 'nullable|string|max:255',
            'user_address_2' => 'nullable|string|max:255',
            'user_city' => 'nullable|string|max:255',
            'user_state' => 'nullable|string|max:255',
            'user_zip' => 'nullable|string|max:50',
            'user_country' => 'nullable|string|max:255',
            'user_phone' => 'nullable|string|max:50',
            'user_fax' => 'nullable|string|max:50',
            'user_mobile' => 'nullable|string|max:50',
            'user_email' => 'required|email|max:255',
            'user_web' => 'nullable|url|max:255',
            'user_vat_id' => 'nullable|string|max:50',
            'user_tax_code' => 'nullable|string|max:50',
            'user_pswd' => 'required|string|min:6',
        ];
    }
}
