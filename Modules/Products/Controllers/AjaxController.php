<?php

namespace Modules\Products\Controllers;

use Modules\Products\Models\Family;
use Modules\Products\Models\Product;
use Modules\Products\Services\ProductService;

/**
 * AjaxController.
 *
 * Handles AJAX requests for products
 * Migrated from CodeIgniter Ajax controller
 */
class AjaxController
{
    /**
     * Product service instance.
     *
     * @var ProductService
     */
    protected ProductService $productService;

    /**
     * Constructor.
     *
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    /**
     * Display modal for product lookups.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function modal_product_lookups()
    {
        $filter_product = request()->get('filter_product');
        $filter_family  = request()->get('filter_family');
        $reset_table    = request()->get('reset_table');

        $query = Product::query();

        if ( ! empty($filter_family)) {
            $query->byFamily($filter_family);
        }

        if ( ! empty($filter_product)) {
            $query->search($filter_product);
        }

        $products = $query->get();
        $families = Family::ordered()->get();

        $default_item_tax_rate = get_setting('default_item_tax_rate');
        $default_item_tax_rate = $default_item_tax_rate !== '' ? $default_item_tax_rate : 0;

        $data = [
            'products'              => $products,
            'families'              => $families,
            'filter_product'        => $filter_product,
            'filter_family'         => $filter_family,
            'default_item_tax_rate' => $default_item_tax_rate,
        ];

        if ($filter_product || $filter_family || $reset_table) {
            return view('products::partial_product_table_modal', $data);
        }

        return view('products::modal_product_lookups', $data);
    }

    /**
     * Process product selections and return JSON.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function process_product_selections()
    {
        $product_ids = request()->post('product_ids');

        $products = $this->productService->getByIds($product_ids);

        foreach ($products as $product) {
            $product->product_price = format_amount($product->product_price);
        }

        return response()->json($products);
    }
}
