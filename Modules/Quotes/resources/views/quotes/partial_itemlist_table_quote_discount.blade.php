@php
$discount_amount = $quote->quote_discount_amount != 0 ? number_format($quote->quote_discount_amount, 2) : '';
$discount_percent = $quote->quote_discount_percent != 0 ? number_format($quote->quote_discount_percent, 2) : '';
$currency = get_setting('currency_symbol');
@endphp

<tr>
    <td class="td-vert-middle">{{ trans('global_discount') }}</td>
    <td class="clearfix">
        <div class="discount-field">
            <div class="input-group input-group-sm">
                <input id="quote_discount_amount" name="quote_discount_amount"
                       class="discount-option form-control amount"
                       aria-label="{{ trans('global_discount') }}"
                       value="{{ $discount_amount }}">
                <span class="input-group-addon">{{ $currency }}</span>
            </div>
        </div>

        <div class="discount-field">
            <div class="input-group input-group-sm">
                <input id="quote_discount_percent" name="quote_discount_percent"
                       aria-label="{{ trans('global_discount') }} %"
                       value="{{ $discount_percent }}"
                       class="discount-option form-control amount">
                <span class="input-group-addon">%</span>
            </div>
        </div>
    </td>
</tr>
