<?php

namespace Modules\Core\Controllers;

use Modules\Core\Support\TranslationHelper;
use Modules\Core\Traits\HandlesDeletion;
use Modules\Products\Models\TaxRate;
use Modules\Products\Services\TaxRateService;

/**
 * TaxRatesController.
 *
 * Manages tax rate CRUD operations
 *
 * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
 */
class TaxRatesController
{
    use HandlesDeletion;

    public function __construct(
        protected TaxRateService $taxRateService
    ) {}

    /**
     * Display a paginated list of tax rates.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     *
     * @legacy-function index
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
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     *
     * @legacy-function form
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('tax_rates.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'tax_rate_name'    => 'required|string|max:255',
                'tax_rate_percent' => 'required|numeric|min:0|max:100',
            ]);

            // Standardize the percentage format
            $validated['tax_rate_percent'] = standardize_amount($validated['tax_rate_percent']);

            if ($id) {
                $this->taxRateService->update($id, $validated);
            } else {
                $this->taxRateService->create($validated);
            }

            return redirect()->route('tax_rates.index')->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        $taxRate = $id ? $this->taxRateService->find($id) : new TaxRate();
        if ($id && ! $taxRate) {
            abort(404);
        }

        return view('core::tax_rates_form', ['tax_rate' => $taxRate]);
    }

    /**
     * Delete a tax rate with business logic validation.
     *
     * Implements:
     * - Early returns for validation
     * - Business rule: Cannot delete tax rates used in products, invoice items, quote items, or tax rate relations
     * - DRY principle via HandlesDeletion trait
     *
     * @param int $id Tax rate ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-file application/modules/tax_rates/controllers/Tax_rates.php
     *
     * @legacy-function delete
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        // Validate ID
        if ($id <= 0) {
            return $this->redirectWithError('tax_rates.index', TranslationHelper::trans('invalid_tax_rate_id'));
        }

        // Check if tax rate exists
        $taxRate = $this->taxRateService->find($id);
        if ( ! $taxRate) {
            return $this->redirectWithError('tax_rates.index', TranslationHelper::trans('tax_rate_not_found'));
        }

        // Business rule: Cannot delete tax rates that are in use
        if ( ! $this->taxRateService->canDelete($id)) {
            $blockers = $this->taxRateService->getDeletionBlockers($id);
            $message  = TranslationHelper::trans('tax_rate_deletion_not_allowed', [
                'products'          => $blockers['products'],
                'invoice_items'     => $blockers['invoice_items'],
                'invoice_tax_rates' => $blockers['invoice_tax_rates'],
                'quote_items'       => $blockers['quote_items'],
                'quote_tax_rates'   => $blockers['quote_tax_rates'],
            ]);

            return $this->redirectWithError('tax_rates.index', $message);
        }

        // Execute delete with standardized error handling
        return $this->executeDelete(
            fn () => $this->taxRateService->delete($id),
            'tax_rates.index'
        );
    }
}
