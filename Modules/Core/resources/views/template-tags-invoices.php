<div class="form-group">
    <label for="tags_invoice">{{ trans('invoices') }}</label>
    <select id="tags_invoice" class="tag-select form-control">
        <option value="{{{invoice_number}}}">
            {{ trans('id') }}
        </option>
        <option value="{{{invoice_status}}}">
            {{ trans('status') }}
        </option>
        <optgroup label="{{ trans('invoice_dates') }}">
            <option value="{{{invoice_date_due}}}">
                {{ trans('due_date') }}
            </option>
            <option value="{{{invoice_date_created}}}">
                {{ trans('invoice_date') }}
            </option>
        </optgroup>
        <optgroup label="{{ trans('invoice_amounts') }}">
            <option value="{{{invoice_item_subtotal}}}">
                {{ trans('subtotal') }}
            </option>
            <option value="{{{invoice_item_tax_total}}}">
                {{ trans('invoice_tax') }}
            </option>
            <option value="{{{invoice_total}}}">
                {{ trans('total') }}
            </option>
            <option value="{{{invoice_paid}}}">
                {{ trans('total_paid') }}
            </option>
            <option value="{{{invoice_balance}}}">
                {{ trans('balance') }}
            </option>
        </optgroup>
        <optgroup label="{{ trans('extra_information') }}">
            <option value="{{{invoice_terms}}}">
                {{ trans('invoice_terms') }}
            </option>
        <option value="{{{invoice_guest_url}}}">
            {{ trans('guest_url') }}
        </option>
<!--                 <option value="{{{payment_method}}}"> -->
<!--                     {{ trans('payment_method') }} -->
<!--                 </option> -->
        </optgroup>
<?php
if ($custom_fields['ip_invoice_custom']) {
    ?>
        <optgroup label="{{ trans('custom_fields') }}">
            <?php foreach ($custom_fields['ip_invoice_custom'] as $custom) { ?>
                <option value="{{{<?php echo 'ip_cf_' . $custom->custom_field_id; ?>}}}">
                    <?php echo $custom->custom_field_label . ' (ID ' . $custom->custom_field_id . ')'; ?>
                </option>
            <?php } ?>
        </optgroup>
<?php
}
?>
    </select>
</div>
