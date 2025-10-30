<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\CustomField;

class CustomFieldsController
{
    /** @legacy-file application/modules/custom_fields/controllers/Custom_fields.php */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $customFields = CustomField::query()->orderBy('custom_field_table')->orderBy('custom_field_label')->paginate(15, ['*'], 'page', $page);
        return view('core::custom_fields_index', ['custom_fields' => $customFields]);
    }

    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) return redirect()->route('custom_fields.index');
        
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate(CustomField::validationRules());
            if ($id) {
                CustomField::query()->findOrFail($id)->update($validated);
            } else {
                CustomField::query()->create($validated);
            }
            return redirect()->route('custom_fields.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $customField = $id ? CustomField::query()->findOrFail($id) : new CustomField();
        return view('core::custom_fields_form', ['custom_field' => $customField]);
    }

    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        CustomField::query()->findOrFail($id)->delete();
        return redirect()->route('custom_fields.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
