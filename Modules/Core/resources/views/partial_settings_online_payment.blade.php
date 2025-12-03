<script>
    $(function () {
        var online_payment_select = $('#online-payment-select');
        online_payment_select.select2().on('change', function () {
            var driver = online_payment_select.val();
            $('.gateway-settings:not(.active-gateway)').addClass('hidden');
            $('#gateway-settings-' + driver).removeClass('hidden').addClass('active-gateway');
        });
    });
</script>

<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2">

        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('online_payments') }}
            </div>
            <div class="fi-section-body">

                <div class="fi-field-wrp">
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="settings[enable_online_payments]" value="0">
                            <input type="checkbox" name="settings[enable_online_payments]" value="1"
                                <?php check_select(get_setting('enable_online_payments'), 1, '==', true) ?>>
                            {{ trans('enable_online_payments') }}
                        </label>
                    </div>
                </div>

                <div class="fi-field-wrp">
                    <label for="online-payment-select">
                        {{ trans('add_payment_provider') }}
                    </label>
                    <select id="online-payment-select" class="fi-input">
                        <option value="">{{ trans('none') }}</option>
                        <?php foreach ($gateway_drivers as $driver => $fields) {
                            $d = mb_strtolower($driver);
                            ?>
                            <option value="{{ $d }}">
                                {{ ucwords(str_replace('_', ' ', $driver)) }}
                            </option>
                        @endif
                    </select>
                </div>

            </div>
        </div>

        <?php
        foreach ($gateway_drivers as $driver => $fields) {
            $d = mb_strtolower($driver);
            ?>
            <div id="gateway-settings-{{ $d }}"
                class="gateway-settings fi-section {{ get_setting('gateway_' . $d . '_enabled') ? 'active-gateway' : 'hidden' }}">

                <div class="fi-section-header">
                    {{ ucwords(str_replace('_', ' ', $driver)) }}
                    <div class="pull-right">
                        <div class="checkbox no-margin">
                            <label>
                                <input type="hidden" name="settings[gateway_{{ $d }}_enabled]" value="0">
                                <input type="checkbox" name="settings[gateway_{{ $d }}_enabled]" value="1"
                                    id="settings[gateway_{{ $d }}_enabled]"
                                    <?php check_select(get_setting('gateway_' . $d . '_enabled'), 1, '==', true) ?>>
                                {{ trans('enabled') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="fi-section-body small">

                    @foreach($fields as $key => $setting)
                        @if($setting['type'] == 'checkbox')
                            <div class="checkbox">
                                <label>
                                    <input type="hidden" name="settings[gateway_{{ $d }}_{{ $key ?>]"
                                        value="0">
                                    <input type="checkbox" name="settings[gateway_<?php echo $d }}_{{ $key ?>]"
                                        value="1"
                                        <?php check_select(get_setting('gateway_' . $d . '_' . $key), 1, '==', true) ?>>
                                    <?php _trans('online_payment_' . $key, '', $setting['label']) }}
                                </label>
                            </div>

                        @else
                            <div class="fi-field-wrp">
                                <label for="settings[gateway_{{ $d }}_{{ $key ?>]">
                                    <?php _trans('online_payment_' . $key, '', $setting['label']) }}
                                </label>
                                <input type="{{ $setting['type'] }}" class="fi-input"
                                    name="settings[gateway_{{ $d }}_{{ $key ?>]"
                                    id="settings[gateway_<?php echo $d }}_{{ $key ?>]"
                                    @if($setting['type'] == 'password')
                                        value="<?php echo $this->crypt->decode(get_setting('gateway_' . $d . '_' . $key)) }}"
                                    @else
                                        value="{{ get_setting('gateway_' . $d . '_' . $key) }}"
                                    @endif
                                >
                                @if($setting['type'] == 'password')
                                    <input type="hidden" value="1"
                                        name="settings[gateway_{{ $d . '_' . $key ?>_field_is_password]">
                                @endif
                            </div>

                        @endif
                    @endif
                    <hr>

                    <div class="fi-field-wrp">
                        <label for="settings[gateway_<?php echo $d }}_currency]">
                            {{ trans('currency') }}
                        </label>
                        <select name="settings[gateway_{{ $d }}_currency]"
                            id="settings[gateway_{{ $d }}_currency]"
                            class="fi-input simple-select">
                            @foreach($gateway_currency_codes as $val => $key)
                                <option value="{{ $val }}"
                                    {{ get_setting('gateway_' . $d . '_currency') ?: get_setting('currency_code') == $val ? 'selected' : '' }}>
                                    {{ $val }}
                                </option>
                            @endif
                        </select>
                    </div>

                    <div class="fi-field-wrp">
                        <label for="settings[gateway_{{ $d }}_payment_method]">
                            {{ trans('online_payment_method') }}
                        </label>
                        <select name="settings[gateway_{{ $d }}_payment_method]"
                            id="settings[gateway_{{ $d }}_payment_method]"
                            class="fi-input simple-select">
                            <option value="">{{ trans('none') }}</option>
                            @foreach($payment_methods as $payment_method)
                                <option value="{{ $payment_method->payment_method_id }}"
                                    <?php check_select(get_setting('gateway_' . $d . '_payment_method'), $payment_method->payment_method_id) ?>>
                                    {{ $payment_method->payment_method_name }}
                                </option>
                            @endif
                        </select>
                    </div>

                </div>

            </div>
        @endif
    </div>
</div>
