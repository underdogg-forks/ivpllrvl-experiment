@php
$invoice_disabled = $invoice->is_read_only != 1 ? '' : ' disabled="disabled"';
@endphp
<div class="overflow-x-auto">
    <table id="item_table" class="items table table-condensed table-bordered no-margin">

        <thead class="hidden">
        <tr>
            <th></th>
            <th>{{ trans('item') }}</th>
<!--
            <th>{{ trans('description') }}</th>
-->
            <th class="amount">{{ trans('quantity') }}</th>
            <th class="amount">{{ trans('price') }}</th>
            @if(!$legacy_calculation) <th class="amount">{{ trans('item_discount') }}</th> @endif
            <th class="amount">{{ trans('tax_rate') }}</th>
            @if($legacy_calculation) <th class="amount">{{ trans('item_discount') }}</th> @endif
<!--
            <th class="amount">{{ trans('subtotal') }}</th>
            <th class="amount">{{ trans('tax') }}</th>
-->
            <th class="amount">{{ trans('total') }}</th>
            <th></th>
        </tr>
        </thead>

        <tbody id="new_row" class="hidden">
        <tr>
            <td rowspan="2" class="td-icon">
                <i class="fa fa-arrows cursor-move"></i>
                @if($invoice->invoice_is_recurring)
                    <br/>
                    <i title="{{ trans('recurring') }}"
                       class="js-item-recurrence-toggler cursor-pointer fa fa-calendar-o text-muted"></i>
                    <input type="hidden" name="item_is_recurring" value=""/>
                @endif
            </td>
            <td class="td-text">
                <input type="hidden" name="invoice_id" value="{{ $invoice_id }}">
                <input type="hidden" name="item_id" value="">
                <input type="hidden" name="item_product_id" value="">
                <input type="hidden" name="item_task_id" class="item-task-id" value="">

                <div class="input-group">
                    <span class="input-group-addon">{{ trans('item') }}</span>
                    <input type="text" name="item_name" class="form-control" value="">
                </div>
            </td>
            <td class="td-amount td-quantity">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('quantity') }}</span>
                    <input type="text" name="item_quantity" class="form-control amount" value="">
                </div>
            </td>
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('price') }}</span>
                    <input type="text" name="item_price" class="form-control amount" value="">
                    <div class="input-group-addon">{{ get_setting('currency_symbol') }}</div>
                </div>
            </td>
@if(!$legacy_calculation)
            @include('core::partial.itemlist_table_item_discount_input')
@endif
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('tax_rate') }}</span>
                    <select name="item_tax_rate_id" class="form-control">
                        <option value="0">{{ trans('none') }}</option>
@foreach($tax_rates as $tax_rate)
                        <option value="{{ $tax_rate->tax_rate_id }}"
                            {{ $tax_rate->tax_rate_id == get_setting('default_item_tax_rate') ? 'selected' : '' }}>
                            {{ format_amount($tax_rate->tax_rate_percent) }}% - {{ $tax_rate->tax_rate_name }}
                        </option>
@endforeach
                    </select>
                </div>
            </td>
@if($legacy_calculation)
            @include('core::partial.itemlist_table_item_discount_input')
@endif
            <td class="td-icon text-right td-vert-middle">
                <button type="button" class="btn_delete_item btn btn-link btn-sm" title="{{ trans('delete') }}">
                    <i class="fa fa-trash-o text-danger"></i>
                </button>
            </td>
        </tr>
        <tr>
@if($invoice->sumex_id == '')
            <td class="td-textarea">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('description') }}</span>
                    <textarea name="item_description" class="form-control"></textarea>
                </div>
            </td>
@else
            <td class="td-date">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('date') }}</span>
                    <input type="text" name="item_date" class="form-control datepicker"
                           value="{{ format_date(date('y-m-d')) }}"{{ $invoice_disabled }}>
                </div>
            </td>
@endif
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('product_unit') }}</span>
                    <select name="item_product_unit_id" class="form-control">
                        <option value="0">{{ trans('none') }}</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->unit_id }}">
                                {{ $unit->unit_name }}/{{ $unit->unit_name_plrl }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td class="td-amount td-vert-middle">
                <span>{{ trans('subtotal') }}</span><br/>
                <span name="subtotal" class="amount"></span>
            </td>
@if(!$legacy_calculation)
            @include('core::partial.itemlist_table_item_discount_show')
@endif
            <td class="td-amount td-vert-middle">
                <span>{{ trans('tax') }}</span><br/>
                <span name="item_tax_total" class="amount"></span>
            </td>
@if($legacy_calculation)
            @include('core::partial.itemlist_table_item_discount_show')
@endif
            <td class="td-amount td-vert-middle">
                <span>{{ trans('total') }}</span><br/>
                <span name="item_total" class="amount"></span>
            </td>
        </tr>
        </tbody>

@foreach($items as $item)
        <tbody class="item">
        <tr>
            <td rowspan="2" class="td-icon">
                <i class="fa fa-arrows cursor-move"></i>
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
                <br/>
                <i title="{{ trans('recurring') }}"
                   class="js-item-recurrence-toggler cursor-pointer fa {{ $item_recurrence_class }}"></i>
                <input type="hidden" name="item_is_recurring" value="{{ $item_recurrence_state }}"/>
@endif
            </td>
            <td class="td-text">
                <input type="hidden" name="invoice_id" value="{{ $invoice_id }}">
                <input type="hidden" name="item_id" value="{{ $item->item_id }}"{{ $invoice_disabled }}>
                <input type="hidden" name="item_task_id" class="item-task-id"
                       value="{{ $item->item_task_id ? $item->item_task_id : '' }}">
                <input type="hidden" name="item_product_id" value="{{ $item->item_product_id }}">

                <div class="input-group">
                    <span class="input-group-addon">{{ trans('item') }}</span>
                    <input type="text" name="item_name" class="form-control"
                           value="{{ htmlspecialchars($item->item_name) }}"{{ $invoice_disabled }}>
                </div>
            </td>
            <td class="td-amount td-quantity">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('quantity') }}</span>
                    <input type="text" name="item_quantity" class="form-control amount"
                           value="{{ format_quantity($item->item_quantity) }}"{{ $invoice_disabled }}>
                </div>
            </td>
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('price') }}</span>
                    <input type="text" name="item_price" class="form-control amount"
                           value="{{ format_amount($item->item_price) }}"{{ $invoice_disabled }}>
                    <div class="input-group-addon">{{ get_setting('currency_symbol') }}</div>
                </div>
            </td>
@if(!$legacy_calculation)
            @include('core::partial.itemlist_table_item_discount_input', ['item' => $item])
@endif
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('tax_rate') }}</span>
                    <select name="item_tax_rate_id" class="form-control"{{ $invoice_disabled }}>
                        <option value="0">{{ trans('none') }}</option>
@foreach($tax_rates as $tax_rate)
                        <option value="{{ $tax_rate->tax_rate_id }}"
                            {{ $item->item_tax_rate_id == $tax_rate->tax_rate_id ? 'selected' : '' }}>
                            {{ format_amount($tax_rate->tax_rate_percent) }}% - {{ $tax_rate->tax_rate_name }}
                        </option>
@endforeach
                    </select>
                </div>
            </td>
@if($legacy_calculation)
            @include('core::partial.itemlist_table_item_discount_input', ['item' => $item])
@endif
                <td class="td-icon text-right td-vert-middle">
@if($invoice->is_read_only != 1)
                    <button type="button" class="btn_delete_item btn btn-link btn-sm" title="{{ trans('delete') }}"
                            data-item-id="{{ $item->item_id }}">
                        <i class="fa fa-trash-o text-danger"></i>
                    </button>
@endif
                </td>
            </tr>

            <tr>
@if($invoice->sumex_id == '')
                    <td class="td-textarea">
                        <div class="input-group">
                            <span class="input-group-addon">{{ trans('description') }}</span>
                            <textarea name="item_description" class="form-control"{{ $invoice_disabled }}
                            >{{ htmlspecialchars($item->item_description) }}</textarea>
                        </div>
                    </td>
@else
                    <td class="td-date">
                        <div class="input-group">
                            <span class="input-group-addon">{{ trans('date') }}</span>
                            <input type="text" name="item_date" class="form-control datepicker"
                                   value="{{ format_date($item->item_date) }}"{{ $invoice_disabled }}>
                        </div>
                    </td>
@endif

                <td class="td-amount">
                    <div class="input-group">
                        <span class="input-group-addon">{{ trans('product_unit') }}</span>
                        <select name="item_product_unit_id" class="form-control">
                            <option value="0">{{ trans('none') }}</option>
@foreach($units as $unit)
                            <option value="{{ $unit->unit_id }}"
                                {{ $item->item_product_unit_id == $unit->unit_id ? 'selected' : '' }}>
                                {{ htmlspecialchars($unit->unit_name) }}/{{ htmlspecialchars($unit->unit_name_plrl) }}
                            </option>
@endforeach
                        </select>
                    </div>
                </td>
                <td class="td-amount td-vert-middle">
                    <span>{{ trans('subtotal') }}</span><br/>
                    <span name="subtotal" class="amount">
                        {{ format_currency($item->item_subtotal) }}
                    </span>
                </td>
@if(!$legacy_calculation)
            @include('core::partial.itemlist_table_item_discount_show', ['item' => $item])
@endif
                <td class="td-amount td-vert-middle">
                    <span>{{ trans('tax') }}</span><br/>
                    <span name="item_tax_total" class="amount">
                        {{ format_currency($item->item_tax_total) }}
                    </span>
                </td>
@if($legacy_calculation)
            @include('core::partial.itemlist_table_item_discount_show', ['item' => $item])
@endif
                <td class="td-amount td-vert-middle">
                    <span>{{ trans('total') }}</span><br/>
                    <span name="item_total" class="amount">
                        {{ format_currency($item->item_total) }}
                    </span>
                </td>
            </tr>
            </tbody>
@endforeach

    </table>
</div>

<br>

<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
    <div class="md:col-span-4">
        <div class="btn-group">
            @if($invoice->is_read_only != 1)
                <a href="javascript:void(0);" class="btn_add_row btn btn-sm btn-default">
                    <i class="fa fa-plus"></i> {{ trans('add_new_row') }}
                </a>
                <a href="javascript:void(0);" class="btn_add_product btn btn-sm btn-default">
                    <i class="fa fa-database"></i>
                    {{ trans('add_product') }}
                </a>
                <a href="javascript:void(0);" class="btn_add_task btn btn-sm btn-default{{ get_setting('projects_enabled') == 1 ? '' : ' hidden' }}">
                    <i class="fa fa-database"></i> {{ trans('add_task') }}
                </a>
            @endif
        </div>
    </div>

    <div class="block md:hidden"><br></div>

    <div class="md:col-span-6 md:col-start-7 lg:col-span-4 lg:col-start-9">
        <table class="table table-bordered text-right">
@if(!$legacy_calculation)
            @include('invoices::partial_itemlist_table_invoice_discount')
@endif
            <tr>
                <td class="w-2/5">{{ trans('subtotal') }}</td>
                <td class="w-3/5 amount">{{ format_currency($invoice->invoice_item_subtotal) }}</td>
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
                        <button type="submit" class="btn btn-xs btn-link" onclick="var Y=confirm('{{ trans('delete_tax_warning') }}');if(Y)show_loader();return Y;">
                            <i class="fa fa-trash-o"></i>
                        </button>
                        <span class="text-muted">
                            {{ htmlspecialchars($invoice_tax_rate->invoice_tax_rate_name) }} {{ format_amount($invoice_tax_rate->invoice_tax_rate_percent) }}%
                        </span>
                        <span class="amount">
                            {{ format_currency($invoice_tax_rate->invoice_tax_rate_amount) }}
                        </span>
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
