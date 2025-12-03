<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2">

        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('projects') }}
            </div>
            <div class="fi-section-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[projects_enabled]">
                                {{ trans('enable_projects') }}
                            </label>
                            <select name="settings[projects_enabled]" class="fi-input simple-select"
                                id="settings[projects_enabled]" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('projects_enabled') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[default_hourly_rate]">
                                {{ trans('default_hourly_rate') }}
                            </label>
                            <div class="input-group">
                                <input type="text" name="settings[default_hourly_rate]" id="settings[default_hourly_rate]"
                                    class="fi-input amount"
                                    value="{{ get_setting('default_hourly_rate') ? format_amount(get_setting('default_hourly_rate')) : get_setting('default_hourly_rate') }}">
                                <span class="input-group-addon">{{ get_setting('currency_symbol') }}</span>
                                <input type="hidden" name="settings[default_hourly_rate_field_is_amount]" value="1">
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
