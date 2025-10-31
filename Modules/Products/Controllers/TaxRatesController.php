<?php

namespace Modules\Products\Controllers;

use Modules\Products\Http\Requests\TaxRateRequest;
use Modules\Products\Models\TaxRate;
use Modules\Products\Services\TaxRateService;

/**
 * TaxRatesController.
 *
 * Handles tax rate management for products and invoices
 */
class TaxRatesController
{
    protected TaxRateService $taxRateService;

    public function __construct(TaxRateService $taxRateService)
    {
        $this->taxRateService = $taxRateService;
    }

    /**
     * Display a paginated list of tax rates.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
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
     * Show the form for creating a new tax rate.
     *
     * @return \Illuminate\View\View
     */
    public function create(): \Illuminate\View\View
    {
        $taxRate = new TaxRate();
        return view('products::tax_rates_form', ['tax_rate' => $taxRate]);
    }

    /**
     * Store a newly created tax rate.
     *
     * @param TaxRateRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TaxRateRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->taxRateService->create($request->validated());

        return redirect()->route('tax_rates.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Show the form for editing an existing tax rate.
     *
     * @param TaxRate $taxRate
     *
     * @return \Illuminate\View\View
     */
    public function edit(TaxRate $taxRate): \Illuminate\View\View
    {
        return view('products::tax_rates_form', ['tax_rate' => $taxRate]);
    }

    /**
     * Update the specified tax rate.
     *
     * @param TaxRateRequest $request
     * @param TaxRate        $taxRate
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TaxRateRequest $request, TaxRate $taxRate): \Illuminate\Http\RedirectResponse
    {
        $this->taxRateService->update($taxRate->tax_rate_id, $request->validated());

        return redirect()->route('tax_rates.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Remove the specified tax rate.
     *
     * @param TaxRate $taxRate
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TaxRate $taxRate): \Illuminate\Http\RedirectResponse
    {
        $this->taxRateService->delete($taxRate->tax_rate_id);

        return redirect()->route('tax_rates.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
