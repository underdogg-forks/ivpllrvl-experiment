<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\CustomField;
use Modules\Core\Services\CustomFieldService;
use Modules\Core\Support\TranslationHelper;
use Modules\Core\Traits\HandlesDeletion;

/**
 * CustomFieldsController.
 *
 * Manages custom field CRUD operations for various entities (clients, invoices, quotes, etc.)
 *
 * @legacy-file application/modules/custom_fields/controllers/Custom_fields.php
 */
class CustomFieldsController
{
    use HandlesDeletion;

    public function __construct(
        protected CustomFieldService $customFieldService
    ) {}

    /**
     * Display a paginated list of custom fields.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-file application/modules/custom_fields/controllers/Custom_fields.php
     * @legacy-function index
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        // Display all custom_fields tables by default
        //redirect('custom_fields/table/all');

        $customFields = CustomField::query()
            ->orderBy('custom_field_table')
            ->orderBy('custom_field_label')
            ->paginate(15, ['*'], 'page', $page);

        return view('core::custom_fields_index', ['custom_fields' => $customFields]);
    }

    /**
     * @param string $name of table (simple) NAME (more comprehensive) & why not a filter by type??? like I/Q payment & todo for product ;)
     * @param int    $page
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/controllers/CustomFields.php
     *
     * @legacy-function table()
     */
    public function table(string $name = 'all', $page = 0): void
    {
        // Determine which name of table custom field to load
        $custom_tables = $this->customfields->custom_tables();
        if ($name != 'all' && in_array($name, $custom_tables)) {
            $this->customfields->by_table_name($name);
        }

        // Paginate before result
        $this->customfields->paginate(site_url('custom_fields/name/' . $name), $page);
        $custom_fields = $this->customfields->result();

        $this->load->model('custom_values/customvalue');
        $this->layout->set(
            [
                'filter_display'     => true,
                'filter_placeholder' => trans('filter_custom_fields'),
                'filter_method'      => 'filter_custom_fields',

                'custom_fields'       => $custom_fields,
                'custom_tables'       => $custom_tables,
                'custom_value_fields' => $this->customvalues->custom_value_fields(),
                'positions'           => $this->customfields->get_positions(true),
            ]
        );
        $this->layout->buffer('content', 'custom_fields/index');
        $this->layout->render();
    }

    /**
     * Display form for creating or editing a custom field.
     *
     * @param int|null $id Custom field ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * @legacy-file application/modules/custom_fields/controllers/Custom_fields.php
     * @legacy-function form
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('custom-fields.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'custom_field_table'  => 'required|string',
                'custom_field_label'  => 'required|string|max:255',
                'custom_field_column' => 'required|string|max:255',
                'custom_field_type'   => 'required|string',
                'custom_field_order'  => 'nullable|integer',
            ]);

            if ($id) {
                $this->customFieldService->update($id, $validated);
            } else {
                $this->customFieldService->create($validated);
            }

            return redirect()->route('custom-fields.index')->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        
	// return object after created?
	if ($id) {
            $customField = $this->customFieldService->find($id);
            if ( ! $customField) {
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
     * @legacy-file application/modules/custom_fields/controllers/Custom_fields.php
     *
     * @legacy-function delete
     *
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        // Early return for validation
        if ($id <= 0) {
            return $this->redirectWithError('custom-fields.index', TranslationHelper::trans('invalid_custom_field_id'));
        }

        // Check if custom field exists
        $customField = $this->customFieldService->find($id);
        if ( ! $customField) {
            return $this->redirectWithError('custom-fields.index', TranslationHelper::trans('custom_field_not_found'));
        }

        // Business rule: Cannot delete custom fields with related custom values
        if ( ! $this->customFieldService->canDelete($id)) {
            $blockers = $this->customFieldService->getDeletionBlockers($id);
            $message  = TranslationHelper::trans('custom_field_deletion_not_allowed', [
                'custom_values' => $blockers['custom_values'],
            ]);

            return $this->redirectWithError('custom-fields.index', $message);
        }

        // Execute deletion with standardized error handling
        return $this->executeDelete(
            fn () => $this->customFieldService->delete($id),
            'custom-fields.index'
        );
    }
}
