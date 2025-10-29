<?php

namespace Modules\Products\Http\Controllers;

use Modules\Products\Entities\Unit;

/**
 * UnitsController
 * 
 * Handles product unit management (e.g., hours, items, kg, etc.)
 */
class UnitsController
{
    /**
     * Display a paginated list of product units
     * 
     * @param int $page Page number for pagination
     * @return \Illuminate\View\View
     * 
     * @legacy-function index
     * @legacy-file application/modules/units/controllers/Units.php
     * @legacy-line 32
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $units = Unit::ordered()
            ->paginate(15, ['*'], 'page', $page);

        return view('products::units_index', [
            'units' => $units,
        ]);
    }

    /**
     * Display form for creating or editing a product unit
     * 
     * @param int|null $id Unit ID (null for create)
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * 
     * @legacy-function form
     * @legacy-file application/modules/units/controllers/Units.php
     * @legacy-line 42
     */
    public function form(?int $id = null)
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->route('units.index');
        }

        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'unit_name' => 'required|string|max:255|unique:ip_units,unit_name' . ($id ? ',' . $id . ',unit_id' : ''),
                'unit_name_plrl' => 'required|string|max:255',
            ]);

            if ($id) {
                // Update existing
                $unit = Unit::findOrFail($id);
                $unit->update($validated);
            } else {
                // Create new
                Unit::create($validated);
            }

            return redirect()->route('units.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        // Load existing record for editing
        if ($id) {
            $unit = Unit::find($id);
            if (!$unit) {
                abort(404);
            }
            $isUpdate = true;
        } else {
            $unit = new Unit();
            $isUpdate = false;
        }

        return view('products::units_form', [
            'unit' => $unit,
            'is_update' => $isUpdate,
        ]);
    }

    /**
     * Delete a product unit
     * 
     * @param int $id Unit ID
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function delete
     * @legacy-file application/modules/units/controllers/Units.php
     * @legacy-line 83
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('units.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
