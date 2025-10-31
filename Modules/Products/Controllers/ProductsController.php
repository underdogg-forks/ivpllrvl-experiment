<?php

namespace Modules\Products\Controllers;

use Modules\Products\Http\Requests\ProductRequest;
use Modules\Products\Models\Family;
use Modules\Products\Models\Product;
use Modules\Products\Models\TaxRate;
use Modules\Products\Models\Unit;
use Modules\Products\Services\ProductService;

/**
 * ProductsController.
 *
 * Handles product catalog management
 */
class ProductsController
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a paginated list of products.
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $products = Product::with(['family', 'unit', 'taxRate'])
            ->orderBy('product_name')
            ->paginate(15, ['*'], 'page', $page);

        return view('products::index', [
            'filter_display'     => true,
            'filter_placeholder' => trans('filter_products'),
            'filter_method'      => 'filter_products',
            'products'           => $products,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): \Illuminate\View\View
    {
        $product = new Product();
        $families = Family::orderBy('family_name')->get();
        $units    = Unit::orderBy('unit_name')->get();
        $taxRates = TaxRate::orderBy('tax_rate_name')->get();

        return view('products::form', [
            'product'   => $product,
            'families'  => $families,
            'units'     => $units,
            'tax_rates' => $taxRates,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(ProductRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->productService->create($request->validated());

        return redirect()->route('products.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): \Illuminate\View\View
    {
        $families = Family::orderBy('family_name')->get();
        $units    = Unit::orderBy('unit_name')->get();
        $taxRates = TaxRate::orderBy('tax_rate_name')->get();

        return view('products::form', [
            'product'   => $product,
            'families'  => $families,
            'units'     => $units,
            'tax_rates' => $taxRates,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(ProductRequest $request, Product $product): \Illuminate\Http\RedirectResponse
    {
        $this->productService->update($product->product_id, $request->validated());

        return redirect()->route('products.index')
            ->with('alert_success', trans('record_successfully_saved'));
    }

    /**
     * Delete the specified product.
     */
    public function destroy(Product $product): \Illuminate\Http\RedirectResponse
    {
        $this->productService->delete($product->product_id);

        return redirect()->route('products.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
