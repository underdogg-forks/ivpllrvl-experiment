<?php

namespace Modules\Products\Controllers;

use Modules\Products\Models\Family;
use Modules\Products\Services\FamilyService;

/**
 * FamiliesController
 *
 * Handles product family management (product categories/groups)
 *
 * @legacy-file application/modules/families/controllers/Families.php
 */
class FamiliesController
{    public function __construct(
        protected FamilyService $familyService
    ) {
    }

    /**
     * Display a paginated list of product families.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/families/controllers/Families.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $families = $this->familyService->getAllPaginated(15, $page);

        return view('products::families_index', [
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_families'),
            'filter_method'      => 'filter_families',
            'families'           => $families,
        ]);
    }

    /**
     * Display form for creating or editing a product family.
     *
     * @param int|null $id Family ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/families/controllers/Families.php
     */
    public function form(?int $id = null)
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->route('families.index');
        }

        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'family_name' => 'required|string|max:255|unique:ip_families,family_name' . ($id ? ',' . $id . ',family_id' : ''),
            ]);

            if ($id) {
                // Update existing
                $this->familyService->update($id, $validated);
            } else {
                // Create new
                $this->familyService->create($validated);
            }

            return redirect()->route('families.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        // Load existing record for editing
        if ($id) {
            $family = $this->familyService->find($id);
            if (!$family) {
                abort(404);
            }
            $isUpdate = true;
        } else {
            $family = new Family();
            $isUpdate = false;
        }

        return view('products::families_form', [
            'family'    => $family,
            'is_update' => $isUpdate,
        ]);
    }

    /**
     * Delete a product family.
     *
     * @param int $id Family ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/families/controllers/Families.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->familyService->delete($id);

        return redirect()->route('families.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
