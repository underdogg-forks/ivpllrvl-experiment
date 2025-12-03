@php
$global_discount = $invoice->invoice_discount_percent > 0 ? format_amount($invoice->invoice_discount_percent) . '%' : format_currency($invoice->invoice_discount_amount);
$global_taxes = null;
if ($invoice_tax_rates) {
    $global_taxes_arr = [];
    foreach ($invoice_tax_rates as $invoice_tax_rate) {
        $global_taxes_arr[] = $invoice_tax_rate->invoice_tax_rate_name . ' (' . format_amount($invoice_tax_rate->invoice_tax_rate_percent) . '%): '
                          . format_currency($invoice_tax_rate->invoice_tax_rate_amount);
    }
    $global_taxes = implode('<br>', $global_taxes_arr);
}
@endphp
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <div class="headerbar-item">
        <div class="btn-group btn-group-sm flex gap-2">
@if($invoice->invoice_balance == 0 || $invoice->invoice_status_id >= 4)
            <button class="btn btn-success disabled opacity-60 cursor-not-allowed">
                <i class="fa fa-check"></i> {{ trans('paid') }}
            </button>
@elseif($enable_online_payments)
            <a href="{{ route('guest.form', $invoice->invoice_url_key) }}"
               class="fi-btn fi-btn-primary">
                <i class="fa fa-credit-card"></i>
                {{ trans('pay_now') }}
            </a>
@endif
            <a href="{{ route('guest.generate-pdf', $invoice->invoice_id) }}"
               class="btn btn-default" id="btn_generate_pdf" target="_blank">
                <i class="fa fa-print"></i> {{ trans('download_pdf') }}
            </a>
        </div>

    </div>
        </div>

    </div>

</div>

<div id="content">

    @include('core::layout.alerts')

    <div class="quote">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

            <div class="md:col-span-9">
                <div>

                    <h3 class="text-lg font-semibold mb-4">{{ htmlspecialchars(format_client($invoice)) }}</h3>
                    <div class="client-address">
                        @include('crm::clients.partial_client_address', ['client' => $invoice])
                    </div>
@if($invoice->client_phone)
                    <br><span><strong>{{ trans('phone') }}:</strong> {{ htmlspecialchars($invoice->client_phone) }}</span>
@endif
@if($invoice->client_email)
                    <br><span><strong>{{ trans('email') }}:</strong> {{ htmlspecialchars($invoice->client_email) }}</span>
@endif
                </div>
            </div>

            <div class="md:col-span-3">

                <table class="table table-bordered w-full">
                    <tr>
                        <td class="px-4 py-2">{{ trans('quote') }} #</td>
                        <td class="px-4 py-2">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2">{{ trans('date') }}</td>
                        <td class="px-4 py-2">{{ date_from_mysql($invoice->invoice_date_created) }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2">{{ trans('due_date') }}</td>
                        <td class="px-4 py-2">{{ date_from_mysql($invoice->invoice_date_expires) }}</td>
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
@if($invoice_tax_rates)
                    <th class="px-4 py-2 text-right">{{ trans('invoice_tax') }}</th>
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
                    <td class="px-4 py-2 amount">{{ format_currency($invoice->invoice_item_subtotal) }}</td>
                    <td class="px-4 py-2 amount">{{ format_currency($invoice->invoice_item_tax_total) }}</td>
@if($invoice_tax_rates)
                    <td class="px-4 py-2 amount">{!! $global_taxes !!}</td>
@endif
@if($legacy_calculation)
                    <td class="px-4 py-2 amount">{{ $global_discount }}</td>
@endif
                    <td class="px-4 py-2 amount"><b>{{ format_currency($invoice->invoice_total) }}</b></td>
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
_dropzone_script($invoice->invoice_url_key, $invoice->client_id, 'guest/get', false);
@endphp
