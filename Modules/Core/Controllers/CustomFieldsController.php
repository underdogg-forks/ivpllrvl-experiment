<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\CustomField;
use Modules\Core\Services\CustomFieldService;

class CustomFieldsController
{
    protected CustomFieldService $customFieldService;

    public function __construct(CustomFieldService $customFieldService)
    {
        $this->customFieldService = $customFieldService;
    }
    /** @legacy-file application/modules/custom_fields/controllers/Custom_fields.php */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $customFields = CustomField::orderBy('custom_field_table')->orderBy('custom_field_label')->paginate(15, ['*'], 'page', $page);

        return view('core::custom_fields_index', ['custom_fields' => $customFields]);
    }

    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('custom_fields.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate($this->customFieldService->getValidationRules());
            if ($id) {
                CustomField::findOrFail($id)->update($validated);
            } else {
                CustomField::create($validated);
            }

            return redirect()->route('custom_fields.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $customField = $id ? CustomField::findOrFail($id) : new CustomField();

        return view('core::custom_fields_form', ['custom_field' => $customField]);
    }

    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        CustomField::findOrFail($id)->delete();

        return redirect()->route('custom_fields.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
