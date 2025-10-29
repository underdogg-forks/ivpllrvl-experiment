<?php

namespace Modules\Products\Controllers;

use Modules\Products\Models\TaxRate;

/**
 * TaxRatesController
 * 
 * Handles tax rate management for products and invoices
 */
class TaxRatesController
{
    /**
     * Display a paginated list of tax rates
     * 
     * @param int $page Page number for pagination
     * @return \Illuminate\View\View
     * 
     * @legacy-function index
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     * @legacy-line 32
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $taxRates = TaxRate::ordered()
            ->paginate(15, ['*'], 'page', $page);

        return view('products::tax_rates_index', [
            'tax_rates' => $taxRates,
        ]);
    }

    /**
     * Display form for creating or editing a tax rate
     * 
     * @param int|null $id Tax rate ID (null for create)
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * 
     * @legacy-function form
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     * @legacy-line 42
     */
    public function form(?int $id = null)
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->route('tax_rates.index');
        }

        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'tax_rate_name' => 'required|string|max:255',
                'tax_rate_percent' => 'required|numeric|min:0|max:100',
            ]);

            // Standardize the tax rate percent (convert comma to dot for decimal)
            if (function_exists('standardize_amount')) {
                $validated['tax_rate_percent'] = standardize_amount($validated['tax_rate_percent']);
            } else {
                // Fallback: ensure dot as decimal separator
                $validated['tax_rate_percent'] = str_replace(',', '.', $validated['tax_rate_percent']);
            }

            if ($id) {
                // Update existing
                $taxRate = TaxRate::query()->findOrFail($id);
                $taxRate->update($validated);
            } else {
                // Create new
                TaxRate::query()->create($validated);
            }

            return redirect()->route('tax_rates.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        // Load existing record for editing
        if ($id) {
            $taxRate = TaxRate::query()->find($id);
            if (!$taxRate) {
                abort(404);
            }
        } else {
            $taxRate = new TaxRate();
        }

        return view('products::tax_rates_form', [
            'tax_rate' => $taxRate,
        ]);
    }

    /**
     * Delete a tax rate
     * 
     * @param int $id Tax rate ID
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function delete
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     * @legacy-line 73
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $taxRate = TaxRate::query()->findOrFail($id);
        $taxRate->delete();

        return redirect()->route('tax_rates.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
