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
                    <div x-data="{ open: false }" class="relative inline-block text-left">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-elevated border border-primary-dark rounded-md text-sm font-medium text-primary hover:bg-hover focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </button>
                        <ul x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-1 w-40 bg-elevated border border-primary rounded-md shadow-lg z-50">
                            <li>
                                <a href="{{ route('products.form', $product->product_id) }}"
                                   class="block px-4 py-2 text-sm text-primary hover:bg-hover">
                                    <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('products.delete', $product->product_id) }}"
                                      method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-hover"
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
