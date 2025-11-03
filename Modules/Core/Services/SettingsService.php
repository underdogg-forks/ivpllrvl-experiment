<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Setting;

/**
 * SettingsService.
 *
 * Service class for managing application settings
 *
 * @legacy-file application/modules/settings/models/Mdl_settings.php (inferred)
 */
class SettingsService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): ?string
    {
        return Setting::class;
    }

    /**
     * Get a setting value by key.
     *
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     *
     * @return mixed Setting value
     *
     * @legacy-function get
     */
    public function get(string $key, $default = null)
    {
        $setting = Setting::query()->where('setting_key', $key)->first();
        
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Save a setting value.
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     *
     * @return void
     *
     * @legacy-function save
     */
    public function save(string $key, $value): void
    {
        Setting::query()->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }

    /**
     * Get all settings as key-value pairs.
     *
     * @return array
     *
     * @legacy-function getAll
     */
    public function getAll(): array
    {
        return Setting::query()
            ->pluck('setting_value', 'setting_key')
            ->toArray();
    }
}
