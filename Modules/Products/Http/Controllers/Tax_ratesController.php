<?php

namespace Modules\Products\Http\Controllers;

use Modules\Products\Entities\Tax_rate;

/**
 * Tax_ratesController
 * 
 * Handles tax rate management
 * Migrated from CodeIgniter Tax_Rates controller
 */
class Tax_ratesController
{
    /**
     * Display a listing of tax rates.
     *
     * @param int $page
     * @return \Illuminate\Contracts\View\View
     */
    public function index($page = 0)
    {
        $tax_rates = Tax_rate::ordered()
            ->paginate(15);

        return view('products::tax_rates.index', [
            'tax_rates' => $tax_rates,
        ]);
    }

    /**
     * Show the form for creating/editing a tax rate.
     *
     * @param int|null $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form($id = null)
    {
        // Handle cancel button
        if (request()->has('btn_cancel')) {
            return redirect()->to('tax_rates');
        }

        // Handle form submission
        if (request()->has('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'tax_rate_name' => 'required|string|max:255',
                'tax_rate_percent' => 'required|numeric',
            ]);

            // Standardize the percent value
            $validated['tax_rate_percent'] = standardize_amount($validated['tax_rate_percent']);

            // Create or update tax rate
            if ($id) {
                $tax_rate = Tax_rate::findOrFail($id);
                $tax_rate->update($validated);
            } else {
                Tax_rate::create($validated);
            }

            return redirect()->to('tax_rates');
        }

        // Load tax rate for editing
        $tax_rate = null;
        if ($id) {
            $tax_rate = Tax_rate::find($id);
            if (!$tax_rate) {
                abort(404);
            }
        }

        return view('products::tax_rates.form', [
            'tax_rate' => $tax_rate,
        ]);
    }

    /**
     * Delete a tax rate.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $tax_rate = Tax_rate::findOrFail($id);
        $tax_rate->delete();

        return redirect()->to('tax_rates');
    }
}

