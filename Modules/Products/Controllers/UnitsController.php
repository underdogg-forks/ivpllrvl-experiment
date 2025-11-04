<?php

namespace Modules\Products\Controllers;

use Modules\Products\Http\Requests\UnitRequest;
use Modules\Products\Models\Unit;
use Modules\Products\Services\UnitService;

/**
 * UnitsController
 *
 * Handles product unit management (e.g., hours, items, kg, etc.)
 *
 * @legacy-file application/modules/units/controllers/Units.php
 */
class UnitsController
{    public function __construct(
        protected UnitService $unitService
    ) {
    }

    /**
     * Display a paginated list of product units.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function index(int $page = 0): \Illuminate\View\View
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
     * @return \Illuminate\View\View
     *
     * @legacy-function form
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function create(): \Illuminate\View\View
    {
        $unit = new Unit();
        return view('products::units_form', ['unit' => $unit]);
    }

    /**
     * Store a newly created unit.
     *
     * @param UnitRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function form (save action)
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function store(UnitRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->unitService->create($request->validated());

        return redirect()->route('units.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Show the form for editing an existing unit.
     *
     * @param Unit $unit
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function form (with ID)
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function edit(Unit $unit): \Illuminate\View\View
    {
        return view('products::units_form', ['unit' => $unit]);
    }

    /**
     * Update the specified unit.
     *
     * @param UnitRequest $request
     * @param Unit        $unit
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function form (update action)
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function update(UnitRequest $request, Unit $unit): \Illuminate\Http\RedirectResponse
    {
        $this->unitService->update($unit->unit_id, $request->validated());

        return redirect()->route('units.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Remove the specified unit.
     *
     * @param Unit $unit
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/units/controllers/Units.php
     */
    public function destroy(Unit $unit): \Illuminate\Http\RedirectResponse
    {
        $this->unitService->delete($unit->unit_id);

        return redirect()->route('units.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
