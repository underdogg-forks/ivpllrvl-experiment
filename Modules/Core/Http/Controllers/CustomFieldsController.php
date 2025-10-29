<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Entities\CustomField;
use Modules\Core\Entities\CustomValue;

/**
 * CustomFieldsController
 * 
 * Manages custom field definitions
 * Migrated from CodeIgniter Custom_Fields controller
 */
class CustomFieldsController
{
    /**
     * Display all custom fields (redirects to default view)
     */
    public function index()
    {
        return redirect()->to(site_url('custom_fields/table/all'));
    }

    /**
     * Display custom fields for a specific table or all tables
     *
     * @param string $name Table name (simple name like 'client', 'invoice', etc.) or 'all'
     * @param int $page Page number for pagination
     */
    public function table(string $name = 'all', int $page = 0)
    {
        $perPage = 15;
        $query = CustomField::query();
        
        // Filter by table if not 'all'
        $customTables = CustomField::customTables();
        if ($name != 'all' && in_array($name, $customTables)) {
            $query->byTableName($name);
        }
        
        // Order and paginate
        $customFields = $query->orderBy('custom_field_table')
            ->orderBy('custom_field_order')
            ->orderBy('custom_field_label')
            ->paginate($perPage, ['*'], 'page', $page);
        
        $positions = $this->getPositions();
        $customValueFields = CustomValue::customValueFields();
        
        $data = [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_custom_fields'),
            'filter_method' => 'filter_custom_fields',
            'custom_fields' => $customFields,
            'custom_tables' => $customTables,
            'custom_value_fields' => $customValueFields,
            'positions' => $positions,
        ];
        
        return view('core::custom_fields.index', $data);
    }

    /**
     * Show form to create or edit a custom field
     *
     * @param int|null $id Custom field ID (null for create)
     */
    public function form(?int $id = null)
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->to(site_url('custom_fields'));
        }
        
        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate
            $validated = request()->validate([
                'custom_field_table' => 'required',
                'custom_field_label' => 'required|max:50',
                'custom_field_type' => 'required',
                'custom_field_order' => 'nullable|integer',
                'custom_field_location' => 'nullable|integer',
            ]);
            
            // Prepare column name
            $label = $validated['custom_field_label'];
            if (strtolower($label) == 'id') {
                $validated['custom_field_column'] = 'field_id';
            } else {
                $validated['custom_field_column'] = strtolower(str_replace(' ', '_', $label));
            }
            
            // Save or update
            if ($id) {
                $customField = CustomField::findOrFail($id);
                $customField->update($validated);
            } else {
                CustomField::create($validated);
            }
            
            session()->flash('alert_success', trans($id ? 'record_successfully_updated' : 'record_successfully_created'));
            return redirect()->to(site_url('custom_fields'));
        }
        
        // Load existing field if editing
        $customField = null;
        if ($id) {
            $customField = CustomField::findOrFail($id);
        }
        
        $customFieldTables = CustomField::customTables();
        $customFieldTypes = CustomField::customTypes();
        $customFieldUsage = []; // TODO: Implement usage check
        $customFieldLocation = $customField->custom_field_location ?? 0;
        $positions = $this->getPositions();
        
        $data = [
            'custom_field_id' => $id,
            'custom_field_tables' => $customFieldTables,
            'custom_field_types' => $customFieldTypes,
            'custom_field_usage' => $customFieldUsage,
            'custom_field_location' => $customFieldLocation,
            'positions' => $positions,
        ];
        
        // Add form values if editing
        if ($customField) {
            $data['custom_field'] = $customField;
        }
        
        return view('core::custom_fields.form', $data);
    }

    /**
     * Delete a custom field
     *
     * @param int $id Custom field ID
     */
    public function delete(int $id)
    {
        $customField = CustomField::findOrFail($id);
        
        // TODO: Check if field is in use before deleting
        // For now, delete related values and then the field
        CustomValue::where('custom_values_field', $id)->delete();
        $customField->delete();
        
        session()->flash('alert_success', trans('record_successfully_deleted'));
        
        // Return to referrer or custom fields page
        $referer = request()->server('HTTP_REFERER');
        $redirectUrl = $referer ?: site_url('custom_fields');
        
        return redirect()->to($redirectUrl);
    }

    /**
     * Get positions for all custom field types
     *
     * @return array
     */
    private function getPositions(): array
    {
        return [
            'client' => [
                0 => trans('custom_fields'),
                1 => trans('address'),
                2 => trans('contact_information'),
                3 => trans('personal_information'),
                4 => trans('tax_information'),
            ],
            'invoice' => [
                0 => trans('custom_fields'),
                1 => trans('after_due_date'),
            ],
            'payment' => [
                0 => trans('custom_fields'),
            ],
            'quote' => [
                0 => trans('custom_fields'),
                1 => trans('after_expires'),
            ],
            'user' => [
                0 => trans('custom_fields'),
                1 => trans('after_email'),
            ],
        ];
    }
}
