<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Entities\Setting;

/**
 * SettingsController
 * 
 * Manages application settings
 * Migrated from CodeIgniter Settings controller
 * 
 * NOTE: This is a simplified version. Full implementation requires:
 * - File upload handling for logos
 * - Encryption for password fields
 * - Database schema modifications for tax rates
 * - Integration with payment gateways config
 */
class SettingsController
{
    /**
     * Display and handle settings form
     */
    public function index()
    {
        // Handle form submission
        if (request()->isMethod('post') && request()->has('settings')) {
            $settings = request()->post('settings');
            
            // Save settings
            foreach ($settings as $key => $value) {
                // Skip meta fields
                if (str_contains($key, 'field_is_password') || str_contains($key, 'field_is_amount')) {
                    continue;
                }
                
                // Skip empty password fields
                if (isset($settings[$key . '_field_is_password']) && empty($value)) {
                    continue;
                }
                
                // TODO: Implement encryption for password fields
                // TODO: Implement amount standardization for amount fields
                
                // Save the setting
                Setting::setValue($key, $value);
            }
            
            // TODO: Handle file uploads for invoice_logo and login_logo
            
            session()->flash('alert_success', trans('settings_successfully_saved'));
            return redirect()->to(site_url('settings'));
        }
        
        // Load required data
        // TODO: Load payment gateways config
        $gateways = config('payment_gateways', []);
        
        // TODO: Load number formats config
        $numberFormats = config('number_formats', []);
        
        // Get all settings
        $allSettings = Setting::getAllSettings();
        
        // TODO: Load related models and data
        // - invoice_groups
        // - tax_rates
        // - payment_methods
        // - templates
        // - email_templates
        // - custom_fields
        
        $data = [
            'gateway_drivers' => $gateways,
            'number_formats' => $numberFormats,
            'languages' => get_available_languages(),
            'countries' => get_country_list(trans('cldr')),
            'date_formats' => date_formats(),
            'current_date' => new \DateTime(),
            'first_days_of_weeks' => [
                '0' => lang('sunday'),
                '1' => lang('monday'),
            ],
        ];
        
        return view('core::settings.index', $data);
    }

    /**
     * Remove a logo file
     *
     * @param string $type Logo type ('invoice' or 'login')
     */
    public function removeLogo(string $type)
    {
        $logoFile = get_setting($type . '_logo');
        
        if ($logoFile && file_exists('./uploads/' . $logoFile)) {
            unlink('./uploads/' . $logoFile);
        }
        
        Setting::setValue($type . '_logo', '');
        
        session()->flash('alert_success', lang($type . '_logo_removed'));
        
        return redirect()->to(site_url('settings'));
    }
}
