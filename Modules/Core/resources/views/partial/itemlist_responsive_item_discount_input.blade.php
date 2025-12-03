@php
$invoice_disabled = isset($invoice) && $invoice->is_read_only == 1 ? ' disabled' : '';
$item_id = $item->item_id ?? '';
$item_value = isset($item->item_discount_amount) ? number_format($item->item_discount_amount, 2) : '';
$currency_symbol = get_setting('currency_symbol'); // You can also move this to a Transformer or Controller
@endphp

<div class="input-group">
    <label for="item_discount_amount_{{ $item_id }}" class="input-group-addon ig-addon-aligned">
        {{ trans('discount') }}
    </label>
    <input type="text" name="item_discount_amount" id="item_discount_amount_{{ $item_id }}" class="fi-input"
           value="{{ $item_value }}" {!! $invoice_disabled !!}
           data-toggle="tooltip" data-placement="bottom" title="{{ trans('item_discount') }}">
    <div class="input-group-addon">
        {{ $currency_symbol }} {{ trans('per_item') }}
    </div>
</div>
