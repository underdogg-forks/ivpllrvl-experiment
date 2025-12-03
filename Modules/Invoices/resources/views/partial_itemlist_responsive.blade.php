@php
$invoice_disabled = $invoice->is_read_only != 1 ? '' : ' disabled="disabled"';
@endphp

<div class="row">
    <div id="item_table" class="items table col-xs-12">
        <div id="new_row" class="form-group details-box" style="display: none;">
            <div class="row">
                <div class="col-xs-12 col-sm-7 col-md-6 col-lg-5">
                    <div class="row">
                        <div class="col-xs-12 col-sm-1">
                            <button type="button" class="btn btn-link up" title="{{ trans('move_up') }}">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-link down" title="{{ trans('move_down') }}">
                                <i class="fa fa-chevron-down"></i>
                            </button>
@if($invoice->invoice_is_recurring)
                                <i title="{{ trans('recurring') }}" class="js-item-recurrence-toggler cursor-pointer fa fa-calendar-o text-muted"></i>
                                <input type="hidden" name="item_is_recurring" value=""/>
@endif
                            <button type="button" class="btn_delete_item btn btn-link btn-sm" title="{{ trans('delete') }}">
                                <i class="fa fa-trash-o text-danger"></i>
                            </button>
                        </div>

                        <div class="col-xs-12 col-sm-11">
                            <div class="input-group flex">
                                <label for="item_name" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('item') }}</label>
                                <input type="text" name="item_name" id="item_name" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md" value="">
                            </div>
                            <div class="input-group flex">
@if($invoice->sumex_id == '')
                                <label for="item_description" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('description') }}</label>
                                <textarea name="item_description" id="item_description" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md"></textarea>
@else
                                <label for="item_date" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('date') }}</label>
                                <input type="text" name="item_date" id="item_date" class="form-control datepicker flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md"
                                       value="{{ format_date(date('y-m-d')) }}"{{ $invoice_disabled }}>
@endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-5 col-md-6 col-lg-7">
                    <div class="row">
                        <div class="col-xs-12 col-lg-6">
                            <div class="input-group flex">
                                <label for="item_quantity" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('quantity') }}</label>
                                <input type="text" name="item_quantity" id="item_quantity" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md" value="">
                            </div>
                            <div class="input-group flex">
                                <label for="item_product_unit_id" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('product_unit') }}</label>
                                <select name="item_product_unit_id" id="item_product_unit_id" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md">
                                    <option value="0">{{ trans('none') }}</option>
@foreach($units as $unit)
                                    <option value="{{ $unit->unit_id }}">
                                        {{ $unit->unit_name }}/{{ $unit->unit_name_plrl }}
                                    </option>
@endforeach
                                </select>
                            </div>
                            <div class="input-group flex">
                                <label for="item_price" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('price') }}</label>
                                <input type="text" name="item_price" id="item_price" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" value="">
                                <div class="input-group-addon px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-r-md">{{ get_setting('currency_symbol') }}</div>
                            </div>
@if(!$legacy_calculation)
                            @include('core::partial.itemlist_responsive_item_discount_input')
@endif
                            <div class="input-group flex">
                                <label for="item_tax_rate_id" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('tax_rate') }}</label>
                                <select name="item_tax_rate_id" id="item_tax_rate_id" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md">
                                    <option value="0">{{ trans('none') }}</option>
@foreach($tax_rates as $tax_rate)
                                    <option value="{{ $tax_rate->tax_rate_id }}"
                                        {{ check_select(get_setting('default_item_tax_rate'), $tax_rate->tax_rate_id) }}>
                                        {{ format_amount($tax_rate->tax_rate_percent) }}% - {{ $tax_rate->tax_rate_name }}
                                    </option>
@endforeach
                                </select>
                            </div>
@if($legacy_calculation)
                            @include('core::partial.itemlist_responsive_item_discount_input')
@endif
                        </div>

                        <input type="hidden" name="invoice_id" value="{{ $invoice_id }}">
                        <input type="hidden" name="item_id" value="">
                        <input type="hidden" name="item_product_id" value="">
                        <input type="hidden" name="item_task_id" class="item-task-id" value="">

                        <div class="col-xs-12 col-md-6 text-right">
                            <div class="grid grid-cols-12 gap-2 mb-1">
                                <div class="col-span-9 sm:col-span-8">
                                    {{ trans('subtotal') }}:
                                </div>
                                <div class="col-span-3 sm:col-span-4">
                                    <span name="subtotal"></span>
                                </div>
                            </div>
@if(!$legacy_calculation)
                            @include('core::partial.itemlist_responsive_item_discount_show')
@endif
                            <div class="grid grid-cols-12 gap-2 mb-1">
                                <div class="col-span-9 sm:col-span-8">
                                    {{ trans('tax') }}:
                                </div>
                                <div class="col-span-3 sm:col-span-4">
                                    <span name="item_tax_total"></span>
                                </div>
                            </div>
@if($legacy_calculation)
                            @include('core::partial.itemlist_responsive_item_discount_show')
@endif
                            <div class="grid grid-cols-12 gap-2 mb-1">
                                <strong>
                                    <div class="col-span-9 sm:col-span-8">
                                        {{ trans('total') }}:
                                    </div>
                                    <div class="col-span-3 sm:col-span-4">
                                        <span name="item_total"></span>
                                    </div>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@foreach($items as $item)
        <div class="form-group details-box item">
            <div class="row">
                <div class="col-xs-12 col-sm-7 col-md-6 col-lg-5">
                    <div class="row">
                        <div class="col-xs-12 col-sm-1">
                            <button type="button" class="btn btn-link up" title="{{ trans('move_up') }}"{{ $invoice_disabled }}>
                                <i class="fa fa-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-link down" title="{{ trans('move_down') }}"{{ $invoice_disabled }}>
                                <i class="fa fa-chevron-down"></i>
                            </button>
@if($invoice->invoice_is_recurring)
    @php
        if ($item->item_is_recurring == 1 || null === $item->item_is_recurring) {
            $item_recurrence_state = '1';
            $item_recurrence_class = 'fa-calendar-check-o text-success';
        } else {
            $item_recurrence_state = '0';
            $item_recurrence_class = 'fa-calendar-o text-muted';
        }
    @endphp
                            <i title="{{ trans('recurring') }}"
                                class="js-item-recurrence-toggler cursor-pointer fa {{ $item_recurrence_class }}">
                            </i>
                            <input type="hidden" name="item_is_recurring" value="{{ $item_recurrence_state }}"/>
@endif
@if($invoice->is_read_only != 1)
                            <button type="button" class="btn_delete_item btn btn-link" title="{{ trans('delete') }}" data-item-id="{{ $item->item_id }}">
                                <i class="fa fa-trash-o text-danger"></i>
                            </button>
@endif
                        </div>

                        <div class="col-xs-12 col-sm-11">
                            <input type="hidden" name="invoice_id" value="{{ $invoice_id }}">
                            <input type="hidden" name="item_id" value="{{ $item->item_id }}"{{ $invoice_disabled }}>
                            <input type="hidden" name="item_task_id" class="item-task-id" value="{{ $item->item_task_id ?? '' }}">
                            <input type="hidden" name="item_product_id" value="{{ $item->item_product_id }}">

                            <div class="input-group flex">
                                <label for="item_name_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('item') }}</label>
                                <input type="text" name="item_name" id="item_name_{{ $item->item_id }}" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md" value="{{ htmlspecialchars($item->item_name) }}"{{ $invoice_disabled }}>
                            </div>

                            <div class="input-group flex">
@if($invoice->sumex_id == '')
                                <label for="item_description_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('description') }}</label>
                                <textarea name="item_description" id="item_description_{{ $item->item_id }}" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md"{{ $invoice_disabled }}>{{ htmlspecialchars($item->item_description) }}</textarea>
@else
                                <label for="item_date_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('date') }}</label>
                                <input type="text" name="item_date" id="item_date_{{ $item->item_id }}" class="form-control datepicker flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md" value="{{ format_date($item->item_date) }}"{{ $invoice_disabled }}>
@endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-5 col-md-6 col-lg-7">
                    <div class="row">
                        <div class="col-xs-12 col-lg-6">
                            <div class="input-group flex">
                                <label for="item_quantity_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('quantity') }}</label>
                                <input type="text" name="item_quantity" id="item_quantity_{{ $item->item_id }}" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md" value="{{ format_quantity($item->item_quantity) }}"{{ $invoice_disabled }}>
                            </div>
                            <div class="input-group flex">
                                <label for="item_product_unit_id_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('product_unit') }}</label>
                                <select name="item_product_unit_id" id="item_product_unit_id_{{ $item->item_id }}" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md"{{ $invoice_disabled }}>
                                    <option value="0">{{ trans('none') }}</option>
@foreach($units as $unit)
                                    <option value="{{ $unit->unit_id }}"
                                        {{ check_select($item->item_product_unit_id, $unit->unit_id) }}>
                                        {{ htmlspecialchars($unit->unit_name) }}/{{ htmlspecialchars($unit->unit_name_plrl) }}
                                    </option>
@endforeach
                                </select>
                            </div>
                            <div class="input-group flex">
                                <label for="item_price_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('price') }}</label>
                                <input type="text" name="item_price" id="item_price_{{ $item->item_id }}" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                       value="{{ format_amount($item->item_price) }}"{{ $invoice_disabled }}>
                                <div class="input-group-addon px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-r-md">{{ get_setting('currency_symbol') }}</div>
                            </div>
@if(!$legacy_calculation)
                            @include('core::partial.itemlist_responsive_item_discount_input', ['item' => $item])
@endif
                            <div class="input-group flex">
                                <label for="item_tax_rate_id_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('tax_rate') }}</label>
                                <select name="item_tax_rate_id" id="item_tax_rate_id_{{ $item->item_id }}" class="form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-md"{{ $invoice_disabled }}>
                                    <option value="0">{{ trans('none') }}</option>
@foreach($tax_rates as $tax_rate)
                                    <option value="{{ $tax_rate->tax_rate_id }}"
                                        {{ check_select($item->item_tax_rate_id, $tax_rate->tax_rate_id) }}>
                                        {{ format_amount($tax_rate->tax_rate_percent) }}% - {{ $tax_rate->tax_rate_name }}
                                    </option>
@endforeach
                                </select>
                            </div>
@if($legacy_calculation)
                            @include('core::partial.itemlist_responsive_item_discount_input', ['item' => $item])
@endif
                            </div>
                            <div class="col-xs-12 col-md-6 text-right">
                                <div class="grid grid-cols-12 gap-2 mb-1">
                                    <div class="col-span-9 sm:col-span-8">
                                        {{ trans('subtotal') }}:
                                    </div>
                                    <div class="col-span-3 sm:col-span-4">
                                        {{ format_currency($item->item_subtotal) }}
                                    </div>
                                </div>
@if(!$legacy_calculation)
                                @include('core::partial.itemlist_responsive_item_discount_show', ['item' => $item])
@endif
                                <div class="grid grid-cols-12 gap-2 mb-1">
                                    <div class="col-span-9 sm:col-span-8">
                                        {{ trans('tax') }}:
                                    </div>
                                    <div class="col-span-3 sm:col-span-4">
                                        {{ format_currency($item->item_tax_total) }}
                                    </div>
                                </div>
@if($legacy_calculation)
                                @include('core::partial.itemlist_responsive_item_discount_show', ['item' => $item])
@endif
                                <div class="grid grid-cols-12 gap-2 mb-1">
                                    <div class="col-span-9 sm:col-span-8">
                                        <b>{{ trans('total') }}:</b>
                                    </div>
                                    <div class="col-span-3 sm:col-span-4">
                                        <b>{{ format_currency($item->item_total) }}</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endforeach
    </div>
</div>

<br>

<div class="row">
    <div class="col-xs-12 col-md-4">
        <div class="btn-group flex gap-2">
@if($invoice->is_read_only != 1)
            <a href="javascript:void(0);" class="btn_add_row btn btn-sm btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                <i class="fa fa-plus"></i> {{ trans('add_new_row') }}
            </a>
            <a href="javascript:void(0);" class="btn_add_product btn btn-sm btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                <i class="fa fa-database"></i>
                {{ trans('add_product') }}
            </a>
            <a href="javascript:void(0);" class="btn_add_task btn btn-sm btn-default{{ get_setting('projects_enabled') == 1 ? '' : ' hidden' }} inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                <i class="fa fa-database"></i> {{ trans('add_task') }}
            </a>
@endif
        </div>
    </div>

    <div class="col-xs-12 visible-xs visible-sm"><br></div>

    <div class="col-xs-12 col-md-6 col-md-offset-2 col-lg-4 col-lg-offset-4">
        <table class="table table-bordered text-right">
@if(!$legacy_calculation)
            @include('invoices::partial_itemlist_table_invoice_discount')
@endif
            <tr>
                <td style="width: 40%;">{{ trans('subtotal') }}</td>
                <td style="width: 60%;"
                class="amount">{{ format_currency($invoice->invoice_item_subtotal) }}</td>
            </tr>
            <tr>
                <td>{{ trans('item_tax') }}</td>
                <td class="amount">{{ format_currency($invoice->invoice_item_tax_total) }}</td>
            </tr>
@if($legacy_calculation)
            <tr>
                <td>{{ trans('invoice_tax') }}</td>
                <td>
    @if($invoice_tax_rates)
        @foreach($invoice_tax_rates as $invoice_tax_rate)
                    <form method="post"
                          action="{{ route('invoices.delete-invoice-tax', [$invoice->invoice_id, $invoice_tax_rate->invoice_tax_rate_id]) }}">
                    @csrf
                    <span class="amount">
                        {{ format_currency($invoice_tax_rate->invoice_tax_rate_amount) }}
                    </span>
                    <span class="text-muted">
                        {{ htmlspecialchars($invoice_tax_rate->invoice_tax_rate_name) }} {{ format_amount($invoice_tax_rate->invoice_tax_rate_percent) }}
                    </span>
                    <button type="submit" class="btn btn-xs btn-link" onclick="var Y=confirm('{{ trans('delete_tax_warning') }}');if(Y)show_loader();return Y;">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </form>
        @endforeach
    @else
        {{ format_currency('0') }}
    @endif
                </td>
            </tr>
            @include('invoices::partial_itemlist_table_invoice_discount')
@endif
            <tr>
                <td>{{ trans('total') }}</td>
                <td class="amount"><b>{{ format_currency($invoice->invoice_total) }}</b></td>
            </tr>
            <tr>
                <td>{{ trans('paid') }}</td>
                <td class="amount"><b>{{ format_currency($invoice->invoice_paid) }}</b></td>
            </tr>
            <tr>
                <td><b>{{ trans('balance') }}</b></td>
                <td class="amount"><b>{{ format_currency($invoice->invoice_balance) }}</b></td>
            </tr>
        </table>
    </div>

</div>
