<div class="table-responsive">
    <table id="products_table" class="table table-hover table-bordered table-striped">
        <tr>
            <th>&nbsp;</th>
            <th>{{ trans('product_sku') }}</th>
            <th>{{ trans('family_name') }}</th>
            <th>{{ trans('product_name') }}</th>
            <th>{{ trans('product_description') }}</th>
            <th class="amount">{{ trans('product_price') }}</th>
        </tr>
        @foreach ($products as $product)
            <tr class="product">
                <td class="text-left">
                    <input type="checkbox" name="product_ids[]"
                           value="{{ $product->product_id }}">
                </td>
                <td nowrap class="text-left">
                    <b>{{ $product->product_sku }}</b>
                </td>
                <td>
                    <b>{{ $product->family_name }}</b>
                </td>
                <td>
                    <b>{{ $product->product_name }}</b>
                </td>
                <td>
                    {!! nl2br(e($product->product_description)) !!}
                </td>
                <td class="amount">
                    {{ format_currency($product->product_price) }}
                </td>
            </tr>
        @endforeach

    </table>
</div>
