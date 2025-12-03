@php
// Called in [quotes|invoices]/partial_itemlist_responsive.php (item & new) line
$invoice_disabled = isset($invoice) && $invoice->is_read_only == 1 ? ' disabled="disabled"' : '';
$item_id          = $item->item_id ?? '';
$item_value       = isset($item->item_discount_amount) ? format_amount($item->item_discount_amount) : '';
@endphp
                                <div class="input-group flex">
                                    <label for="item_discount_amount_{{ $item_id }}" class="input-group-addon ig-addon-aligned px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('discount') }}</label>
                                    <input type="text" name="item_discount_amount" id="item_discount_amount_{{ $item_id }}" class="fi-input flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                           value="{{ $item_value }}"{{ $invoice_disabled }}
                                           data-toggle="tooltip" data-placement="bottom" title="{{ trans('item_discount') }}">
                                    <div class="input-group-addon px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-r-md">{{ get_setting('currency_symbol') }} {{ trans('per_item') }}</div>
                                </div>
