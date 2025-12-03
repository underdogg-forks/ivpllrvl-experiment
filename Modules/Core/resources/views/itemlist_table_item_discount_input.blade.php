@php
// Called in [quotes|invoices]/partial_itemlist_table.php (item & new) line
$invoice_disabled = isset($invoice) && $invoice->is_read_only == 1 ? ' disabled="disabled"' : '';
$item_value       = isset($item->item_discount_amount) ? format_amount($item->item_discount_amount) : '';
@endphp
            <td class="td-amount">
                <div class="input-group flex">
                    <span class="input-group-addon px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md">{{ trans('discount') }}</span>
                    <input type="text" name="item_discount_amount" class="fi-input amount flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                           value="{{ $item_value }}"{{ $invoice_disabled }}
                           data-toggle="tooltip" data-placement="bottom" title="{{ trans('item_discount') }}">
                    <span class="input-group-addon px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-r-md">{{ get_setting('currency_symbol') }} {{ trans('per_item') }}</span>
                </div>
            </td>
