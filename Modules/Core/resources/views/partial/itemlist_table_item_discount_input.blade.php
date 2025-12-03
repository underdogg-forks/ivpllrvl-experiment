<?php
// Called in [quotes|invoices]/partial_itemlist_table.php (item & new) line
$invoice_disabled = isset($invoice) && $invoice->is_read_only == 1 ? ' disabled="disabled"' : '';
$item_value       = isset($item->item_discount_amount) ? format_amount($item->item_discount_amount) : '';
?>
            <td class="td-amount">
                <div class="input-group">
                    <span class="input-group-addon">{{ trans('discount') }}</span>
                    <input type="text" name="item_discount_amount" class="fi-input amount"
                           value="{{ $item_value }}"{{ $invoice_disabled }}
                           data-toggle="tooltip" data-placement="bottom" title="{{ trans('item_discount') }}">
                    <span class="input-group-addon">{{ get_setting('currency_symbol') . ' ' . trans('per_item') }}</span>
                </div>
            </td>
