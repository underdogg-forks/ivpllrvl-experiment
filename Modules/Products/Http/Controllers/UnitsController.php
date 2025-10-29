<?php

namespace Modules\Products\Http\Controllers;

use Modules\Products\Entities\Unit;
use Illuminate\Http\Request;

/**
 * UnitsController
 * 
 * Handles product unit of measure management
 * Migrated from CodeIgniter Units controller
 */
class UnitsController
{
    /**
     * Display a listing of units.
     *
     * @param int $page
     * @return \Illuminate\Contracts\View\View
     */
    public function index($page = 0)
    {
        $units = Unit::ordered()
            ->paginate(15);

        return view('products::index', [
            'units' => $units,
        ]);
    }

    /**
     * Show the form for creating/editing a unit.
     *
     * @param int|null $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form($id = null)
    {
        // Handle cancel button
        if (request()->has('btn_cancel')) {
            return redirect()->to('units');
        }

        // Handle form submission
        if (request()->has('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'unit_name' => 'required|string|max:255',
                'unit_name_plrl' => 'required|string|max:255',
            ]);

            // Check for duplicates on create
            if (request()->input('is_update') == 0) {
                $existing = Unit::where('unit_name', $validated['unit_name'])->first();
                if ($existing) {
                    session()->flash('alert_error', trans('unit_already_exists'));
                    return redirect()->to('units/form');
                }
            }

            // Create or update unit
            if ($id) {
                $unit = Unit::findOrFail($id);
                $unit->update($validated);
            } else {
                Unit::create($validated);
            }

            return redirect()->to('units');
        }

        // Load unit for editing
        $unit = null;
        $is_update = false;
        if ($id) {
            $unit = Unit::find($id);
            if (!$unit) {
                abort(404);
            }
            $is_update = true;
        }

        return view('products::form', [
            'unit' => $unit,
            'is_update' => $is_update,
        ]);
    }

    /**
     * Delete a unit.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->to('units');
    }
}

