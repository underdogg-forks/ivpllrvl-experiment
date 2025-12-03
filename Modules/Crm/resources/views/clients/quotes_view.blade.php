@php
$global_discount = $quote->quote_discount_percent > 0 ? format_amount($quote->quote_discount_percent) . '%' : format_currency($quote->quote_discount_amount);
$global_taxes = null;
if ($quote_tax_rates) {
    $global_taxes_arr = [];
    foreach ($quote_tax_rates as $quote_tax_rate) {
        $global_taxes_arr[] = $quote_tax_rate->quote_tax_rate_name . ' (' . format_amount($quote_tax_rate->quote_tax_rate_percent) . '%): '
                          . format_currency($quote_tax_rate->quote_tax_rate_amount);
    }
    $global_taxes = implode('<br>', $global_taxes_arr);
}
@endphp
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <h1 class="headerbar-title text-xl font-bold">{{ trans('quote') }} #{{ $quote->quote_number }}</h1>

    <div class="headerbar-item">
        <div class="btn-group btn-group-sm flex gap-2">
@if(in_array($quote->quote_status_id, [2, 3]))
            <a href="{{ route('guest.approve', $quote->quote_id) }}"
               class="btn btn-success inline-flex items-center gap-2 px-3 py-1.5 bg-green-600 dark:bg-green-500 rounded-md text-sm font-medium text-white hover:bg-green-700">
                <i class="fa fa-check"></i>
                {{ trans('approve_this_quote') }}
            </a>
            <a href="{{ route('guest.reject', $quote->quote_id) }}"
               class="btn btn-danger inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 dark:bg-red-500 rounded-md text-sm font-medium text-white hover:bg-red-700">
                <i class="fa fa-times-circle"></i>
                {{ trans('reject_this_quote') }}
            </a>
@elseif($quote->quote_status_id == 4)
            <a href="#" class="btn btn-success disabled inline-flex items-center gap-2 px-3 py-1.5 bg-green-600 rounded-md text-sm font-medium text-white opacity-60 cursor-not-allowed">
                <i class="fa fa-check"></i>
                {{ trans('quote_approved') }}
            </a>
@elseif($quote->quote_status_id == 5)
            <a href="#" class="btn btn-danger disabled inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 rounded-md text-sm font-medium text-white opacity-60 cursor-not-allowed">
                <i class="fa fa-times-circle"></i>
                {{ trans('quote_rejected') }}
            </a>
@endif
            <a href="{{ route('guest.generate-pdf', $quote_id) }}"
               class="btn btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50" id="btn_generate_pdf" target="_blank">
                <i class="fa fa-print"></i> {{ trans('download_pdf') }}
            </a>
        </div>

    </div>

</div>

<div id="content">

    @include('core::layout.alerts')

    <div class="quote">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

            <div class="md:col-span-9">
                <div>

                    <h3 class="text-lg font-semibold mb-4">{{ htmlspecialchars(format_client($quote)) }}</h3>
                    <div class="client-address">
                        @include('crm::clients.partial_client_address', ['client' => $quote])
                    </div>
@if($quote->client_phone)
                    <br><span><strong>{{ trans('phone') }}:</strong> {{ htmlspecialchars($quote->client_phone) }}</span>
@endif
@if($quote->client_email)
                    <br><span><strong>{{ trans('email') }}:</strong> {{ htmlspecialchars($quote->client_email) }}</span>
@endif
                </div>
            </div>

            <div class="md:col-span-3">

                <table class="table table-bordered w-full">
                    <tr>
                        <td class="px-4 py-2">{{ trans('quote') }} #</td>
                        <td class="px-4 py-2">{{ $quote->quote_number }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2">{{ trans('date') }}</td>
                        <td class="px-4 py-2">{{ date_from_mysql($quote->quote_date_created) }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2">{{ trans('due_date') }}</td>
                        <td class="px-4 py-2">{{ date_from_mysql($quote->quote_date_expires) }}</td>
                    </tr>
                </table>

            </div>

        </div>

        <br/>
        <div class="overflow-x-auto">
            <table class="table table-bordered w-full">
                <thead>
                <tr>
                    <th class="px-4 py-2"></th>
                    <th class="px-4 py-2">{{ trans('item') }} / {{ trans('description') }}</th>
                    <th class="px-4 py-2"></th>
                    <th class="px-4 py-2"></th>
                    <th class="px-4 py-2"></th>
                </tr>
                </thead>
@foreach($items as $i => $item)
                <tbody class="item">
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td rowspan="2" class="w-5 text-center px-4 py-2">{{ 1 + $i }}</td>
                    <td class="px-4 py-2">{{ htmlspecialchars($item->item_name) }}</td>
                    <td class="px-4 py-2">
                        <span class="float-left">{{ trans('quantity') }}</span>
                        <span class="float-right amount">{{ format_quantity($item->item_quantity) }} {{ htmlspecialchars($item->item_product_unit) }}</span>
                    </td>
                    <td class="px-4 py-2">
                        <span class="float-left">{{ trans('price') }}</span>
                        <span class="float-right amount">{{ format_currency($item->item_price) }}</span>
                    </td>
                    <td class="px-4 py-2">
                        <span class="float-left">{{ trans('subtotal') }}</span>
                        <span class="float-right amount">{{ format_currency($item->item_subtotal) }}</span>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-2 text-muted text-gray-600 dark:text-gray-400">{!! nl2br(htmlspecialchars($item->item_description)) !!}</td>
                    <td class="px-4 py-2">
                        <span class="float-left">{{ trans('discount') }}</span>
                        <span class="float-right amount">
                            <span data-toggle="tooltip" data-placement="bottom" title="{{ trans('item_discount') }}">
                                {{ format_currency($item->item_discount) }}
                            </span>
@php
        // New Discount calculation - since v1.6.3
        $item_global_discount = $legacy_calculation ? 0 : $item->item_subtotal - ($item->item_total - $item->item_tax_total + $item->item_discount);
@endphp
@if($item_global_discount)
                            <span data-toggle="tooltip" data-placement="bottom" title="{{ trans('global_discount') }}">
                                + {{ format_currency($item_global_discount) }}
                            </span>
                            <span data-toggle="tooltip" data-placement="bottom" title="{{ trans('discount') }} ({{ trans('subtotal') }})">
                                = {{ format_currency($item_global_discount + $item->item_discount) }}
                            </span>
@endif
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <span class="float-left">{{ trans('tax') }}</span>
                        <span class="float-right amount">{{ $item->item_tax_rate_percent ? $item->item_tax_rate_name . ' (' . format_amount($item->item_tax_rate_percent) . '%): ' : '' }}{{ format_currency($item->item_tax_total) }}</span>
                    </td>
                    <td class="px-4 py-2">
                        <span class="float-left">{{ trans('total') }}</span>
                        <span class="float-right amount">{{ format_currency($item->item_total) }}</span>
                    </td>
                </tr>
                </tbody>
@endforeach
            </table>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-bordered w-full">
                <thead>
                <tr>
@if(!$legacy_calculation)
                    <th class="px-4 py-2 text-right">{{ trans('global_discount') }}</th>
@endif
                    <th class="px-4 py-2 text-right">{{ trans('subtotal') }}</th>
                    <th class="px-4 py-2 text-right">{{ trans('item_tax') }}</th>
@if($quote_tax_rates)
                    <th class="px-4 py-2 text-right">{{ trans('quote_tax') }}</th>
@endif
@if($legacy_calculation)
                    <th class="px-4 py-2 text-right">{{ trans('global_discount') }}</th>
@endif
                    <th class="px-4 py-2 text-right">{{ trans('total') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
@if(!$legacy_calculation)
                    <td class="px-4 py-2 amount">{{ $global_discount }}</td>
@endif
                    <td class="px-4 py-2 amount">{{ format_currency($quote->quote_item_subtotal) }}</td>
                    <td class="px-4 py-2 amount">{{ format_currency($quote->quote_item_tax_total) }}</td>
@if($quote_tax_rates)
                    <td class="px-4 py-2 amount">{!! $global_taxes !!}</td>
@endif
@if($legacy_calculation)
                    <td class="px-4 py-2 amount">{{ $global_discount }}</td>
@endif
                    <td class="px-4 py-2 amount"><b>{{ format_currency($quote->quote_total) }}</b></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="md:col-span-6">

        @php _dropzone_html(); @endphp

    </div>
</div>

@php
_dropzone_script($quote->quote_url_key, $quote->client_id, 'guest/get', false);
@endphp
