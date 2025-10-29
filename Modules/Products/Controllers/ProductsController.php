<?php

namespace Modules\Products\Http\Controllers;

use Modules\Products\Entities\Product;
use Modules\Products\Entities\Family;
use Modules\Products\Entities\Unit;
use Modules\Products\Entities\TaxRate;

/**
 * ProductsController
 * 
 * Handles product catalog management
 */
class ProductsController
{
    /**
     * Display a paginated list of products
     * 
     * @param int $page Page number for pagination
     * @return \Illuminate\View\View
     * 
     * @legacy-function index
     * @legacy-file application/modules/products/controllers/Products.php
     * @legacy-line 32
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $products = Product::with(['family', 'unit', 'taxRate'])
            ->orderBy('product_name')
            ->paginate(15, ['*'], 'page', $page);

        return view('products::index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_products'),
            'filter_method' => 'filter_products',
            'products' => $products,
        ]);
    }

    /**
     * Display form for creating or editing a product
     * 
     * @param int|null $id Product ID (null for create)
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * 
     * @legacy-function form
     * @legacy-file application/modules/products/controllers/Products.php
     * @legacy-line 49
     */
    public function form(?int $id = null)
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->route('products.index');
        }

        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate input
            $rules = Product::validationRules();
            $validated = request()->validate($rules);
            
            if ($id) {
                // Update existing
                $product = Product::findOrFail($id);
                $product->update($validated);
            } else {
                // Create new
                Product::create($validated);
            }
            
            return redirect()->route('products.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        // Load existing record for editing
        if ($id) {
            $product = Product::find($id);
            if (!$product) {
                abort(404);
            }
        } else {
            // New product
            $product = new Product();
        }

        // Load related data for dropdowns
        $families = Family::orderBy('family_name')->get();
        $units = Unit::orderBy('unit_name')->get();
        $taxRates = TaxRate::orderBy('tax_rate_name')->get();

        return view('products::form', [
            'product' => $product,
            'families' => $families,
            'units' => $units,
            'tax_rates' => $taxRates,
        ]);
    }

    /**
     * Delete a product
     * 
     * @param int $id Product ID
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function delete
     * @legacy-file application/modules/products/controllers/Products.php
     * @legacy-line 87
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();
        
        return redirect()->route('products.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }
}
