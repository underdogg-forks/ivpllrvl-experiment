<?php

namespace Modules\Products\Http\Controllers;

use Modules\Products\Entities\Product;
use Modules\Products\Entities\Family;
use Modules\Products\Entities\Unit;
use Modules\Products\Entities\TaxRate;

/**
 * ProductsController
 * 
 * Handles product management
 * Migrated from CodeIgniter Products controller
 */
class ProductsController
{
    /**
     * Display a listing of products.
     *
     * @param int $page
     * @return \Illuminate\Contracts\View\View
     */
    public function index($page = 0)
    {
        $products = Product::with(['family', 'unit', 'taxRate'])
            ->paginate(15);

        return view('products::products.index', [
            'filter_display' => true,
            'filter_placeholder' => trans('filter_products'),
            'filter_method' => 'filter_products',
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating/editing a product.
     *
     * @param int|null $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form($id = null)
    {
        // Handle cancel button
        if (request()->has('btn_cancel')) {
            return redirect()->to('products');
        }

        // Handle form submission
        if (request()->has('btn_submit')) {
            // Validate input
            $validated = request()->validate([
                'product_sku' => 'nullable|string|max:255',
                'product_name' => 'required|string|max:255',
                'product_description' => 'nullable|string',
                'product_price' => 'required|numeric',
                'purchase_price' => 'nullable|numeric',
                'provider_name' => 'nullable|string|max:255',
                'family_id' => 'nullable|integer',
                'unit_id' => 'nullable|integer',
                'tax_rate_id' => 'nullable|integer',
                'product_tariff' => 'nullable|string|max:255',
            ]);

            // Create or update product
            if ($id) {
                $product = Product::findOrFail($id);
                $product->update($validated);
            } else {
                Product::create($validated);
            }

            return redirect()->to('products');
        }

        // Load product for editing
        $product = null;
        if ($id) {
            $product = Product::find($id);
            if (!$product) {
                abort(404);
            }
        }

        // Load related data for dropdowns
        $families = Family::ordered()->get();
        $units = Unit::ordered()->get();
        $tax_rates = TaxRate::ordered()->get();

        return view('products::products.form', [
            'product' => $product,
            'families' => $families,
            'units' => $units,
            'tax_rates' => $tax_rates,
        ]);
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->to('products');
    }
}

