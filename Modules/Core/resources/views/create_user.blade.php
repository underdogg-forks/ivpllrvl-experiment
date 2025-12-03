<script>
    $(function () {
        $("[name='user_country']").select2({
            placeholder: "{{ trans('country') }}",
            allowClear: true
        });

        var password_input = $('.passwordmeter-input');
        if (password_input) {
            password_input.on('input', function () {
                var strength = zxcvbn(password_input.val());

                $('.passmeter-2, .passmeter-3').hide();
                if (strength.score === 4) {
                    $('.passmeter-2, .passmeter-3').show();
                } else if (strength.score === 3) {
                    $('.passmeter-2').show();
                }
            });
        }
    });
</script>

<script src="<?php _core_asset('js/zxcvbn.js'); ?>"></script>

<div class="container">
    <div class="install-panel">

        <h1 id="logo"><span>InvoicePlane</span></h1>

        <form method="post" action="{{ route($this->uri->uri_string()) }}">

            <?php _csrf_field(); ?>

            <input type="hidden" name="user_type" value="1">

            <legend>{{ trans('setup_create_user') }}</legend>

            {{ $this->layout->load_view('layout/alerts') }}

            <p>{{ trans('setup_create_user_message') }}</p>

            <div class="form-group">
                <label for="user_email">
                    {{ trans('email_address') }}
                </label>
                <input type="email" name="user_email" id="user_email" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_email', true) }}">
                <span class="help-block">{{ trans('setup_user_email_info') }}</span>
            </div>

            <div class="form-group">
                <label for="user_name">
                    {{ trans('name') }}
                </label>
                <input type="text" name="user_name" id="user_name" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_name', true) }}">
                <span class="help-block">{{ trans('setup_user_name_info') }}</span>
            </div>

            <div class="form-group">
                <label for="user_password">
                    {{ trans('password') }}
                </label>
                <input type="password" name="user_password" id="user_password"
                       class="form-control passwordmeter-input">
                <div class="progress" style="height:3px;">
                    <div class="progress-bar progress-bar-danger passmeter passmeter-1" style="width: 33%"></div>
                    <div class="progress-bar progress-bar-warning passmeter passmeter-2"
                         style="display: none; width: 33%"></div>
                    <div class="progress-bar progress-bar-success passmeter passmeter-3"
                         style="display: none; width: 34%"></div>
                </div>

                <span class="help-block">{{ trans('setup_user_password_info') }}</span>
            </div>

            <div class="form-group">
                <label for="user_passwordv">
                    {{ trans('verify_password') }}
                </label>
                <input type="password" name="user_passwordv" id="user_passwordv" class="form-control">
                <span class="help-block">{{ trans('setup_user_password_verify_info') }}</span>
            </div>

            <div class="form-group">
                <label for="user_language">
                    {{ trans('language') }}
                </label>
                <select name="user_language" id="user_language" class="form-control simple-select">
                    <option value="system">
                        {{ trans('use_system_language') ?>
                    </option>
                    @foreach($languages as $language)
                        <option value="<?php echo $language }}">
                            {{ ucfirst($language) }}
                        </option>
                    @endif
                </select>
            </div>

            <legend>{{ trans('address') }}</legend>
            <p>{{ trans('setup_user_address_info') }}</p>

            <div class="form-group">
                <label>
                    {{ trans('street_address') }}
                </label>
                <input type="text" name="user_address_1" id="user_address_1" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_address_1', true) }}">
            </div>

            <div class="form-group">
                <label>
                    {{ trans('street_address_2') }}
                </label>
                <input type="text" name="user_address_2" id="user_address_2" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_address_2', true) }}"
                       placeholder="{{ trans('optional') }}">
            </div>

            <div class="form-group">
                <label>
                    {{ trans('city') }}
                </label>
                <input type="text" name="user_city" id="user_city" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_city', true) }}"
                       placeholder="{{ trans('optional') }}">
            </div>

            <div class="form-group">
                <label>
                    {{ trans('state') }}
                </label>
                <input type="text" name="user_state" id="user_state" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_state', true) }}"
                       placeholder="{{ trans('optional') }}">
            </div>

            <div class="form-group">
                <label>
                    {{ trans('zip_code') }}
                </label>
                <input type="text" name="user_zip" id="user_zip" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_zip', true) }}"
                       placeholder="{{ trans('optional') }}">
            </div>

            <div class="form-group">
                <label>
                    {{ trans('country') }}
                </label>
                <select name="user_country" class="form-control simple-select">
                    <option value="">{{ trans('none') }}</option>
                    @foreach($countries as $cldr => $country)
                        <option value="{{ $cldr }}"
                            {{ $this->mdl_users->form_value('user_country') == $cldr ? 'selected' : '' }}>
                            {{ $country ?>
                        </option>
                    @endif
                </select>
            </div>

            <legend>{{ trans('setup_other_contact') }}</legend>

            <p>{{ trans('setup_user_contact_info') }}</p>

            <div class="form-group">
                <label>
                    {{ trans('phone') }}
                </label>
                <input type="text" name="user_phone" id="user_phone" class="form-control"
                       value="<?php echo $this->mdl_users->form_value('user_phone', true) }}"
                       placeholder="{{ trans('optional') }}">
            </div>

            <div class="form-group">
                <label>
                    {{ trans('fax') }}
                </label>
                <input type="text" name="user_fax" id="user_fax" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_fax', true) }}"
                       placeholder="{{ trans('optional') }}">
            </div>

            <div class="form-group">
                <label>
                    {{ trans('mobile') }}
                </label>
                <input type="text" name="user_mobile" id="user_mobile" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_mobile', true) }}"
                       placeholder="{{ trans('optional') }}">
            </div>

            <div class="form-group">
                <label>
                    {{ trans('web') }}
                </label>
                <input type="text" name="user_web" id="user_web" class="form-control"
                       value="{{ $this->mdl_users->form_value('user_web', true) }}"
                       placeholder="{{ trans('optional') }}">
            </div>

            <input type="submit" class="btn btn-success" name="btn_continue"
                   value="{{ trans('continue') }}">

        </form>

    </div>
</div>
