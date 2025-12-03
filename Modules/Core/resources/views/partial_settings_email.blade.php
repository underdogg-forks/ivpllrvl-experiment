<script>
    $(function () {
        toggle_smtp_settings();

        $('#email_send_method').change(function () {
            toggle_smtp_settings();
        });

        function toggle_smtp_settings() {
            email_send_method = $('#email_send_method').val();

            if (email_send_method === 'smtp') {
                $('#div-smtp-settings').show();
            } else {
                $('#div-smtp-settings').hide();
            }
        }
    });
</script>

<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2">

        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('email') }}
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="form-group">
                            <label for="settings[email_pdf_attachment]">
                                {{ trans('email_pdf_attachment') }}
                            </label>
                            <select name="settings[email_pdf_attachment]" id="settings[email_pdf_attachment]"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">{{ trans('no') }}</option>
                                <option value="1" {{ get_setting('email_pdf_attachment') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email_send_method">
                                {{ trans('email_send_method') }}
                            </label>
                            <select name="settings[email_send_method]" id="email_send_method"
                                class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
                                <option value="phpmail" {{ get_setting('email_send_method') == 'phpmail' ? 'selected' : '' }}>
                                    {{ trans('email_send_method_phpmail') }}
                                </option>
                                <option value="sendmail" {{ get_setting('email_send_method') == 'sendmail' ? 'selected' : '' }}>
                                    {{ trans('email_send_method_sendmail') }}
                                </option>
                                <option value="smtp" {{ get_setting('email_send_method') == 'smtp' ? 'selected' : '' }}>
                                    {{ trans('email_send_method_smtp') }}
                                </option>
                            </select>
                        </div>

                        <div id="div-smtp-settings">
                            <hr>

                            <div class="form-group">
                                <label for="settings[smtp_server_address]">
                                    {{ trans('smtp_server_address') }}
                                </label>
                                <input type="text" name="settings[smtp_server_address]" id="settings[smtp_server_address]"
                                    class="form-control"
                                    value="{{ get_setting('smtp_server_address', '', true) }}">
                            </div>

                            <div class="form-group">
                                <label for="settings[smtp_mail_from]">
                                    {{ trans('smtp_mail_from') }}
                                </label>
                                <input type="email" name="settings[smtp_mail_from]" id="settings[smtp_mail_from]"
                                    class="form-control"
                                    value="{{ get_setting('smtp_mail_from', '', true) }}">
                            </div>

                            <div class="form-group">
                                <label for="settings[smtp_authentication]">
                                    {{ trans('smtp_requires_authentication') }}
                                </label>
                                <select name="settings[smtp_authentication]" id="settings[smtp_authentication]"
                                    class="form-control simple-select">
                                    <option value="0">
                                        {{ trans('no') }}
                                    </option>
                                    <option value="1" {{ get_setting('smtp_authentication') == '1' ? 'selected' : '' }}>
                                        {{ trans('yes') }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="settings[smtp_username]">
                                    {{ trans('smtp_username') }}
                                </label>
                                <input type="text" name="settings[smtp_username]" id="settings[smtp_username]"
                                    class="form-control"
                                    value="{{ get_setting('smtp_username', '', true) }}">
                            </div>

                            <div class="form-group">
                                <label for="smtp_password">
                                    {{ trans('smtp_password') }}
                                </label>
                                <input type="password" id="smtp_password" class="form-control"
                                    name="settings[smtp_password]" autocomplete="new-password"
                                    value="">
                                <input type="hidden" name="settings[smtp_password_field_is_password]" value="1">
                            </div>

                            <div class="form-group">
                                <div>
                                    <label for="settings[smtp_port]">
                                        {{ trans('smtp_port') }}
                                    </label>
                                    <input type="number" name="settings[smtp_port]" id="settings[smtp_port]"
                                        class="form-control"
                                        value="{{ get_setting('smtp_port') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="settings[smtp_security]">
                                    {{ trans('smtp_security') }}
                                </label>
                                <select name="settings[smtp_security]" id="settings[smtp_security]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                    <option value="">{{ trans('none') }}</option>
                                    <option value="ssl" {{ get_setting('smtp_security') == 'ssl' ? 'selected' : '' }}>
                                        {{ trans('smtp_ssl') }}
                                    </option>
                                    <option value="tls" {{ get_setting('smtp_security') == 'tls' ? 'selected' : '' }}>
                                        {{ trans('smtp_tls') }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="settings[smtp_verify_certs]">
                                    {{ trans('smtp_verify_certs') }}
                                </label>
                                <select name="settings[smtp_verify_certs]" id="settings[smtp_verify_certs]"
                                    class="form-control simple-select" data-minimum-results-for-search="Infinity">
                                    <option value="1">{{ trans('yes') }}</option>
                                    <option value="0" {{ get_setting('smtp_verify_certs') == '0' ? 'selected' : '' }}>
                                        {{ trans('no') }}
                                    </option>
                                </select>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
