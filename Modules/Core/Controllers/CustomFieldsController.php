<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\CustomField;
use Modules\Core\Services\CustomFieldService;

use Modules\Core\Support\TranslationHelper;
/**
 * CustomFieldsController
 *
 * Manages custom field CRUD operations for various entities (clients, invoices, quotes, etc.)
 *
 * @legacy-file application/modules/custom_fields/controllers/Custom_fields.php
 */
class CustomFieldsController
{    public function __construct(
        protected CustomFieldService $customFieldService
    ) {
    }

    /**
     * Display a paginated list of custom fields.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/custom_fields/controllers/Custom_fields.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $customFields = CustomField::query()
            ->orderBy('custom_field_table')
            ->orderBy('custom_field_label')
            ->paginate(15, ['*'], 'page', $page);

        return view('core::custom_fields_index', ['custom_fields' => $customFields]);
    }

    /**
     * Display form for creating or editing a custom field.
     *
     * @param int|null $id Custom field ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/custom_fields/controllers/Custom_fields.php
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('custom_fields.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'custom_field_table' => 'required|string',
                'custom_field_label' => 'required|string|max:255',
                'custom_field_column' => 'required|string|max:255',
                'custom_field_type' => 'required|string',
                'custom_field_order' => 'nullable|integer',
            ]);

            if ($id) {
                $this->customFieldService->update($id, $validated);
            } else {
                $this->customFieldService->create($validated);
            }

            return redirect()->route('custom_fields.index')->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        if ($id) {
            $customField = $this->customFieldService->find($id);
            if (!$customField) {
                abort(404);
            }
        } else {
            $customField = new CustomField();
        }

        return view('core::custom_fields_form', ['custom_field' => $customField]);
    }

    /**
     * Delete a custom field.
     *
     * @param int $id Custom field ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/custom_fields/controllers/Custom_fields.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->customFieldService->delete($id);

        return redirect()->route('custom_fields.index')->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
