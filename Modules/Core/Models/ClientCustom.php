<?php

namespace Modules\Core\Models;

/**
 * ClientCustom Model.
 *
 * Eloquent model for managing client custom fields
 * Migrated from CodeIgniter Mdl_Client_Custom model
 *
 * @property int $client_custom_id
 * @property int $client_id
 */
class ClientCustom extends BaseModel
{
    /**
     * Custom field positions for clients.
     */
    public static array $positions = [
        'custom_fields',
        'address',
        'contact_information',
        'personal_information',
        'tax_information',
    ];

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
    protected $table = 'ip_client_custom';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'client_custom_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'client_custom_id' => 'integer',
        'client_id'        => 'integer',
    ];

    /**
     * Get validation rules for client custom fields.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'client_id'          => 'required|integer',
            'custom_field_id'    => 'required|integer',
            'custom_field_value' => 'nullable|string',
        ];
    }

    /**
     * Get custom fields for a specific client.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int                                   $clientId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByClientId($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
