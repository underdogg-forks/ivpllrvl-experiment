<?php

namespace Modules\Quotes\Entities;

use App\Models\BaseModel;

/**
 * Quote_item Model
 * 
 * Eloquent model for managing ip_quote_items
 * Migrated from CodeIgniter model
 */
class Quote_item extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_quote_items';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'item_id';

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
        'item_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    // TODO: Add relationships, scopes, and methods from original model
}
