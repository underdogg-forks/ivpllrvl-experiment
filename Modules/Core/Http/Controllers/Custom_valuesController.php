<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Entities\Custom_field;
use Modules\Core\Entities\Custom_value;

/**
 * Custom_valuesController
 * 
 * Manages custom field value options (for SINGLE-CHOICE and MULTIPLE-CHOICE fields)
 * Migrated from CodeIgniter Custom_Values controller
 */
class Custom_valuesController
{
    /**
     * Display all custom values grouped by field
     *
     * @param int $page Page number for pagination
     */
    public function index(int $page = 0)
    {
        $perPage = 15;
        
        // Get custom values with their fields
        $customValues = Custom_value::with('customField')
            ->select('custom_values.*', \DB::raw('COUNT(custom_field_label) as count'))
            ->join('ip_custom_fields', 'ip_custom_values.custom_values_field', '=', 'ip_custom_fields.custom_field_id')
            ->groupBy('ip_custom_fields.custom_field_id')
            ->orderBy('custom_values_value')
            ->paginate($perPage, ['*'], 'page', $page);
        
        $customTables = Custom_field::customTables();
        $positions = $this->getPositions();
        
        $data = [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_custom_values'),
            'filter_method' => 'filter_custom_values',
            'custom_tables' => $customTables,
            'custom_values' => $customValues,
            'positions' => $positions,
        ];
        
        return view('core::custom_values.index', $data);
    }

    /**
     * Display custom values for a specific field
     *
     * @param int $id Custom field ID
     */
    public function field(int $id)
    {
        $field = Custom_field::findOrFail($id);
        $elements = Custom_value::where('custom_values_field', $id)->get();
        
        $customTables = Custom_field::customTables();
        $positions = $this->getPositions();
        $position = $positions[$field->custom_field_table][$field->custom_field_location] ?? '';
        
        $data = [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_custom_values'),
            'filter_method' => 'filter_custom_values_field',
            'id' => $id,
            'field' => $field,
            'elements' => $elements,
            'custom_field_usage' => [], // TODO: Implement usage check
            'position' => $position,
            'table' => $customTables[$field->custom_field_table] ?? '',
        ];
        
        return view('core::custom_values.field', $data);
    }

    /**
     * Show form to edit a custom value
     *
     * @param int $id Custom value ID
     */
    public function edit(int $id)
    {
        $value = Custom_value::with('customField')->findOrFail($id);
        $fid = $value->custom_values_field;
        
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->to(site_url('custom_values/field/' . $fid));
        }
        
        // Handle form submission
        if (request()->isMethod('post')) {
            // Validate
            $validated = request()->validate([
                'custom_values_value' => 'required',
            ]);
            
            $value->update($validated);
            
            session()->flash('alert_success', trans('record_successfully_updated'));
            return redirect()->to(site_url('custom_values/field/' . $fid));
        }
        
        $positions = $this->getPositions();
        $field = $value->customField;
        $position = $positions[$field->custom_field_table][$field->custom_field_location] ?? '';
        
        $data = [
            'id' => $id,
            'fid' => $fid,
            'value' => $value,
            'position' => $position,
            'custom_field_usage' => [], // TODO: Implement usage check
        ];
        
        return view('core::custom_values.edit', $data);
    }

    /**
     * Show form to create a new custom value
     *
     * @param int $id Custom field ID
     */
    public function create(int $id)
    {
        if (!$id) {
            return redirect()->to(site_url('custom_values'));
        }
        
        $fid = $id;
        
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->to(site_url('custom_values/field/' . $fid));
        }
        
        // Handle form submission
        if (request()->isMethod('post')) {
            // Validate
            $validated = request()->validate([
                'custom_values_value' => 'required',
            ]);
            
            $validated['custom_values_field'] = $fid;
            Custom_value::create($validated);
            
            session()->flash('alert_success', trans('record_successfully_created'));
            return redirect()->to(site_url('custom_values/field/' . $fid));
        }
        
        $field = Custom_field::findOrFail($id);
        $customTables = Custom_field::customTables();
        $table = $customTables[$field->custom_field_table] ?? '';
        
        $positions = $this->getPositions();
        $position = $positions[$field->custom_field_table][$field->custom_field_location] ?? '';
        
        $data = [
            'id' => $id,
            'field' => $field,
            'table' => $table,
            'position' => $position,
        ];
        
        return view('core::custom_values.new', $data);
    }

    /**
     * Delete a custom value
     *
     * @param int $id Custom value ID
     */
    public function delete(int $id)
    {
        $customValue = Custom_value::findOrFail($id);
        
        // TODO: Check if value is in use before deleting
        // For now, just delete
        $customValue->delete();
        
        $fid = request()->post('custom_field_id');
        $redirectUrl = $fid ? 'custom_values/field/' . $fid : 'custom_values';
        
        session()->flash('alert_success', trans('record_successfully_deleted'));
        return redirect()->to(site_url($redirectUrl));
    }

    /**
     * Get positions for all custom field types
     *
     * @return array
     */
    private function getPositions(): array
    {
        return [
            'ip_client_custom' => [
                0 => trans('custom_fields'),
                1 => trans('address'),
                2 => trans('contact_information'),
                3 => trans('personal_information'),
                4 => trans('tax_information'),
            ],
            'ip_invoice_custom' => [
                0 => trans('custom_fields'),
                1 => trans('after_due_date'),
            ],
            'ip_payment_custom' => [
                0 => trans('custom_fields'),
            ],
            'ip_quote_custom' => [
                0 => trans('custom_fields'),
                1 => trans('after_expires'),
            ],
            'ip_user_custom' => [
                0 => trans('custom_fields'),
                1 => trans('after_email'),
            ],
        ];
    }
}
