<?php

namespace Modules\Core\Controllers;

use Modules\Custom\Models\CustomValue;

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
        if (request()->post('btn_cancel')) {
            return redirect()->route('custom_values.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate(CustomValue::validationRules());
            if ($id) {
                CustomValue::query()->findOrFail($id)->update($validated);
            } else {
                CustomValue::query()->create($validated);
            }

            return redirect()->route('custom_values.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $customValue  = $id ? CustomValue::query()->findOrFail($id) : new CustomValue();
        $customFields = \Modules\Custom\Models\CustomField::query()->orderBy('custom_field_label')->get();

        return view('core::custom_values_form', ['custom_value' => $customValue, 'custom_fields' => $customFields]);
    }

    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        CustomValue::query()->findOrFail($id)->delete();

        return redirect()->route('custom_values.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
