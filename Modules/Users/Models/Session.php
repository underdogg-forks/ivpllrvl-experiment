<?php

namespace Modules\Users\Entities;

use Modules\Core\Models\BaseModel;

/**
 * Session Model
 * 
 * Eloquent model for managing unknown_table
 * Migrated from CodeIgniter model
 */
class Session extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'unknown_table';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

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
        'id' => 'integer',
        // TODO: Add more casts as needed
    ];

    /**
     * Get validation rules for sessions.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'user_id' => 'required|integer',
            'session_data' => 'nullable|string',
        ];
    }
}
