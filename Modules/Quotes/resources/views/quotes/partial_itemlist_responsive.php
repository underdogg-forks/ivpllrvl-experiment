<div class="row">
    <div id="item_table" class="items table col-xs-12">
        <div id="new_row" class="form-group details-box" style="display: none;">
            <div class="row">
                <div class="col-xs-12 col-sm-7 col-md-6 col-lg-5">
                    <div class="row">
                        <div class="col-xs-12 col-sm-1">
                            <button type="button" class="btn btn-link up" title="{{ _trans('move_up') }}">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-link down" title="{{ _trans('move_down') }}">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                            <button type="button" class="btn_delete_item btn btn-link btn-sm" title="{{ _trans('delete') }}">
                                <i class="fa fa-trash-o text-danger"></i>
                            </button>
                        </div>
                        <div class="col-xs-12 col-sm-11">
                            <div class="input-group">
                                <label for="item_name" class="input-group-addon ig-addon-aligned">{{ _trans('item') }}</label>
                                <input type="text" name="item_name" id="item_name" class="form-control" value="">
                            </div>
                            <div class="input-group">
                                <label for="item_description" class="input-group-addon ig-addon-aligned">{{ _trans('description') }}</label>
                                <textarea name="item_description" id="item_description" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-5 col-md-6 col-lg-7">
                    <div class="row">
                        <div class="col-xs-12 col-lg-6">
                            <div class="input-group">
                                <label for="item_quantity" class="input-group-addon ig-addon-aligned">{{ _trans('quantity') }}</label>
                                <input type="text" name="item_quantity" id="item_quantity" class="form-control" value="">
                            </div>
                            <div class="input-group">
                                <label for="item_product_unit_id" class="input-group-addon ig-addon-aligned">{{ _trans('product_unit') }}</label>
                                <select name="item_product_unit_id" id="item_product_unit_id" class="form-control">
                                    <option value="0">{{ _trans('none') }}</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}/{{ $unit->unit_name_plrl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group">
                                <label for="item_price" class="input-group-addon ig-addon-aligned">{{ _trans('price') }}</label>
                                <input type="text" name="item_price" id="item_price" class="form-control" value="">
                                <div class="input-group-addon">{{ get_setting('currency_symbol') }}</div>
                            </div>
                            @includeWhen(! $legacy_calculation, 'layout.partial.itemlist_responsive_item_discount_input')
                            <div class="input-group">
                                <label for="item_tax_rate_id" class="input-group-addon ig-addon-aligned">{{ _trans('tax_rate') }}</label>
                                <select name="item_tax_rate_id" id="item_tax_rate_id" class="form-control">
                                    <option value="0">{{ _trans('none') }}</option>
                                    @foreach($tax_rates as $tax_rate)
                                    <option value="{{ $tax_rate->tax_rate_id }}" {{ check_select(get_setting('default_item_tax_rate'), $tax_rate->tax_rate_id) }}>
                                    {{ format_amount($tax_rate->tax_rate_percent) }}% - {{ $tax_rate->tax_rate_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @includeWhen($legacy_calculation, 'layout.partial.itemlist_responsive_item_discount_input')
                        </div>

                        <input type="hidden" name="quote_id" value="{{ $quote_id }}">
                        <input type="hidden" name="item_id" value="">
                        <input type="hidden" name="item_product_id" value="">

                        <div class="col-xs-12 col-md-6 text-right">
                            <div class="row mb-1">
                                <div class="col-xs-9 col-sm-8">{{ _trans('subtotal') }}:</div>
                                <div class="col-xs-3 col-sm-4"><span name="subtotal"></span></div>
                            </div>
                            @includeWhen(! $legacy_calculation, 'layout.partial.itemlist_responsive_item_discount_show')
                            <div class="row mb-1">
                                <div class="col-xs-9 col-sm-8">{{ _trans('tax') }}:</div>
                                <div class="col-xs-3 col-sm-4"><span name="item_tax_total"></span></div>
                            </div>
                            @includeWhen($legacy_calculation, 'layout.partial.itemlist_responsive_item_discount_show')
                            <div class="row mb-1">
                                <strong>
                                    <div class="col-xs-9 col-sm-8">{{ _trans('total') }}:</div>
                                    <div class="col-xs-3 col-sm-4"><span name="item_total"></span></div>
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
                            <button type="button" class="btn btn-link up" title="{{ _trans('move_up') }}">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-link down" title="{{ _trans('move_down') }}">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                            <button type="button" class="btn_delete_item btn btn-link" title="{{ _trans('delete') }}" data-item-id="{{ $item->item_id }}">
                                <i class="fa fa-trash-o text-danger"></i>
                            </button>
                        </div>

                        <div class="col-xs-12 col-sm-11">
                            <input type="hidden" name="quote_id" value="{{ $quote_id }}">
                            <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                            <input type="hidden" name="item_product_id" value="{{ $item->item_product_id }}">
                            <div class="input-group">
                                <label for="item_name_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned">{{ _trans('item') }}</label>
                                <input type="text" name="item_name" id="item_name_{{ $item->item_id }}" class="form-control" value="{{ _htmlsc($item->item_name) }}">
                            </div>
                            <div class="input-group">
                                <label for="item_description_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned">{{ _trans('description') }}</label>
                                <textarea name="item_description" id="item_description_{{ $item->item_id }}" class="form-control">{{ htmlsc($item->item_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-5 col-md-6 col-lg-7">
                    <div class="row">
                        <div class="col-xs-12 col-lg-6">
                            <div class="input-group">
                                <label for="item_quantity_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned">{{ _trans('quantity') }}</label>
                                <input type="text" name="item_quantity" id="item_quantity_{{ $item->item_id }}" class="form-control" value="{{ format_quantity($item->item_quantity) }}">
                            </div>
                            <div class="input-group">
                                <label for="item_product_unit_id_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned">{{ _trans('product_unit') }}</label>
                                <select name="item_product_unit_id" id="item_product_unit_id_{{ $item->item_id }}" class="form-control">
                                    <option value="0">{{ _trans('none') }}</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->unit_id }}" {{ check_select($item->item_product_unit_id, $unit->unit_id) }}>
                                        {{ htmlsc($unit->unit_name) }}/{{ htmlsc($unit->unit_name_plrl) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group">
                                <label for="item_price_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned">{{ _trans('price') }}</label>
                                <input type="text" name="item_price" id="item_price_{{ $item->item_id }}" class="form-control" value="{{ format_amount($item->item_price) }}">
                                <div class="input-group-addon">{{ get_setting('currency_symbol') }}</div>
                            </div>
                            @includeWhen(! $legacy_calculation, 'layout.partial.itemlist_responsive_item_discount_input', ['item' => $item])
                            <div class="input-group">
                                <label for="item_tax_rate_id_{{ $item->item_id }}" class="input-group-addon ig-addon-aligned">{{ _trans('tax_rate') }}</label>
                                <select name="item_tax_rate_id" id="item_tax_rate_id_{{ $item->item_id }}" class="form-control">
                                    <option value="0">{{ _trans('none') }}</option>
                                    @foreach($tax_rates as $tax_rate)
                                    <option value="{{ $tax_rate->tax_rate_id }}" {{ check_select($item->item_tax_rate_id, $tax_rate->tax_rate_id) }}>
                                        {{ format_amount($tax_rate->tax_rate_percent) }}% - {{ $tax_rate->tax_rate_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @includeWhen($legacy_calculation, 'layout.partial.itemlist_responsive_item_discount_input', ['item' => $item])
                        </div>

                        <div class="col-xs-12 col-md-6 text-right">
                            <div class="row mb-1">
                                <div class="col-xs-9 col-sm-8">{{ _trans('subtotal') }}:</div>
                                <div class="col-xs-3 col-sm-4">{{ format_currency($item->item_subtotal) }}</div>
                            </div>
                            @includeWhen(! $legacy_calculation, 'layout.partial.itemlist_responsive_item_discount_show', ['item' => $item])
                            <div class="row mb-1">
                                <div class="col-xs-9 col-sm-8">{{ _trans('tax') }}:</div>
                                <div class="col-xs-3 col-sm-4">{{ format_currency($item->item_tax_total) }}</div>
                            </div>
                            @includeWhen($legacy_calculation, 'layout.partial.itemlist_responsive_item_discount_show', ['item' => $item])
                            <div class="row mb-1">
                                <div class="col-xs-9 col-sm-8"><b>{{ _trans('total') }}:</b></div>
                                <div class="col-xs-3 col-sm-4"><b>{{ format_currency($item->item_total) }}</b></div>
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
        <div class="btn-group">
            <a href="javascript:void(0);" class="btn_add_row btn btn-sm btn-default">
                <i class="fa fa-plus"></i>{{ _trans('add_new_row') }}
            </a>
            <a href="javascript:void(0);" class="btn_add_product btn btn-sm btn-default">
                <i class="fa fa-database"></i>{{ _trans('add_product') }}
            </a>
        </div>
    </div>

    <div class="col-xs-12 visible-xs visible-sm"><br></div>

    <div class="col-xs-12 col-md-6 col-md-offset-2 col-lg-4 col-lg-offset-4">
        <table class="table table-bordered text-right">
            @includeWhen(! $legacy_calculation, 'quotes.partial_itemlist_table_quote_discount')
            <tr>
                <td style="width: 40%;">{{ _trans('subtotal') }}</td>
                <td style="width: 60%;" class="amount">{{ format_currency($quote->quote_item_subtotal) }}</td>
            </tr>
            <tr>
                <td>{{ _trans('item_tax') }}</td>
                <td class="amount">{{ format_currency($quote->quote_item_tax_total) }}</td>
            </tr>
            @if($legacy_calculation)
            <tr>
                <td>{{ _trans('quote_tax') }}</td>
                <td>
                    @forelse($quote_tax_rates as $quote_tax_rate)
                    <form method="post" action="{{ site_url('quotes/delete_quote_tax/' . $quote->quote_id . '/' . $quote_tax_rate->quote_tax_rate_id) }}">
                        @csrf
                        <span class="amount">{{ format_currency($quote_tax_rate->quote_tax_rate_amount) }}</span>
                        <span class="text-muted">{{ htmlsc($quote_tax_rate->quote_tax_rate_name) }} {{ format_amount($quote_tax_rate->quote_tax_rate_percent) }}</span>
                        <button type="submit" class="btn btn-xs btn-link" onclick="return confirm('{{ _trans('delete_tax_warning') }}') ? show_loader() : false;">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </form>
                    @empty
                    {{ format_currency(0) }}
                    @endforelse
                </td>
            </tr>
            @include('quotes.partial_itemlist_table_quote_discount')
            @endif
            <tr>
                <td><b>{{ _trans('total') }}</b></td>
                <td class="amount"><b>{{ format_currency($quote->quote_total) }}</b></td>
            </tr>
        </table>
    </div>
</div>
