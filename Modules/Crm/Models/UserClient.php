<?php

namespace Modules\Crm\Entities;

use App\Models\BaseModel;

/**
 * UserClient Model
 * 
 * Eloquent model for managing ip_user_clients
 * Migrated from CodeIgniter model
 */
class UserClient extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_user_clients';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_client_id';

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
        'user_client_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    /**
     * Get validation rules for user-client assignments.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'user_id' => 'required|integer',
            'client_id' => 'required|integer',
        ];
    }
}
