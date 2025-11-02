<?php

namespace Modules\Core\Controllers;

use Modules\Custom\Models\CustomValue;
use Modules\Core\Services\CustomValueService;
use Modules\Core\Services\CustomFieldService;

class CustomValuesController
{
    /**
     * CustomValue service instance.
     *
     * @var CustomValueService
     */
    protected CustomValueService $customValueService;

    /**
     * CustomField service instance.
     *
     * @var CustomFieldService
     */
    protected CustomFieldService $customFieldService;

    /**
     * Constructor.
     *
     * @param CustomValueService  $customValueService
     * @param CustomFieldService $customFieldService
     */
    public function __construct(
        CustomValueService $customValueService,
        CustomFieldService $customFieldService
    ) {
        $this->customValueService  = $customValueService;
        $this->customFieldService = $customFieldService;
    }

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
            $validated = request()->validate($this->customValueService->getValidationRules());
            if ($id) {
                $this->customValueService->update($id, $validated);
            } else {
                $this->customValueService->create($validated);
            }

            return redirect()->route('custom_values.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $customValue  = $id ? $this->customValueService->findOrFail($id) : new CustomValue();
        $customFields = \Modules\Custom\Models\CustomField::orderBy('custom_field_label')->get();

        return view('core::custom_values_form', ['custom_value' => $customValue, 'custom_fields' => $customFields]);
    }

    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->customValueService->delete($id);

        return redirect()->route('custom_values.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
