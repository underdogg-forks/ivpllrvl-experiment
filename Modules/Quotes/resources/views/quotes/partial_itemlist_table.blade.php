
<div class="table-responsive">
    <table id="item_table" class="items table table-condensed table-bordered no-margin">

        <thead style="display:none">
        <tr>
            <th></th>
            <th>{{ trans('item') }}</th>
<!--
            <th>{{ trans('description') }}</th>
-->
            <th class="amount">{{ trans('quantity') }}</th>
            <th class="amount">{{ trans('price') }}</th>
            @if (!$legacy_calculation)
                <th class="amount">{{ trans('item_discount') }}</th>
            @endif
            <th class="amount">{{ trans('tax_rate') }}</th>
            @if ($legacy_calculation)
                <th class="amount">{{ trans('item_discount') }}</th>
            @endif
<!--
            <th class="amount">{{ trans('subtotal') }}</th>
            <th class="amount">{{ trans('tax') }}</th>
-->
            <th class="amount">{{ trans('total') }}</th>
            <th></th>
        </tr>
        </thead>

        <tbody id="new_row" style="display:none">
        <tr>
            <td rowspan="2" class="td-icon"><i class="fa fa-arrows cursor-move"></i></td>
            <td class="td-text">
                <input type="hidden" name="quote_id" value="{{ $quote_id }}">
                <input type="hidden" name="item_id" value="">
                <input type="hidden" name="item_product_id" value="">

                <div class="input-group">
                    <span class="input-group-addon">{{ trans('item') }}</span>
                    <input type="text" name="item_name" class="fi-input" value="">
                </div>
            </td>
            <td class="td-amount td-quantity">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('quantity') }}</span>
                    <input type="text" name="item_quantity" class="fi-input amount" value="">
                </div>
            </td>
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('price') }}</span>
                    <input type="text" name="item_price" class="fi-input amount" value="">
                    <div class="input-group-addon">{{ get_setting('currency_symbol') }}</div>
                </div>
            </td>
            @if (!$legacy_calculation)
                @include('core::partial.itemlist_table_item_discount_input')
            @endif
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('tax_rate') }}</span>
                    <select name="item_tax_rate_id" class="fi-input">
                        <option value="0">{{ trans('none') }}</option>
                        @foreach ($tax_rates as $tax_rate)
                            <option value="{{ $tax_rate->tax_rate_id }}">
                                {{ format_amount($tax_rate->tax_rate_percent) }}% - {{ $tax_rate->tax_rate_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </td>
            @if ($legacy_calculation)
                @include('core::partial.itemlist_table_item_discount_input')
            @endif
            <td class="td-icon text-right td-vert-middle">
                <button type="button" class="btn_delete_item fi-link fi-size-sm" title="{{ trans('delete') }}">
                    <i class="fa fa-trash-o text-danger"></i>
                </button>
            </td>
        </tr>
        <tr>
            <td class="td-textarea">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('description') }}</span>
                    <textarea name="item_description" class="fi-input"></textarea>
                </div>
            </td>
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('product_unit') }}</span>
                    <select name="item_product_unit_id"
                            class="fi-input">
                        <option value="0">{{ trans('none') }}</option>
                        @foreach ($units as $unit)
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
            @if (!$legacy_calculation)
                @include('core::partial.itemlist_table_item_discount_show')
            @endif
            <td class="td-amount td-vert-middle">
                <span>{{ trans('tax') }}</span><br/>
                <span name="item_tax_total" class="amount"></span>
            </td>
            @if ($legacy_calculation)
                @include('core::partial.itemlist_table_item_discount_show')
            @endif
            <td class="td-amount td-vert-middle">
                <span>{{ trans('total') }}</span><br/>
                <span name="item_total" class="amount"></span>
            </td>
        </tr>
        </tbody>

        @foreach ($items as $item)
            <tbody class="item">
                <tr>
                    <td rowspan="2" class="td-icon"><i class="fa fa-arrows cursor-move"></i></td>
                    <td class="td-text">
                        <input type="hidden" name="quote_id" value="{{ $quote_id }}">
                        <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                        <input type="hidden" name="item_product_id" value="{{ $item->item_product_id }}">

                        <div class="input-group">
                            <span class="input-group-addon">{{ trans('item') }}</span>
                            <input type="text" name="item_name" class="fi-input"
                                   value="{{ $item->item_name }}">
                        </div>
                    </td>
                    <td class="td-amount td-quantity">
                        <div class="input-group">
                            <span class="input-group-addon">{{ trans('quantity') }}</span>
                            <input type="text" name="item_quantity" class="fi-input amount"
                                   value="{{ format_quantity($item->item_quantity) }}">
                        </div>
                    </td>
                    <td class="td-amount">
                        <div class="input-group">
                            <span class="input-group-addon">{{ trans('price') }}</span>
                            <input type="text" name="item_price" class="fi-input amount"
                                   value="{{ format_amount($item->item_price) }}">
                            <div class="input-group-addon">{{ get_setting('currency_symbol') }}</div>
                        </div>
                    </td>
                    @if (!$legacy_calculation)
                        @include('core::partial.itemlist_table_item_discount_input', ['item' => $item])
                    @endif
                    <td class="td-amount">
                        <div class="input-group">
                            <span class="input-group-addon">{{ trans('tax_rate') }}</span>
                            <select name="item_tax_rate_id" class="fi-input">
                                <option value="0">{{ trans('none') }}</option>
                                @foreach ($tax_rates as $tax_rate)
                                    <option value="{{ $tax_rate->tax_rate_id }}"
                                        {{ $item->item_tax_rate_id == $tax_rate->tax_rate_id ? 'selected="selected"' : '' }}>
                                        {{ format_amount($tax_rate->tax_rate_percent) }}% - {{ $tax_rate->tax_rate_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    @if ($legacy_calculation)
                        @include('core::partial.itemlist_table_item_discount_input', ['item' => $item])
                    @endif
                    <td class="td-icon text-right td-vert-middle">
                        <button type="button" class="btn_delete_item fi-link fi-size-sm" title="{{ trans('delete') }}"
                                data-item-id="{{ $item->item_id }}">
                            <i class="fa fa-trash-o text-danger"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td class="td-textarea">
                        <div class="input-group">
                            <span class="input-group-addon">{{ trans('description') }}</span>
                            <textarea name="item_description" class="fi-input">{{ $item->item_description }}</textarea>
                        </div>
                    </td>
                    <td class="td-amount">
                        <div class="input-group">
                            <span class="input-group-addon">{{ trans('product_unit') }}</span>
                            <select name="item_product_unit_id"
                                    class="fi-input">
                                <option value="0">{{ trans('none') }}</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->unit_id }}"
                                        {{ check_select($item->item_product_unit_id, $unit->unit_id) }}>
                                        {{ $unit->unit_name }}/{{ $unit->unit_name_plrl }}
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
                    @if (!$legacy_calculation)
                        @include('core::partial.itemlist_table_item_discount_show', ['item' => $item])
                    @endif
                    <td class="td-amount td-vert-middle">
                        <span>{{ trans('tax') }}</span><br/>
                        <span name="item_tax_total" class="amount">
                            {{ format_currency($item->item_tax_total) }}
                        </span>
                    </td>
                    @if ($legacy_calculation)
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

<div class="row">
    <div class="col-xs-12 col-md-4">
        <div class="btn-group">
            <a href="javascript:void(0);" class="btn_add_row btn fi-size-sm fi-btn-secondary">
                <i class="fa fa-plus"></i>
                {{ trans('add_new_row') }}
            </a>
            <a href="javascript:void(0);" class="btn_add_product btn fi-size-sm fi-btn-secondary">
                <i class="fa fa-database"></i>
                {{ trans('add_product') }}
            </a>
        </div>
    </div>

    <div class="col-xs-12 visible-xs visible-sm"><br></div>

    <div class="col-xs-12 col-md-6 col-md-offset-2 col-lg-4 col-lg-offset-4">
        <table class="table table-bordered text-right">
            @if (!$legacy_calculation)
                @include('quotes::quotes.partial_itemlist_table_quote_discount')
            @endif
            <tr>
                <td style="width: 40%;">{{ trans('subtotal') }}</td>
                <td style="width: 60%;" class="amount">{{ format_currency($quote->quote_item_subtotal) }}</td>
            </tr>
            <tr>
                <td>{{ trans('item_tax') }}</td>
                <td class="amount">{{ format_currency($quote->quote_item_tax_total) }}</td>
            </tr>
            @if ($legacy_calculation)
                <tr>
                    <td>{{ trans('quote_tax') }}</td>
                    <td>
                        @if ($quote_tax_rates)
                            @foreach ($quote_tax_rates as $quote_tax_rate)
                                <form method="POST" class="form-inline"
                                      action="{{ route('quotes.delete_tax', [$quote->quote_id, $quote_tax_rate->quote_tax_rate_id]) }}">
                                    @csrf
                                    <button type="submit" class="btn fi-size-xs fi-btn-link" onclick="var Y=confirm('{{ trans('delete_tax_warning') }}');if(Y)show_loader();return Y;">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                    <span class="text-muted">
                                        {{ $quote_tax_rate->quote_tax_rate_name }} {{ format_amount($quote_tax_rate->quote_tax_rate_percent) }}%
                                    </span>
                                    <span class="amount">
                                        {{ format_currency($quote_tax_rate->quote_tax_rate_amount) }}
                                    </span>
                                </form>
                            @endforeach
                        @else
                            {{ format_currency('0') }}
                        @endif
                    </td>
                </tr>
                @include('quotes::quotes.partial_itemlist_table_quote_discount')
            @endif
            <tr>
                <td><b>{{ trans('total') }}</b></td>
                <td class="amount"><b>{{ format_currency($quote->quote_total) }}</b></td>
            </tr>
        </table>
    </div>

</div>
