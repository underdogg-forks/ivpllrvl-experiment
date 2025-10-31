<?php

namespace Modules\Core\Controllers;

use Modules\Core\Http\Requests\SettingsRequest;
use Modules\Core\Models\Setting;

class SettingsController
{
    /** @legacy-file application/modules/settings/controllers/Settings.php */
    public function index(): \Illuminate\View\View
    {
        $settings = Setting::query()->get()->pluck('setting_value', 'setting_key')->toArray();

        return view('core::settings_index', ['settings' => $settings]);
    }

    public function save(SettingsRequest $request)
    {
        if (request()->isMethod('post')) {
            $settings = $request->getAllowedSettings();

            foreach ($settings as $key => $value) {
                Setting::query()->updateOrCreate(
                    ['setting_key' => $key],
                    ['setting_value' => $value]
                );
            }

            return redirect()->route('settings.index')->with('alert_success', trans('settings_successfully_saved'));
        }

        return redirect()->route('settings.index');
    }
}
