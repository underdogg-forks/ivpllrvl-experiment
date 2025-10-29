<?php

namespace Modules\Products\Http\Controllers;

use Modules\Products\Entities\Family;
use Illuminate\Http\Request;

/**
 * FamiliesController
 * 
 * Handles product family management
 * Migrated from CodeIgniter Families controller
 */
class FamiliesController
{
    /**
     * Display a listing of product families.
     *
     * @param int $page
     * @return \Illuminate\Contracts\View\View
     */
    public function index($page = 0)
    {
        $families = Family::ordered()
            ->paginate(15);

        return view('products::index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_families'),
            'filter_method' => 'filter_families',
            'families' => $families,
        ]);
    }

    /**
     * Show the form for creating/editing a family.
     *
     * @param int|null $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form($id = null)
    {
        // Handle cancel button
        if (request()->has('btn_cancel')) {
            return redirect()->to('families');
        }

        // Handle form submission
        if (request()->has('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'family_name' => 'required|string|max:255',
            ]);

            // Check for duplicates on create
            if (request()->input('is_update') == 0) {
                $existing = Family::where('family_name', $validated['family_name'])->first();
                if ($existing) {
                    session()->flash('alert_error', trans('family_already_exists'));
                    return redirect()->to('families/form');
                }
            }

            // Create or update family
            if ($id) {
                $family = Family::findOrFail($id);
                $family->update($validated);
            } else {
                Family::create($validated);
            }

            return redirect()->to('families');
        }

        // Load family for editing
        $family = null;
        $is_update = false;
        if ($id) {
            $family = Family::find($id);
            if (!$family) {
                abort(404);
            }
            $is_update = true;
        }

        return view('products::form', [
            'family' => $family,
            'is_update' => $is_update,
        ]);
    }

    /**
     * Delete a family.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $family = Family::findOrFail($id);
        $family->delete();

        return redirect()->to('families');
    }
}

