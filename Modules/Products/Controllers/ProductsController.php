<?php

namespace Modules\Products\Controllers;

use AllowDynamicProperties;
use Illuminate\Http\Request;
use Modules\Core\Controllers\AdminController;
use Modules\Products\Services\ProductsService;
use src\Services\FamiliesService;
use src\Services\TaxRatesService;
use src\Services\UnitsService;

#[AllowDynamicProperties]
class ProductsController extends AdminController
{
    /**
     * Display a paginated list of products.
     *
     * The returned view is populated with the paginated products and settings
     * controlling the filter UI (display flag, placeholder text, and filter method).
     *
     * @return \Illuminate\Contracts\View\View the products index view with paginated products and filter configuration
     */
    public function index(Request $request, int $page = 0): \Illuminate\Contracts\View\View
    {
        $service = new ProductsService();
        $service->paginate(route('products.index'), $page);
        $products = $service->result();

        return view('products.index', [
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_products'),
            'filter_method'      => 'filter_products',
            'products'           => $products,
        ]);
    }

    /**
     * Display and process the product creation/edit form.
     *
     * Handles cancel redirects, validates submitted data and saves the product when valid,
     * prepares the form for editing an existing product (or aborts with 404 if the product
     * cannot be prepared), and provides families, units, and tax rates for the view.
     *
     * @param \Illuminate\Http\Request $request the current HTTP request
     * @param int|null                 $id      optional product ID for editing; null when creating a new product
     *
     * @return \Illuminate\Contracts\View\View the products form view populated with `families`, `units`, and `tax_rates`
     */
    public function form(Request $request, $id = null): \Illuminate\Contracts\View\View
    {
        if ($request->has('btn_cancel')) {
            return redirect()->route('products.index');
        }
        // Filter input if needed
        // Validation
        $service = new ProductsService();
        if ($service->runValidation()) {
            $db_array = $service->dbArray();
            $service->save($id, $db_array);

            return redirect()->route('products.index');
        }
        if ($id && ! $request->has('btn_submit') && ! $service->prepForm($id)) {
            abort(404);
        }
        $families  = (new FamiliesService())->getAll();
        $units     = (new UnitsService())->getAll();
        $tax_rates = (new TaxRatesService())->getAll();

        return view('products.form', [
            'families'  => $families,
            'units'     => $units,
            'tax_rates' => $tax_rates,
        ]);
    }

    /**
     * Delete the specified product and redirect to the products index.
     *
     * @param int|string $id identifier of the product to delete
     *
     * @return \Illuminate\Http\RedirectResponse redirect response to the products index route
     */
    public function delete($id)
    {
        (new ProductsService())->delete($id);

        return redirect()->route('products.index');
    }
}
