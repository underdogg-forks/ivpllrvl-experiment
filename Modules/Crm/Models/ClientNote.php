<?php

namespace Modules\Crm\Models;

use Modules\Core\Models\BaseModel;

/**
 * ClientNote Model.
 *
 * Eloquent model for managing ip_client_notes
 * Migrated from CodeIgniter model
 */
class ClientNote extends BaseModel
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
    protected $table = 'ip_client_notes';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'client_note_id';

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
        'client_note_id' => 'integer',
        // TODO: Add more casts as needed
    ];

    /**
     * Get validation rules for client notes.
     *
     * @return array
     */
}
