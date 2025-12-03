<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2">

        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('quote') }}
            </div>
            <div class="fi-section-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[default_quote_group]">
                                {{ trans('default_quote_group') }}
                            </label>
                            <select name="settings[default_quote_group]" id="settings[default_quote_group]"
                                class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
                                @foreach ($invoice_groups as $invoice_group)
                                    <option value="{{ $invoice_group->invoice_group_id }}"
                                        {{ get_setting('default_quote_group') == $invoice_group->invoice_group_id ? 'selected' : '' }}>
                                        {{ $invoice_group->invoice_group_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[default_quote_notes]">
                                {{ trans('default_notes') }}
                            </label>
                            <textarea name="settings[default_quote_notes]" id="settings[default_quote_notes]" rows="3"
                                class="fi-input">{{ get_setting('default_quote_notes', '', true) }}</textarea>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[quotes_expire_after]">
                                {{ trans('quotes_expire_after') }}
                            </label>
                            <input type="number" name="settings[quotes_expire_after]" id="settings[quotes_expire_after]"
                                class="fi-input"
                                value="{{ get_setting('quotes_expire_after') }}">
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[generate_quote_number_for_draft]">
                                {{ trans('generate_quote_number_for_draft') }}
                            </label>
                            <select name="settings[generate_quote_number_for_draft]" class="fi-input simple-select"
                                id="settings[generate_quote_number_for_draft]" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('generate_quote_number_for_draft') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('pdf_settings') }}
            </div>
            <div class="fi-section-body">
                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[mark_quotes_sent_pdf]">
                                {{ trans('mark_quotes_sent_pdf') }}
                            </label>
                            <select name="settings[mark_quotes_sent_pdf]" id="settings[mark_quotes_sent_pdf]"
                                class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('mark_quotes_sent_pdf') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[quote_pre_password]">
                                {{ trans('quote_pre_password') }}
                            </label>
                            <input type="text" name="settings[quote_pre_password]" id="settings[quote_pre_password]"
                                class="fi-input" value="{{ get_setting('quote_pre_password', '', true) }}">
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('quote_templates') }}
            </div>
            <div class="fi-section-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[pdf_quote_template]">
                                {{ trans('default_pdf_template') }}
                            </label>
                            <select name="settings[pdf_quote_template]" id="settings[pdf_quote_template]"
                                class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
                                @foreach ($pdf_quote_templates as $quote_template)
                                    <option value="{{ $quote_template }}"
                                        {{ get_setting('pdf_quote_template') == $quote_template ? 'selected' : '' }}>
                                        {{ $quote_template }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[public_quote_template]">
                                {{ trans('default_public_template') }}
                            </label>
                            <select name="settings[public_quote_template]" id="settings[public_quote_template]"
                                class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
                                @foreach ($public_quote_templates as $quote_template)
                                    <option value="{{ $quote_template }}"
                                        {{ get_setting('public_quote_template') == $quote_template ? 'selected' : '' }}>
                                        {{ $quote_template }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[email_quote_template]">
                                {{ trans('default_email_template') }}
                            </label>
                            <select name="settings[email_quote_template]" id="settings[email_quote_template]"
                                class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
                                @foreach ($email_templates_quote as $email_template)
                                    <option value="{{ $email_template->email_template_id }}"
                                        {{ get_setting('email_quote_template') == $email_template->email_template_id ? 'selected' : '' }}>
                                        {{ $email_template->email_template_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[pdf_quote_footer]">
                                {{ trans('pdf_quote_footer') }}
                            </label>
                            <textarea name="settings[pdf_quote_footer]" id="settings[pdf_quote_footer]"
                                class="fi-input no-margin">{{ get_setting('pdf_quote_footer', '', true) }}</textarea>
                            <p class="help-block">{{ trans('pdf_quote_footer_hint') }}</p>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
