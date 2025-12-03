<?php

namespace Modules\Core\Models;

/**
 * Setting Model.
 *
 * Eloquent model for managing application settings
 * Migrated from CodeIgniter Mdl_Settings model
 *
 * @property int    $setting_id
 * @property string $setting_key
 * @property string $setting_value
 */
class Setting extends BaseModel
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
    protected $table = 'ip_settings';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'setting_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'setting_id' => 'integer',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     *
     * @return string|null
     */
    public static function getValue(string $key): ?string
    {
        $setting = static::where('setting_key', $key)->first();

        return $setting ? $setting->setting_value : null;
    }

    /**
     * Save or update a setting.
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public static function setValue(string $key, string $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }

    /**
     * Delete a setting by key.
     *
     * @param string $key
     *
     * @return void
     */
    public static function deleteByKey(string $key): void
    {
        static::where('setting_key', $key)->delete();
    }

    /**
     * Get all settings as key-value array.
     *
     * @return array
     */
    public static function getAllSettings(): array
    {
        return static::all()->pluck('setting_value', 'setting_key')->toArray();
    }

    /**
     * Get validation rules for settings.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'setting_key'   => 'required|string|max:255',
            'setting_value' => 'nullable|string',
        ];
    }
}
