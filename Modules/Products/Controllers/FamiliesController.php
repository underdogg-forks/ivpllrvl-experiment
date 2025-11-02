<?php

declare(strict_types=1);

namespace Modules\Products\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Products\Http\Requests\FamilyRequest;
use Modules\Products\Models\Family;
use Modules\Products\Services\FamilyService;

/**
 * FamiliesController
 *
 * Handles product family management operations including listing, creating,
 * editing, updating, and deleting families. Product families are categories
 * or groups that organize products (e.g., Services, Hardware, Software, etc.)
 *
 * @legacy-file application/modules/families/controllers/Families.php
 */
class FamiliesController
{
    /**
     * @param FamilyService $familyService Service for family business logic
     */
    public function __construct(
        private readonly FamilyService $familyService
    ) {
    }

    /**
     * Display a paginated list of product families.
     *
     * Returns families with pagination and filter configuration for the view.
     *
     * @param int $page Page number for pagination (default: 0)
     *
     * @return View
     *
     * @legacy-function index
     * @legacy-file application/modules/families/controllers/Families.php
     * @legacy-line 32
     */
    public function index(int $page = 0): View
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
     * Show the form for creating a new product family.
     *
     * Provides empty family instance for the creation form.
     *
     * @return View
     *
     * @legacy-function form (new family)
     * @legacy-file application/modules/families/controllers/Families.php
     * @legacy-line 47
     */
    public function create(): View
    {
        $family = new Family();

        return view('products::families_form', [
            'family'    => $family,
            'is_update' => false,
        ]);
    }

    /**
     * Store a newly created product family in the database.
     *
     * @param FamilyRequest $request Validated request data
     *
     * @return RedirectResponse
     *
     * @legacy-function form (save)
     * @legacy-file application/modules/families/controllers/Families.php
     * @legacy-line 47
     */
    public function store(FamilyRequest $request): RedirectResponse
    {
        $this->familyService->create($request->validated());

        return redirect()
            ->route('families.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Show the form for editing the specified product family.
     *
     * @param Family $family The family to edit (route model binding)
     *
     * @return View
     *
     * @legacy-function form (edit family)
     * @legacy-file application/modules/families/controllers/Families.php
     * @legacy-line 47
     */
    public function edit(Family $family): View
    {
        return view('products::families_form', [
            'family'    => $family,
            'is_update' => true,
        ]);
    }

    /**
     * Update the specified product family in the database.
     *
     * @param FamilyRequest $request Validated request data
     * @param Family        $family  The family to update (route model binding)
     *
     * @return RedirectResponse
     *
     * @legacy-function form (update)
     * @legacy-file application/modules/families/controllers/Families.php
     * @legacy-line 47
     */
    public function update(FamilyRequest $request, Family $family): RedirectResponse
    {
        $this->familyService->update($family->family_id, $request->validated());

        return redirect()
            ->route('families.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Remove the specified product family from the database.
     *
     * @param Family $family The family to delete (route model binding)
     *
     * @return RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/families/controllers/Families.php
     * @legacy-line 84
     */
    public function destroy(Family $family): RedirectResponse
    {
        $this->familyService->delete($family->family_id);

        return redirect()
            ->route('families.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
