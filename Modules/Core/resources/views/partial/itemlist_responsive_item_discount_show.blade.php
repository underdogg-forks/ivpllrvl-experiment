@php
// Called in [quotes|invoices]/partial_itemlist_responsive.php (item & new) line
$item_value = isset($item) ? format_currency($item->item_discount) : '';
$item_global_discount = $item_value ? $item->item_subtotal - ($item->item_total - $item->item_tax_total + $item->item_discount) : 0;
@endphp
<hr class="no-margin">
                                <div class="grid grid-cols-12 gap-2 mb-1">
                                    <div class="col-span-9 sm:col-span-8">{{ trans('item_discount') }}:</div>
                                    <div class="col-span-3 sm:col-span-4">{{ $item_value }}</div>
                                </div>
@if(!$legacy_calculation && $item_global_discount)
                                <div class="grid grid-cols-12 gap-2 mb-1">
                                    <div class="col-span-9 sm:col-span-8">{{ trans('global_discount') }}:</div>
                                    <div class="col-span-3 sm:col-span-4">{{ format_currency($item_global_discount) }}</div>
                                </div>
                                <div class="grid grid-cols-12 gap-2 mb-1">
                                    <div class="col-span-9 sm:col-span-8">{{ trans('discount') }} ({{ trans('subtotal') }}):</div>
                                    <div class="col-span-3 sm:col-span-4">{{ format_currency($item_global_discount + $item->item_discount) }}</div>
                                </div>
@endif
<hr class="no-margin">
