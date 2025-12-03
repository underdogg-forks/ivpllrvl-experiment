@extends('core::layouts.app')

@section('content')
<form method="post">

    @csrf

    <div id="headerbar">
        <h1 class="headerbar-title">{{ trans('products_form') }}</h1>
        @include('core::header_buttons')
    </div>

    <div id="content">

        @include('core::alerts')

        <div class="row">
            <div class="col-xs-12 col-md-6">

                <div class="fi-section">
                    <div class="fi-section-header">

                        @if (isset($product->product_id) && $product->product_id)
                            #{{ $product->product_id }}&nbsp;
                            {{ $product->product_name }}
                        @else
                            {{ trans('new_product') }}
                        @endif

                    </div>
                    <div class="fi-section-body">

                        <div class="fi-field-wrp">
                            <label for="family_id">
                                {{ trans('family') }}
                            </label>

                            <select name="family_id" id="family_id" class="fi-input simple-select">
                                <option value="0">{{ trans('select_family') }}</option>
                                @foreach ($families as $family)
                                    <option value="{{ $family->family_id }}"
                                        {{ (isset($product->family_id) && $product->family_id == $family->family_id) ? 'selected' : '' }}>
                                        {{ $family->family_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="product_sku">
                                {{ trans('product_sku') }}
                            </label>

                            <input type="text" name="product_sku" id="product_sku" class="fi-input"
                                   value="{{ old('product_sku', $product->product_sku ?? '') }}">
                        </div>

                        <div class="fi-field-wrp">
                            <label for="product_name">
                                {{ trans('product_name') }}
                            </label>

                            <input type="text" name="product_name" id="product_name" class="fi-input" required
                                   value="{{ old('product_name', $product->product_name ?? '') }}">
                        </div>

                        <div class="fi-field-wrp">
                            <label for="product_description">
                                {{ trans('product_description') }}
                            </label>

                            <textarea name="product_description" id="product_description" class="fi-input"
                                      rows="3">{{ old('product_description', $product->product_description ?? '') }}</textarea>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="product_price">
                                {{ trans('product_price') }}
                            </label>

                            <div class="input-group has-feedback">
                                <input type="text" name="product_price" id="product_price" class="fi-input"
                                       value="{{ old('product_price', format_amount($product->product_price ?? 0)) }}" required>
                                <span class="input-group-addon">{{ get_setting('currency_symbol') }}</span>
                            </div>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="unit_id">
                                {{ trans('product_unit') }}
                            </label>

                            <select name="unit_id" id="unit_id" class="fi-input simple-select">
                                <option value="0">{{ trans('select_unit') }}</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->unit_id }}"
                                        {{ (isset($product->unit_id) && $product->unit_id == $unit->unit_id) ? 'selected' : '' }}>
                                        {{ $unit->unit_name }}/{{ $unit->unit_name_plrl }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="tax_rate_id">
                                {{ trans('tax_rate') }}
                            </label>

                            <select name="tax_rate_id" id="tax_rate_id" class="fi-input simple-select">
                                <option value="0">{{ trans('none') }}</option>
                                @foreach ($tax_rates as $tax_rate)
                                    <option value="{{ $tax_rate->tax_rate_id }}"
                                        {{ (isset($product->tax_rate_id) && $product->tax_rate_id == $tax_rate->tax_rate_id) ? 'selected' : '' }}>
                                        {{ $tax_rate->tax_rate_name }} ({{ format_amount($tax_rate->tax_rate_percent) }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-6">

                <div class="fi-section">
                    <div class="fi-section-header">
                        {{ trans('extra_information') }}
                    </div>
                    <div class="fi-section-body">

                        <div class="fi-field-wrp">
                            <label for="provider_name">
                                {{ trans('provider_name') }}
                            </label>

                            <input type="text" name="provider_name" id="provider_name" class="fi-input"
                                   value="{{ old('provider_name', $product->provider_name ?? '') }}">
                        </div>

                        <div class="fi-field-wrp">
                            <label for="purchase_price">
                                {{ trans('purchase_price') }}
                            </label>

                            <div class="input-group has-feedback">
                                <input type="text" name="purchase_price" id="purchase_price" class="fi-input"
                                       value="{{ old('purchase_price', format_amount($product->purchase_price ?? 0)) }}">
                                <span class="input-group-addon">{{ get_setting('currency_symbol') }}</span>
                            </div>
                        </div>

                    </div>
                </div>

                @if (get_setting('sumex') == '1')
                    <div class="fi-section">
                        <div class="fi-section-header">
                            {{ trans('invoice_sumex') }}
                        </div>
                        <div class="fi-section-body">

                            <div class="fi-field-wrp">
                                <label for="product_tariff">
                                    {{ trans('product_tariff') }}
                                </label>

                                <input type="text" name="product_tariff" id="product_tariff" class="fi-input"
                                       value="{{ old('product_tariff', $product->product_tariff ?? '') }}">
                            </div>

                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

</form>
@endsection
