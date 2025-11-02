<?php

declare(strict_types=1);

namespace Modules\Products\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Products\Http\Requests\UnitRequest;
use Modules\Products\Models\Unit;
use Modules\Products\Services\UnitService;

/**
 * UnitsController
 *
 * Handles product unit management operations including listing, creating,
 * editing, updating, and deleting units. Units define the measurement types
 * for products (e.g., hours, items, kg, pieces, etc.)
 *
 * @legacy-file application/modules/units/controllers/Units.php
 */
class UnitsController
{
    /**
     * @param UnitService $unitService Service for unit business logic
     */
    public function __construct(
        private readonly UnitService $unitService
    ) {
    }

    /**
     * Display a paginated list of product units.
     *
     * Returns units ordered by name with pagination support.
     *
     * @param int $page Page number for pagination (default: 0)
     *
     * @return View
     *
     * @legacy-function index
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function index(int $page = 0): View
    {
        $units = Unit::query()
            ->ordered()
            ->paginate(15, ['*'], 'page', $page);

        return view('products::units_index', [
            'units' => $units,
        ]);
    }

    /**
     * Show the form for creating a new unit.
     *
     * Provides empty unit instance for the creation form.
     *
     * @return View
     *
     * @legacy-function form (new unit)
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function create(): View
    {
        $unit = new Unit();
        
        return view('products::units_form', [
            'unit' => $unit,
        ]);
    }

    /**
     * Store a newly created unit in the database.
     *
     * @param UnitRequest $request Validated request data
     *
     * @return RedirectResponse
     *
     * @legacy-function form (save)
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function store(UnitRequest $request): RedirectResponse
    {
        $this->unitService->create($request->validated());

        return redirect()
            ->route('units.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Show the form for editing the specified unit.
     *
     * @param Unit $unit The unit to edit (route model binding)
     *
     * @return View
     *
     * @legacy-function form (edit unit)
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function edit(Unit $unit): View
    {
        return view('products::units_form', [
            'unit' => $unit,
        ]);
    }

    /**
     * Update the specified unit in the database.
     *
     * @param UnitRequest $request Validated request data
     * @param Unit        $unit    The unit to update (route model binding)
     *
     * @return RedirectResponse
     *
     * @legacy-function form (update)
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->unitService->update($unit->unit_id, $request->validated());

        return redirect()
            ->route('units.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Remove the specified unit from the database.
     *
     * @param Unit $unit The unit to delete (route model binding)
     *
     * @return RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        $this->unitService->delete($unit->unit_id);

        return redirect()
            ->route('units.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
