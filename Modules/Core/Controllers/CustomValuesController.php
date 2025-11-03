<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\CustomField;
use Modules\Core\Services\CustomFieldService;
use Modules\Core\Services\CustomValueService;
use Modules\Custom\Models\CustomValue;

/**
 * CustomValuesController
 *
 * Manages custom value CRUD operations for custom fields
 *
 * @legacy-file application/modules/custom_values/controllers/Custom_values.php
 */
class CustomValuesController
{
    protected CustomValueService $customValueService;
    protected CustomFieldService $customFieldService;

    public function __construct(
        CustomValueService $customValueService,
        CustomFieldService $customFieldService
    ) {
        $this->customValueService = $customValueService;
        $this->customFieldService = $customFieldService;
    }

    /**
     * Display a paginated list of custom values.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/custom_values/controllers/Custom_values.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $customValues = CustomValue::query()
            ->with('customField')
            ->orderBy('custom_field_id')
            ->paginate(15, ['*'], 'page', $page);

        return view('core::custom_values_index', ['custom_values' => $customValues]);
    }

    /**
     * Display form for creating or editing a custom value.
     *
     * @param int|null $id Custom value ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/custom_values/controllers/Custom_values.php
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('custom_values.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'custom_field_id' => 'required|integer|exists:ip_custom_fields,custom_field_id',
                'custom_value_value' => 'required|string|max:255',
            ]);

            if ($id) {
                $this->customValueService->update($id, $validated);
            } else {
                $this->customValueService->create($validated);
            }

            return redirect()->route('custom_values.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $customValue = $id ? $this->customValueService->find($id) : new CustomValue();
        if ($id && !$customValue) {
            abort(404);
        }

        $customFields = CustomField::query()->orderBy('custom_field_label')->get();

        return view('core::custom_values_form', ['custom_value' => $customValue, 'custom_fields' => $customFields]);
    }

    /**
     * Delete a custom value.
     *
     * @param int $id Custom value ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/custom_values/controllers/Custom_values.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->customValueService->delete($id);

        return redirect()->route('custom_values.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
