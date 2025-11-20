<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\CustomField;
use Modules\Core\Services\CustomFieldService;
use Modules\Core\Services\CustomValueService;
use Modules\Core\Support\TranslationHelper;
use Modules\Custom\Models\CustomValue;

/**
 * CustomValuesController.
 *
 * Manages custom value CRUD operations for custom fields
 *
 * @legacy-file application/modules/custom_values/controllers/Custom_values.php
 */
class CustomValuesController
{
    public function __construct(
        protected CustomValueService $customValueService,
        protected CustomFieldService $customFieldService
    ) {}

    /**
     * Display a paginated list of custom values.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-file application/modules/custom_values/controllers/Custom_values.php
     *
     * @legacy-function index
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        /*
               $this->customvalues->grouped()->paginate(site_url('custom_values/index'), $page);
                $custom_values = $this->customvalues->result();

                $this->load->model('custom_fields/customfield');
                // Determine which name of table custom field to load
                $custom_tables = $this->customfields->custom_tables();
                // load positions by table name
                $positions = $this->customfields->get_positions(true);

                $this->layout->set(
                    [
                        'filter_display'     => true,
                        'filter_placeholder' => trans('filter_custom_values'),
                        'filter_method'      => 'filter_custom_values',
                        'custom_tables'      => $custom_tables,
                        'custom_values'      => $custom_values,
                        'positions'          => $positions,
                    ]
                );
        */

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
     * @legacy-file application/modules/custom_values/controllers/Custom_values.php
     *
     * @legacy-function form
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('custom_values.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'custom_field_id'    => 'required|integer|exists:ip_custom_fields,custom_field_id',
                'custom_value_value' => 'required|string|max:255',
            ]);

            if ($id) {
                $this->customValueService->update($id, $validated);
            } else {
                $this->customValueService->create($validated);
            }

            return redirect()->route('custom_values.index')->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        $customValue = $id ? $this->customValueService->find($id) : new CustomValue();
        if ($id && ! $customValue) {
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
     * @legacy-file application/modules/custom_values/controllers/Custom_values.php
     *
     * @legacy-function delete
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        /*
                if ( ! $this->customvalues->delete($id)) {
                    $this->session->set_flashdata('alert_info', trans('id') . sprintf(' "%s" ', $id) . trans('custom_values_used_not_deletable'));
                }

                $fid = $this->input->post('custom_field_id');
                redirect('custom_values' . ($fid ? '/field/' . $fid : ''));
        */

        $this->customValueService->delete($id);

        return redirect()->route('custom_values.index')->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}
