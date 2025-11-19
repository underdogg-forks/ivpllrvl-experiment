<?php

namespace Modules\Products\Controllers;

use Modules\Core\Support\TranslationHelper;
use Modules\Core\Traits\HandlesDeletion;
use Modules\Products\Models\Family;
use Modules\Products\Models\Product;
use Modules\Products\Models\TaxRate;
use Modules\Products\Models\Unit;
use Modules\Products\Services\ProductService;

/**
 * ProductsController.
 *
 * Manages product CRUD operations
 * Implements SOLID principles, DRY pattern, and early returns
 *
 * @legacy-file application/modules/products/controllers/Products.php
 */
class ProductsController
{
    use HandlesDeletion;

    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a paginated list of products.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/products/controllers/Products.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $products = Product::query()
            ->with(['family', 'unit', 'taxRate'])
            ->orderBy('product_name')
            ->paginate(15, ['*'], 'page', $page);

        return view('products::products_index', [
            'filter_display'     => true,
            'filter_placeholder' => TranslationHelper::trans('filter_products'),
            'filter_method'      => 'filter_products',
            'products'           => $products,
        ]);
    }

    /**
     * Display form for creating or editing a product.
     *
     * @param int|null $id Product ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     *
     * @legacy-file application/modules/products/controllers/Products.php
     */
    public function form(?int $id = null)
    {
        // Early return for cancel action
        if (request()->post('btn_cancel')) {
            return redirect()->route('products.index');
        }

        // Early return for form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            return $this->handleFormSubmission($id);
        }

        return $this->showForm($id);
    }

    /**
     * Delete a product with security checks and error handling.
     *
     * Implements:
     * - Early returns for validation
     * - DRY principle via HandlesDeletion trait
     * - SOLID principles with single responsibility
     * - Proper error handling
     * - Business rule: Cannot delete products used in invoices
     *
     * @param int $id Product ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     *
     * @legacy-file application/modules/products/controllers/Products.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        // Validate ID
        if ($id <= 0) {
            return $this->redirectWithError('products.index', TranslationHelper::trans('invalid_product_id'));
        }

        // Check if product exists
        $product = $this->productService->find($id);
        if ( ! $product) {
            return $this->redirectWithError('products.index', TranslationHelper::trans('product_not_found'));
        }

        // Business rule: Cannot delete products that are used in invoices
        if ( ! $this->productService->canDelete($id)) {
            $itemCount = $this->productService->getInvoiceItemCount($id);

            return $this->redirectWithError(
                'products.index',
                TranslationHelper::trans('product_deletion_not_allowed_invoice_items', ['count' => $itemCount])
            );
        }

        // Execute delete with standardized error handling
        return $this->executeDelete(
            fn () => $this->productService->delete($id),
            'products.index'
        );
    }

    /**
     * Handle form submission for create/update.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleFormSubmission(?int $id): \Illuminate\Http\RedirectResponse
    {
        $validated = request()->validate([
            'product_name'        => 'required|string|max:255',
            'product_sku'         => 'nullable|string|max:255',
            'product_description' => 'nullable|string',
            'product_price'       => 'required|numeric|min:0',
            'family_id'           => 'nullable|integer|exists:ip_families,family_id',
            'unit_id'             => 'nullable|integer|exists:ip_units,unit_id',
            'tax_rate_id'         => 'nullable|integer|exists:ip_tax_rates,tax_rate_id',
        ]);

        if ($id) {
            $this->productService->update($id, $validated);
        } else {
            $this->productService->create($validated);
        }

        return redirect()->route('products.index')
            ->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
    }

    /**
     * Show the form for create/edit.
     *
     * @param int|null $id
     *
     * @return \Illuminate\View\View
     */
    protected function showForm(?int $id): \Illuminate\View\View
    {
        $product = $id ? $this->productService->find($id) : new Product();

        // Early return with 404 if product not found
        if ($id && ! $product) {
            abort(404);
        }

        $families = Family::query()->orderBy('family_name')->get();
        $units    = Unit::query()->orderBy('unit_name')->get();
        $taxRates = TaxRate::query()->orderBy('tax_rate_name')->get();

        return view('products::products_form', [
            'product'   => $product,
            'families'  => $families,
            'units'     => $units,
            'tax_rates' => $taxRates,
        ]);
    }
}
