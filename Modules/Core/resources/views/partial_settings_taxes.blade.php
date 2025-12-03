<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2">

        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('taxes') }}
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[default_invoice_tax_rate]">
                                {{ trans('default_invoice_tax_rate') }}
                            </label>
                            <select name="settings[default_invoice_tax_rate]" id="settings[default_invoice_tax_rate]"
                                class="form-control simple-select">
                                <option value="">{{ trans('none') }}</option>
                                @foreach ($tax_rates as $tax_rate)
                                    <option value="{{ $tax_rate->tax_rate_id }}"
                                        {{ get_setting('default_invoice_tax_rate') == $tax_rate->tax_rate_id ? 'selected' : '' }}>
                                        {{ $tax_rate->tax_rate_percent }}% - {{ $tax_rate->tax_rate_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="settings[default_item_tax_rate]">
                                {{ trans('default_item_tax_rate') }}
                            </label>
                            <select name="settings[default_item_tax_rate]" id="settings[default_item_tax_rate]"
                                class="form-control simple-select">
                                <option value="">{{ trans('none') }}</option>
                                @foreach ($tax_rates as $tax_rate)
                                    <option value="{{ $tax_rate->tax_rate_id }}"
                                        {{ get_setting('default_item_tax_rate') == $tax_rate->tax_rate_id ? 'selected' : '' }}>
                                        {{ $tax_rate->tax_rate_percent }}% - {{ $tax_rate->tax_rate_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

@php
// LEGACY_CALCULATION false : Taxes Global N, Item Y : Use simple calculation : Apply global discount before item tax
// For e-invoices : 🗸 EN16931, ? PEPPOL3BIS, ? UBL, ? CII ••• (WIP : todo: checks, modify, create models).
@endphp
@if (!$legacy_calculation)
                    <input name="settings[default_include_item_tax]" id="settings[default_include_item_tax]" type="hidden" value="">
@else
{{-- LEGACY_CALCULATION true : Taxes Global Y, Item Y : Use legacy calculation for Discounts & Taxes : By default in ipconfig. --}}
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[default_include_item_tax]">
                                {{ trans('default_invoice_tax_rate_placement') }}
                            </label>
                            <select name="settings[default_include_item_tax]" id="settings[default_include_item_tax]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
                                <option value="0" {{ get_setting('default_include_item_tax') == '0' ? 'selected' : '' }}>
                                    {{ trans('apply_before_item_tax') }}
                                </option>
                                <option value="1" {{ get_setting('default_include_item_tax') == '1' ? 'selected' : '' }}>
                                    {{ trans('apply_after_item_tax') }}
                                </option>
                            </select>
                        </div>
                    </div>
{{-- Fi LEGACY_CALCULATION (Show or not Global Taxes) - since v1.6.3 --}}
@endif
                </div>

            </div>
        </div>

    </div>
</div>
