@php
// Called in [quotes|invoices]/partial_itemlist_table.php (item & new) line
$item_value = isset($item) ? format_currency($item->item_discount) : '';
$item_global_discount = $item_value ? $item->item_subtotal - ($item->item_total - $item->item_tax_total + $item->item_discount) : 0;
@endphp
                <td class="td-amount td-vert-middle">
                    <span>{{ trans('discount') }}</span><br/>
                    <span data-toggle="tooltip" data-placement="bottom" title="{{ trans('item_discount') }}"
                          class="amount">{{ $item_value }}</span>
@if(!$legacy_calculation && $item_global_discount)
                    + <span data-toggle="tooltip" data-placement="bottom" title="{{ trans('global_discount') }}"
                          class="amount">{{ format_currency($item_global_discount) }}</span>
                    = <span data-toggle="tooltip" data-placement="bottom" title="{{ trans('discount') }} ({{ trans('subtotal') }})"
                          class="amount">{{ format_currency($item_global_discount + $item->item_discount) }}</span>
@endif
                </td>
