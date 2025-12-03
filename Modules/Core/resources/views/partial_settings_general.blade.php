<script>
    $(function () {
        $('#btn_generate_cron_key').click(function () {
            $.post("{{ route('settings.get-cron-key') }}", function (data) {
                $('#cron_key').val(data);
            });
        });
    });
</script>

<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2">

        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('general') }}
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[default_language]">
                                {{ trans('language') }}
                            </label>
                            <select name="settings[default_language]" id="settings[default_language]"
                                class="form-control simple-select">
                                @php $sys_lang = get_setting('default_language'); @endphp
                                @foreach ($languages as $language)
                                    <option value="{{ $language }}" {{ $sys_lang == $language ? 'selected' : '' }}>
                                        {{ ucfirst($language) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[system_theme]">
                                {{ trans('theme') }}
                            </label>
                            <select name="settings[system_theme]" id="settings[system_theme]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                @foreach($available_themes as $theme_key => $theme_name)
                                    <option value="{{ $theme_key }}" {{ get_setting('system_theme') == $theme_key ? 'selected' : '' }}>
                                        {{ $theme_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[first_day_of_week]">
                                {{ trans('first_day_of_week') }}
                            </label>
                            <select name="settings[first_day_of_week]" id="settings[first_day_of_week]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                @foreach($first_days_of_weeks as $first_day_of_week_id => $first_day_of_week_name)
                                    <option value="{{ $first_day_of_week_id }}"
                                        {{ get_setting('first_day_of_week') == $first_day_of_week_id ? 'selected' : '' }}>
                                        {{ $first_day_of_week_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[date_format]">
                                {{ trans('date_format') }}
                            </label>
                            <select name="settings[date_format]" id="settings[date_format]"
                                class="form-control simple-select">
                                @foreach($date_formats as $date_format)
                                    <option value="{{ $date_format['setting'] }}"
                                        {{ get_setting('date_format') == $date_format['setting'] ? 'selected' : '' }}>
                                        {{ $current_date->format($date_format['setting']) }}
                                        ({{ $date_format['setting'] ?>)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[default_country]">
                                {{ trans('default_country') }}
                            </label>
                            <select name="settings[default_country]" id="settings[default_country]"
                                class="form-control simple-select">
                                <option value="">{{ trans('none') }}</option>
                                @foreach($countries as $cldr => $country)
                                    <option value="<?php echo $cldr }}" {{ get_setting('default_country') == $cldr ? 'selected' : '' }}>
                                        {{ $country ?>
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="default_list_limit">
                                {{ trans('default_list_limit') }}
                            </label>
                            <input type="number" name="settings[default_list_limit]" id="default_list_limit"
                                class="form-control" minlength="1" min="1" required
                                value="<?php echo get_setting('default_list_limit', 15, true) ?>">
                        </div>
                    </div>
                </div>

            </div>
        </div>


        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('amount_settings') }}
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[currency_symbol]">
                                {{ trans('currency_symbol') }}
                            </label>
                            <input type="text" name="settings[currency_symbol]" id="settings[currency_symbol]"
                                class="form-control"
                                value="<?php echo get_setting('currency_symbol', '', true) }}">
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[currency_symbol_placement]">
                                {{ trans('currency_symbol_placement') }}
                            </label>
                            <select name="settings[currency_symbol_placement]" id="settings[currency_symbol_placement]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="before" {{ get_setting('currency_symbol_placement') == 'before' ? 'selected' : '' }}>
                                    {{ trans('before_amount') }}
                                </option>
                                <option value="after" {{ get_setting('currency_symbol_placement') == 'after' ? 'selected' : '' }}>
                                    {{ trans('after_amount') }}
                                </option>
                                <option value="afterspace" {{ get_setting('currency_symbol_placement') == 'afterspace' ? 'selected' : '' }}>
                                    {{ trans('after_amount_space') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[currency_code]">
                                {{ trans('currency_code') }}
                            </label>
                            <select name="settings[currency_code]"
                                id="settings[currency_code]"
                                class="form-control simple-select">
                                @foreach($gateway_currency_codes as $val => $key)
                                    <option value="{{ $val }}"
                                        {{ get_setting('currency_code' == '', true), $val ? 'selected' : '' }}>
                                        {{ $val }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="tax_rate_decimal_places">
                                {{ trans('tax_rate_decimal_places') }}
                            </label>
                            <select name="settings[tax_rate_decimal_places]" class="form-control simple-select"
                                id="tax_rate_decimal_places" data-minimum-results-for-search="Infinity">
                                <option value="2" {{ get_setting('tax_rate_decimal_places') == '2' ? 'selected' : '' }}>
                                    2
                                </option>
                                <option value="3" {{ get_setting('tax_rate_decimal_places') == '3' ? 'selected' : '' }}>
                                    3
                                </option>
                            </select>
                            <p class="help-block">{{ trans('tax_rate_decimal_places_hint') }}</p>

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[number_format]">
                                {{ trans('number_format') }}
                            </label>
                            <select name="settings[number_format]" id="settings[number_format]"
                                class="form-control simple-select"
                                data-minimum-results-for-search="Infinity">
                                @foreach($number_formats as $key => $value)
                                    <option value="{{ $key }}"
                                        {{ get_setting('number_format') == $value['label'] ? 'selected' : '' }}>
                                        {{ trans($value['label']) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[default_item_decimals]">
                                {{ trans('default_item_decimals') }}
                            </label>
                            <?php $current_default_item_decimals = get_setting('default_item_decimals'); ?>
                            <select name="settings[default_item_decimals]" id="settings[default_item_decimals]"
                                class="form-control simple-select"
                                data-minimum-results-for-search="Infinity">
                                <option value="1" {{ $current_default_item_decimals == '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $current_default_item_decimals == '2' ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $current_default_item_decimals == '3' ? 'selected' : '' }}>3</option>
                                <option value="4" {{ $current_default_item_decimals == '4' ? 'selected' : '' }}>4</option>
                                <option value="5" {{ $current_default_item_decimals == '5' ? 'selected' : '' }}>5</option>
                                <option value="6" {{ $current_default_item_decimals == '6' ? 'selected' : '' }}>6</option>
                                <option value="7" {{ $current_default_item_decimals == '7' ? 'selected' : '' }}>7</option>
                                <option value="8" {{ $current_default_item_decimals == '8' ? 'selected' : '' }}>8</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>


        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('dashboard') }}
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[quote_overview_period]">
                                {{ trans('quote_overview_period') }}
                            </label>
                            <select name="settings[quote_overview_period]" id="settings[quote_overview_period]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="this-month" {{ get_setting('quote_overview_period') == 'this-month' ? 'selected' : '' }}>
                                    {{ trans('this_month') }}
                                </option>
                                <option value="last-month" {{ get_setting('quote_overview_period') == 'last-month' ? 'selected' : '' }}>
                                    {{ trans('last_month') }}
                                </option>
                                <option value="this-quarter" {{ get_setting('quote_overview_period') == 'this-quarter' ? 'selected' : '' }}>
                                    {{ trans('this_quarter') }}
                                </option>
                                <option value="last-quarter" {{ get_setting('quote_overview_period') == 'last-quarter' ? 'selected' : '' }}>
                                    {{ trans('last_quarter') }}
                                </option>
                                <option value="this-year" {{ get_setting('quote_overview_period') == 'this-year' ? 'selected' : '' }}>
                                    {{ trans('this_year') }}
                                </option>
                                <option value="last-year" {{ get_setting('quote_overview_period') == 'last-year' ? 'selected' : '' }}>
                                    {{ trans('last_year') }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[invoice_overview_period]">
                                {{ trans('invoice_overview_period') }}
                            </label>
                            <select name="settings[invoice_overview_period]" id="settings[invoice_overview_period]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="this-month" {{ get_setting('invoice_overview_period') == 'this-month' ? 'selected' : '' }}>
                                    {{ trans('this_month') }}
                                </option>
                                <option value="last-month" {{ get_setting('invoice_overview_period') == 'last-month' ? 'selected' : '' }}>
                                    {{ trans('last_month') }}
                                </option>
                                <option value="this-quarter" {{ get_setting('invoice_overview_period') == 'this-quarter' ? 'selected' : '' }}>
                                    {{ trans('this_quarter') }}
                                </option>
                                <option value="last-quarter" {{ get_setting('invoice_overview_period') == 'last-quarter' ? 'selected' : '' }}>
                                    {{ trans('last_quarter') }}
                                </option>
                                <option value="this-year" {{ get_setting('invoice_overview_period') == 'this-year' ? 'selected' : '' }}>
                                    {{ trans('this_year') }}
                                </option>
                                <option value="last-year" {{ get_setting('invoice_overview_period') == 'last-year' ? 'selected' : '' }}>
                                    {{ trans('last_year') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="disable_quickactions">
                                {{ trans('disable_quickactions') }}
                            </label>
                            <select name="settings[disable_quickactions]" class="form-control simple-select"
                                id="disable_quickactions" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('disable_quickactions') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('interface') }}
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="disable_sidebar">
                                {{ trans('disable_sidebar') }}
                            </label>
                            <select name="settings[disable_sidebar]" class="form-control simple-select"
                                id="disable_sidebar" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('disable_sidebar') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[custom_title]">
                                {{ trans('custom_title') }}
                            </label>
                            <input type="text" name="settings[custom_title]" id="settings[custom_title]"
                                class="form-control"
                                value="{{ get_setting('custom_title', '', true) }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="monospace_amounts">
                                {{ trans('monospaced_font_for_amounts') }}
                            </label>
                            <select name="settings[monospace_amounts]" class="form-control simple-select"
                                id="monospace_amounts" data-minimum-results-for-search="Infinity">
                                <option value="0">{{ trans('no') }}</option>
                                <option value="1" {{ get_setting('monospace_amounts') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>

                            <p class="help-block">
                                {{ trans('example') }}:
                                <span style="font-family: Monaco, Lucida Console, monospace">
                                    {{ format_currency(123456.78) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="login_logo">
                                {{ trans('login_logo') }}
                            </label>
                            @if(get_setting('login_logo'))
                                <br/>
                                <img class="personal_logo"
                                    src="{{ base_url() }}uploads/{{ get_setting('login_logo') }}"><br>
                                <a href="{{ route('settings.remove-logo', ['type' => 'login']) }}">{{ trans('remove_logo') }}</a><br/>
                            @endif
                            <input type="file" name="login_logo" id="login_logo" class="form-control"/>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[reports_in_new_tab]">
                                {{ trans('open_reports_in_new_tab') }}
                            </label>
                            <select name="settings[reports_in_new_tab]" id="settings[reports_in_new_tab]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">{{ trans('no') }}</option>
                                <option value="1" {{ get_setting('reports_in_new_tab') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="settings[show_responsive_itemlist]">
                                {{ trans('show_responsive_itemlist') }}
                            </label>
                            <select name="settings[show_responsive_itemlist]" id="settings[show_responsive_itemlist]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('show_responsive_itemlist') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('system_settings') }}
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[bcc_mails_to_admin]">
                                {{ trans('bcc_mails_to_admin') }}
                            </label>
                            <select name="settings[bcc_mails_to_admin]" id="settings[bcc_mails_to_admin]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">{{ trans('no') }}</option>
                                <option value="1" {{ get_setting('bcc_mails_to_admin') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>

                            <p class="help-block">{{ trans('bcc_mails_to_admin_hint') }}</p>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="cron_key">
                                {{ trans('cron_key') }}
                            </label>
                            <div class="input-group">
                                <input type="text" name="settings[cron_key]" id="cron_key" class="form-control" readonly
                                    value="{{ get_setting('cron_key') }}">
                                <div class="input-group-btn">
                                    <button id="btn_generate_cron_key" type="button" class="btn btn-primary btn-block">
                                        <i class="fa fa-recycle fa-margin"></i> {{ trans('generate') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
