<div class="table-responsive">
    <table class="table table-hover table-striped">

        <thead>
        <tr>
            <th>{{ trans('family') }}</th>
            <th>{{ trans('product_sku') }}</th>
            <th>{{ trans('product_name') }}</th>
            <th>{{ trans('product_description') }}</th>
            <th class="amount last">{{ trans('product_price') }}</th>
            <th>{{ trans('product_unit') }}</th>
            <th>{{ trans('tax_rate') }}</th>
            @if (get_setting('sumex') == '1')
                <th>{{ trans('product_tariff') }}</th>
            @endif
            <th>{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($products as $product)
            <tr>
                <td><a href="{{ route('families.form', $product->family_id) }}"><i class="fa fa-edit"></i> {{ $product->family_name }}</a></td>
                <td>{{ $product->product_sku }}</td>
                <td><a href="{{ route('products.form', $product->product_id) }}"><i class="fa fa-edit"></i> {{ $product->product_name }}</a></td>
                <td>{!! nl2br(e($product->product_description)) !!}</td>
                <td class="amount last">{{ format_currency($product->product_price) }}</td>
                <td>{{ $product->unit_name }}</td>
                <td>{{ $product->tax_rate_id ? $product->tax_rate_name : trans('none') }}</td>
                @if (get_setting('sumex') == '1')
                    <td>{{ $product->product_tariff }}</td>
                @endif
                <td>
                    <div class="options btn-group">
                        <a class="btn btn-default btn-sm dropdown-toggle"
                           data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('products.form', $product->product_id) }}">
                                    <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('products.delete', $product->product_id) }}"
                                      method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-button"
                                            onclick="return confirm('{{ trans('delete_record_warning') }}');">
                                        <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>
</div>
