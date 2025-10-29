<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Entities\Setting;

class SettingsController
{
    /** @legacy-file application/modules/settings/controllers/Settings.php */
    public function index(): \Illuminate\View\View
    {
        $settings = Setting::all()->pluck('setting_value', 'setting_key')->toArray();
        return view('core::settings_index', ['settings' => $settings]);
    }

    public function save()
    {
        if (request()->isMethod('post')) {
            $settings = request()->except(['_token', 'btn_submit']);
            
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    ['setting_key' => $key],
                    ['setting_value' => $value]
                );
            }
            
            return redirect()->route('settings.index')->with('alert_success', trans('settings_successfully_saved'));
        }
        
        return redirect()->route('settings.index');
    }
}
