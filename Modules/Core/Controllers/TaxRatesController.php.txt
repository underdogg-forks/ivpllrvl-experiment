<?php

namespace Modules\Core\Controllers;

use AllowDynamicProperties;
use Illuminate\Http\Request;
use Modules\Core\Services\TaxRatesService;

#[AllowDynamicProperties]
class TaxRatesController extends AdminController
{
    /**
     * TaxRatesController constructor.
     */
    public function __construct(
        protected TaxRatesService $taxRatesService
    ) {
        parent::__construct();
    }

    /**
     * Display a listing of the tax rates.
     *
     * @param int $page
     *
     * @return \Illuminate\View\View
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $this->taxRatesService->paginate(route('tax_rates.index'), $page);
        $tax_rates = $this->taxRatesService->result();

        return view('tax_rates.index', [
            'tax_rates' => $tax_rates,
        ]);
    }

    /**
     * Show the form for creating or editing a tax rate.
     *
     * @param Request  $request
     * @param int|null $id
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form(Request $request, ?int $id = null): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if ($request->has('btn_cancel')) {
            return redirect()->route('tax_rates.index');
        }
        $this->filterInput();
        if ($this->taxRatesService->runValidation()) {
            $this->taxRatesService->form_values['tax_rate_percent'] = standardize_amount($this->taxRatesService->form_values['tax_rate_percent']);
            $db_array                                               = $this->taxRatesService->dbArray();
            $db_array['tax_rate_percent']                           = standardize_amount($request->input('tax_rate_percent'));
            $this->taxRatesService->save($id, $db_array);

            return redirect()->route('tax_rates.index');
        }
        if ($id && ! $request->has('btn_submit') && ! $this->taxRatesService->prepForm($id)) {
            abort(404);
        }

        return view('tax_rates.form');
    }

    /**
     * Store a new or updated tax rate.
     *
     * @param Request  $request
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function formStore(Request $request, ?int $id = null): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        if ($request->has('btn_cancel')) {
            return redirect()->route('tax_rates.index');
        }
        $this->filterInput();
        if ($this->taxRatesService->runValidation()) {
            $this->taxRatesService->form_values['tax_rate_percent'] = standardize_amount($this->taxRatesService->form_values['tax_rate_percent']);
            $db_array                                               = $this->taxRatesService->dbArray();
            $db_array['tax_rate_percent']                           = standardize_amount($request->input('tax_rate_percent'));
            $this->taxRatesService->save($id, $db_array);

            return redirect()->route('tax_rates.index');
        }
        if ($id && ! $request->has('btn_submit') && ! $this->taxRatesService->prepForm($id)) {
            abort(404);
        }

        return view('tax_rates.form');
    }

    /**
     * Delete a tax rate.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->taxRatesService->delete($id);

        return redirect()->route('tax_rates.index');
    }
}
