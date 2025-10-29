<?php

namespace Modules\Core\Entities;

use App\Models\BaseModel;

/**
 * User_custom Model
 * 
 * Eloquent model for managing user custom fields
 * Migrated from CodeIgniter Mdl_User_Custom model
 * 
 * @property int $user_custom_id
 * @property int $user_id
 */
class User_custom extends BaseModel
{
    /**
     * Custom field positions for users
     */
    public static array $positions = [
        'custom_fields',
        'after_email',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_user_custom';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_custom_id';

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
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'user_custom_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Get custom fields for a specific user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUserId($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
