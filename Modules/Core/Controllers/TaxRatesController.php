<?php

namespace Modules\Core\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Services\TaxRatesService;
use Modules\Products\Models\TaxRate;

/**
 * TaxRatesController
 *
 * Manages tax rate CRUD operations
 *
 * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
 */
class TaxRatesController
{    public function __construct(
        protected TaxRatesService $taxRatesService
    ) {
    }

    /**
     * Display a paginated list of tax rates.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $taxRates = TaxRate::query()
            ->orderBy('tax_rate_name')
            ->paginate(15, ['*'], 'page', $page);

        return view('core::tax_rates_index', ['tax_rates' => $taxRates]);
    }

    /**
     * Display form for creating or editing a tax rate.
     *
     * @param int|null $id Tax rate ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('tax_rates.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'tax_rate_name' => 'required|string|max:255',
                'tax_rate_percent' => 'required|numeric|min:0|max:100',
            ]);

            // Standardize the percentage format
            $validated['tax_rate_percent'] = standardize_amount($validated['tax_rate_percent']);

            if ($id) {
                $this->taxRatesService->update($id, $validated);
            } else {
                $this->taxRatesService->create($validated);
            }

            return redirect()->route('tax_rates.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $taxRate = $id ? $this->taxRatesService->find($id) : new TaxRate();
        if ($id && !$taxRate) {
            abort(404);
        }

        return view('core::tax_rates_form', ['tax_rate' => $taxRate]);
    }

    /**
     * Delete a tax rate.
     *
     * @param int $id Tax rate ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->taxRatesService->delete($id);

        return redirect()->route('tax_rates.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}
