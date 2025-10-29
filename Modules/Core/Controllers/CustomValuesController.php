<?php

namespace Modules\Core\Http\Controllers;

use Modules\Custom\Entities\CustomValue;

class CustomValuesController
{
    /** @legacy-file application/modules/custom_values/controllers/Custom_values.php */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $customValues = CustomValue::with('customField')->orderBy('custom_field_id')->paginate(15, ['*'], 'page', $page);
        return view('core::custom_values_index', ['custom_values' => $customValues]);
    }

    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) return redirect()->route('custom_values.index');
        
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate(CustomValue::validationRules());
            if ($id) {
                CustomValue::findOrFail($id)->update($validated);
            } else {
                CustomValue::create($validated);
            }
            return redirect()->route('custom_values.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $customValue = $id ? CustomValue::findOrFail($id) : new CustomValue();
        $customFields = \Modules\Custom\Entities\CustomField::orderBy('custom_field_label')->get();
        return view('core::custom_values_form', ['custom_value' => $customValue, 'custom_fields' => $customFields]);
    }

    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        CustomValue::findOrFail($id)->delete();
        return redirect()->route('custom_values.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
