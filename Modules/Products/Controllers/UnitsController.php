<?php

namespace Modules\Products\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Support\TranslationHelper;
use Modules\Core\Traits\HandlesDeletion;
use Modules\Products\Http\Requests\UnitRequest;
use Modules\Products\Models\Unit;
use Modules\Products\Services\UnitService;

/**
 * UnitsController.
 *
 * Handles product unit management (e.g., hours, items, kg, etc.)
 *
 * @legacy-file application/modules/units/controllers/Units.php
 */
class UnitsController
{
    use HandlesDeletion;

    public function __construct(
        protected UnitService $unitService
    ) {}

    /**
     * Display a paginated list of product units.
     *
     * @param int $page Page number for pagination
     *
     * @return View
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function index(int $page = 0): View
    {
        $units = Unit::query()->ordered()
            ->paginate(15, ['*'], 'page', $page);

        return view('products::units_index', [
            'units' => $units,
        ]);
    }

    /**
     * Show the form for creating a new unit.
     *
     * @return View
     *
     * @legacy-function form
     *
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function create(): View
    {
        $unit = new Unit();

        return view('products::units_form', ['unit' => $unit]);
    }

    /**
     * Store a newly created unit.
     *
     * @param UnitRequest $request
     *
     * @return RedirectResponse
     *
     * @legacy-function form (save action)
     *
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function store(UnitRequest $request): RedirectResponse
    {
        $this->unitService->create($request->validated());

        return redirect()->route('units.index')
            ->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
    }

    /**
     * Show the form for editing an existing unit.
     *
     * @param Unit $unit
     *
     * @return View
     *
     * @legacy-function form (with ID)
     *
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function edit(Unit $unit): View
    {
        return view('products::units_form', ['unit' => $unit]);
    }

    /**
     * Update the specified unit.
     *
     * @param UnitRequest $request
     * @param Unit        $unit
     *
     * @return RedirectResponse
     *
     * @legacy-function form (update action)
     *
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->unitService->update($unit->unit_id, $request->validated());

        return redirect()->route('units.index')
            ->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
    }

    /**
     * Remove the specified unit with business logic validation.
     *
     * Implements:
     * - Early returns for validation
     * - Business rule: Cannot delete units used in products, invoice items, or quote items
     * - DRY principle via HandlesDeletion trait
     *
     * @param Unit $unit
     *
     * @return RedirectResponse
     *
     * @legacy-function delete
     *
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        $unitId = $unit->unit_id;

        // Business rule: Cannot delete units that are in use
        if (!$this->unitService->canDelete($unitId)) {
            $blockers = $this->unitService->getDeletionBlockers($unitId);
            $message = TranslationHelper::trans('unit_deletion_not_allowed', [
                'products'      => $blockers['products'],
                'invoice_items' => $blockers['invoice_items'],
                'quote_items'   => $blockers['quote_items'],
            ]);
            
            return $this->redirectWithError('units.index', $message);
        }

        // Execute delete with standardized error handling
        return $this->executeDelete(
            fn () => $this->unitService->delete($unitId),
            'units.index'
        );
    }
}
