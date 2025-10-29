<?php

namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

/**
 * Invoice_group Model
 * 
 * Eloquent model for managing ip_invoice_groups
 * Migrated from CodeIgniter model
 */
class Invoice_group extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoice_groups';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_group_id';

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
        // TODO: Add fillable fields from validation_rules or db schema
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_group_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
