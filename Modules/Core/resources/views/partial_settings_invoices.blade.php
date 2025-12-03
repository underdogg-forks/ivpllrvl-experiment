<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2">
        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('invoices') }}
            </div>
            <div class="fi-section-body">
                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[default_invoice_group]">
                                {{ trans('default_invoice_group') }}
                            </label>
                            <select name="settings[default_invoice_group]" id="settings[default_invoice_group]"
                                class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($invoice_groups as $invoice_group)
                                <option value="{{ $invoice_group->invoice_group_id }}"
                                    {{ get_setting('default_invoice_group') == $invoice_group->invoice_group_id ? 'selected' : '' }}>
                                    {{ $invoice_group->invoice_group_name }}
                                </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[default_invoice_terms]">
                                {{ trans('default_terms') }}
                            </label>
                            <textarea name="settings[default_invoice_terms]" id="settings[default_invoice_terms]"
                                      class="fi-input" rows="4"
                                >{{ get_setting('default_invoice_terms', '', true) }}</textarea>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[invoice_default_payment_method]">
                                {{ trans('default_payment_method') }}
                            </label>
                            <select name="settings[invoice_default_payment_method]" class="fi-input simple-select"
                                id="settings[invoice_default_payment_method]" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($payment_methods as $payment_method)
                                <option value="{{ $payment_method->payment_method_id }}"
                                    <?php check_select($payment_method->payment_method_id, get_setting('invoice_default_payment_method')) ?>>
                                    {{ $payment_method->payment_method_name }}
                                </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[invoices_due_after]">
                                {{ trans('invoices_due_after') }}
                            </label>
                            <input type="number" name="settings[invoices_due_after]" id="settings[invoices_due_after]"
                                   class="fi-input" value="{{ get_setting('invoices_due_after') }}">
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[generate_invoice_number_for_draft]">
                                {{ trans('generate_invoice_number_for_draft') }}
                            </label>
                            <select name="settings[generate_invoice_number_for_draft]" class="fi-input simple-select"
                                    id="settings[generate_invoice_number_for_draft]" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('generate_invoice_number_for_draft') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[einvoicing]">
                                {{ trans('einvoicing_enable') }}
                            </label>
                            <select name="settings[einvoicing]" id="settings[einvoicing]"
                                class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('einvoicing') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                            <p class="help-block">
                                {{ trans('einvoicing_enable_help') }}
                                <a href="https://github.com/InvoicePlane/InvoicePlane-e-invoices" target="_blank">InvoicePlane-e-invoices</a>
                            </p>
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
                            <label for="settings[mark_invoices_sent_pdf]">
                                {{ trans('mark_invoices_sent_pdf') }}
                            </label>
                            <select name="settings[mark_invoices_sent_pdf]" id="settings[mark_invoices_sent_pdf]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('mark_invoices_sent_pdf') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[invoice_pre_password]">
                                {{ trans('invoice_pre_password') }}
                            </label>
                            <input type="text" name="settings[invoice_pre_password]" id="settings[invoice_pre_password]"
                                   class="fi-input"
                                   value="{{ get_setting('invoice_pre_password', '', true) }}">
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[pdf_watermark]">
                                {{ trans('pdf_watermark') }}
                            </label>
                            <select name="settings[pdf_watermark]" id="settings[pdf_watermark]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('pdf_watermark') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label>{{ trans('invoice_logo') }}</label>
@if(get_setting('invoice_logo'))
                                <br/>
                                <img class="personal_logo"
                                     src="{{ base_url() }}uploads/{{ get_setting('invoice_logo') }}">
                                <br>
                                {{ anchor('settings/remove_logo/invoice', trans('remove_logo')) }}<br/>
@endif
                            <input type="file" name="invoice_logo" size="40" class="fi-input"/>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('invoice_templates') }}
            </div>
            <div class="fi-section-body">
                <div class="help-block">
                    {{ trans('invoice_templates_info') }}
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[pdf_invoice_template]">
                                {{ trans('default_pdf_template') }}
                            </label>
                            <select name="settings[pdf_invoice_template]" id="settings[pdf_invoice_template]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($pdf_invoice_templates as $invoice_template)
                                <option value="{{ $invoice_template }}"
                                    {{ get_setting('pdf_invoice_template') == $invoice_template ? 'selected' : '' }}>
                                    {{ $invoice_template }}
                                </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[pdf_invoice_template_paid]">
                                {{ trans('pdf_template_paid') }}
                            </label>
                            <select name="settings[pdf_invoice_template_paid]" id="settings[pdf_invoice_template_paid]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($pdf_invoice_templates as $invoice_template)
                                <option value="{{ $invoice_template }}"
                                    {{ get_setting('pdf_invoice_template_paid') == $invoice_template ? 'selected' : '' }}>
                                    {{ $invoice_template }}
                                </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[pdf_invoice_template_overdue]">
                                {{ trans('pdf_template_overdue') }}
                            </label>
                            <select name="settings[pdf_invoice_template_overdue]" class="fi-input simple-select"
                                    id="settings[pdf_invoice_template_overdue]" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($pdf_invoice_templates as $invoice_template)
                                    <option value="{{ $invoice_template }}"
                                        {{ get_setting('pdf_invoice_template_overdue') == $invoice_template ? 'selected' : '' }}>
                                        {{ $invoice_template }}
                                    </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[public_invoice_template]">
                                {{ trans('default_public_template') }}
                            </label>
                            <select name="settings[public_invoice_template]" id="settings[public_invoice_template]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($public_invoice_templates as $invoice_template)
                                <option value="{{ $invoice_template }}"
                                    {{ get_setting('public_invoice_template') == $invoice_template ? 'selected' : '' }}>
                                    {{ $invoice_template }}
                                </option>
@endif
                            </select>
                        </div>

                    </div>
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[email_invoice_template]">
                                {{ trans('default_email_template') }}
                            </label>
                            <select name="settings[email_invoice_template]" id="settings[email_invoice_template]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($email_templates_invoice as $email_template)
                                <option value="{{ $email_template->email_template_id }}"
                                    {{ get_setting('email_invoice_template') == $email_template->email_template_id ? 'selected' : '' }}>
                                    {{ $email_template->email_template_title }}
                                </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[email_invoice_template_paid]">
                                {{ trans('email_template_paid') }}
                            </label>
                            <select name="settings[email_invoice_template_paid]" id="settings[email_invoice_template_paid]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($email_templates_invoice as $email_template)
                                <option value="{{ $email_template->email_template_id }}"
                                    {{ get_setting('email_invoice_template_paid') == $email_template->email_template_id ? 'selected' : '' }}>
                                    {{ $email_template->email_template_title }}
                                </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[email_invoice_template_overdue]">
                                {{ trans('email_template_overdue') }}
                            </label>
                            <select name="settings[email_invoice_template_overdue]" class="fi-input simple-select"
                                    id="settings[email_invoice_template_overdue]" data-minimum-results-for-search="Infinity">
                                <option value="">{{ trans('none') }}</option>
@foreach($email_templates_invoice as $email_template)
                                <option value="{{ $email_template->email_template_id }}"
                                    {{ get_setting('email_invoice_template_overdue') == $email_template->email_template_id ? 'selected' : '' }}>
                                    {{ $email_template->email_template_title }}
                                </option>
@endif
                            </select>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[pdf_invoice_footer]">
                                {{ trans('pdf_invoice_footer') }}
                            </label>
                            <textarea name="settings[pdf_invoice_footer]" id="settings[pdf_invoice_footer]"
                                      class="fi-input no-margin">{{ get_setting('pdf_invoice_footer', '', true) }}</textarea>
                            <p class="help-block">{{ trans('pdf_invoice_footer_hint') }}</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="fi-section" id="panel-qr-code-settings">
            <div class="fi-section-header">
                {{ trans('qr_code_settings') }}
            </div>
            <div class="fi-section-body">

<?php
$qr_code = get_setting('qr_code');
?>
                <div class="fi-field-wrp">
                    <div class="checkbox">
                        <label>
                            <input
                                type="hidden"
                                name="settings[qr_code]"
                                value="0"
                            >
                            <input
                                type="checkbox"
                                name="settings[qr_code]"
                                id="settings[qr_code]"
                                value="1"
                                <?php check_select($qr_code, 1, '==', true) ?>
                            >
                            {{ trans('qr_code_settings_enable') }}
                        </label>
                        <p class="help-block">{{ trans('qr_code_settings_enable_hint') }}</p>
                    </div>
                </div>

                <div class="row {{ $qr_code ? '' : 'hidden' }}">
                    <div class="col-xs-12">
                        <p class="alert alert-info no-padding">
                            <i class="fa fa-info"></i>{{ trans('qr_code_settings_enable_hint_users') }}&nbsp;<i class="fa fa-qrcode"></i>
                        </p>
                    </div>
                </div>

                <div class="row {{ $qr_code ? '' : 'hidden' }}">
                    <div class="col-xs-12 col-md-6">
                        <div class="fi-field-wrp">
                            <label for="settings[qr_code_recipient]">
                                {{ trans('qr_code_settings_recipient') }}
                            </label>
                            <input
                                type="text"
                                name="settings[qr_code_recipient]"
                                id="settings[qr_code_recipient]"
                                class="fi-input"
                                placeholder="<?php _htmlsc(trans('company')); ?>"
                                value="{{ get_setting('qr_code_recipient') }}"
                            >
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="fi-field-wrp">
                            <label for="settings[qr_code_iban]">
                                {{ trans('qr_code_settings_iban') }}
                            </label>
                            <input
                                type="text"
                                name="settings[qr_code_iban]"
                                id="settings[qr_code_iban]"
                                class="fi-input"
                                value="{{ get_setting('qr_code_iban') }}"
                            >
                        </div>
                    </div>
                </div>

                <div class="row {{ $qr_code ? '' : 'hidden' }}">
                    <div class="col-xs-12 col-md-6">
                        <div class="fi-field-wrp">
                            <label for="settings[qr_code_bic]">
                                {{ trans('qr_code_settings_bic') }}
                            </label>
                            <input
                                type="text"
                                name="settings[qr_code_bic]"
                                id="settings[qr_code_bic]"
                                class="fi-input"
                                value="{{ get_setting('qr_code_bic') }}"
                            >
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="fi-field-wrp">
                            <label for="settings[qr_code_remittance_text]">
                                {{ trans('qr_code_settings_remittance_text') }}
                            </label>
                            <input
                                type="text"
                                name="settings[qr_code_remittance_text]"
                                id="settings[qr_code_remittance_text]"
                                class="fi-input taggable"
                                value="{{ get_setting('qr_code_remittance_text') }}"
                                placeholder="{{{invoice_number}}}"
                            >
                        </div>

                        <div class="fi-section">
                            <div class="fi-section-header">
                                {{ trans('qr_code_settings_remittance_text_tags') }}
                            </div>
                            <div class="fi-section-body">
                                <?php $this->layout->load_view('email_templates/template-tags-invoices'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('email_settings') }}
            </div>
            <div class="fi-section-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">

                        <div class="fi-field-wrp">
                            <label for="settings[automatic_email_on_recur]">
                                {{ trans('automatic_email_on_recur') }}
                            </label>
                            <select name="settings[automatic_email_on_recur]" id="settings[automatic_email_on_recur]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ get_setting('automatic_email_on_recur') == '1' ? 'selected' : '' }}>
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
                {{ trans('other_settings') }}
            </div>
            <div class="fi-section-body">
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="fi-field-wrp">
                            <label for="settings[read_only_toggle]">
                                {{ trans('set_to_read_only') }}
                            </label>
                            <select name="settings[read_only_toggle]" id="settings[read_only_toggle]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="2" {{ get_setting('read_only_toggle') == '2' ? 'selected' : '' }}>
                                    {{ trans('sent') }}
                                </option>
                                <option value="3" {{ get_setting('read_only_toggle') == '3' ? 'selected' : '' }}>
                                    {{ trans('viewed') }}
                                </option>
                                <option value="4" {{ get_setting('read_only_toggle') == '4' ? 'selected' : '' }}>
                                    {{ trans('paid') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="fi-field-wrp">
                            <label for="settings[no_update_invoice_due_date_mail]">
                                {{ trans('no_update_invoice_due_date_mail') }}
                            </label>
                            <select name="settings[no_update_invoice_due_date_mail]" class="fi-input simple-select"
                                id="settings[no_update_invoice_due_date_mail]" data-minimum-results-for-search="Infinity">
                                <option value="1" {{ get_setting('no_update_invoice_due_date_mail') == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                                <option value="0" {{ get_setting('no_update_invoice_due_date_mail') == '0' ? 'selected' : '' }}>
                                    {{ trans('no') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
$sumex = get_setting('sumex');
// Set in ipconfig OR is 1 (in db)
if (SUMEX_SETTINGS || $sumex == '1') {
    ?>

        <div class="fi-section">
            <div class="fi-section-header">
                {{ trans('sumex_settings') }}
            </div>
            <div class="fi-section-body">

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="fi-field-wrp">
                            <label for="settings[sumex]">
                                {{ trans('invoice_sumex') }}
                            </label>
                            <select name="settings[sumex]" id="settings[sumex]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
                                <option value="0">
                                    {{ trans('no') }}
                                </option>
                                <option value="1" {{ $sumex == '1' ? 'selected' : '' }}>
                                    {{ trans('yes') }}
                                </option>
                            </select>
                            <p class="help-block">{{ trans('invoice_sumex_help') }}</p>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[sumex_sliptype]">
                                {{ trans('invoice_sumex_sliptype') }}
                            </label>
                            <select name="settings[sumex_sliptype]" id="settings[sumex_sliptype]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
<?php
        $slipTypes = ['esr9', 'esrRed'];
    foreach ($slipTypes as $k => $v) {
        ?>
                                <option value="{{ $k }}" <?php check_select(get_setting('sumex_sliptype'), $k) ?>>
                                    {{ trans('invoice_sumex_sliptype-' . $v) }}
                                </option>
@endif
                            </select>
                            <p class="help-block">{{ trans('invoice_sumex_sliptype_help') }}</p>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="fi-field-wrp">
                            <label for="settings[sumex_role]">
                                {{ trans('invoice_sumex_role') }}
                            </label>
                            <select name="settings[sumex_role]" id="settings[sumex_role]"
                                    class="fi-input simple-select">
<?php
        // Expect $sumex_roles to be passed from controller
        $roles = $sumex_roles ?? [];
    foreach ($roles as $k => $v) {
        ?>
                                <option value="{{ $k }}" <?php check_select(get_setting('sumex_role'), $k) ?>>
                                    {{ trans('invoice_sumex_role_' . $v) }}
                                </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[sumex_place]">
                                {{ trans('invoice_sumex_place') }}
                            </label>
                            <select name="settings[sumex_place]" id="settings[sumex_place]"
                                    class="fi-input simple-select" data-minimum-results-for-search="Infinity">
<?php
        // Expect $sumex_places to be passed from controller
        $places = $sumex_places ?? [];
    foreach ($places as $k => $v) {
        ?>
                                <option value="{{ $k }}" {{ get_setting('sumex_place') == $k ? 'selected' : '' }}>
                                    {{ trans('invoice_sumex_place_' . $v) }}
                                </option>
@endif
                            </select>
                        </div>

                        <div class="fi-field-wrp">
                            <label for="settings[sumex_canton]">
                                {{ trans('invoice_sumex_canton') }}
                            </label>
                            <select name="settings[sumex_canton]" id="settings[sumex_canton]"
                                    class="fi-input simple-select">
<?php
        // Expect $sumex_cantons to be passed from controller
        $cantons = $sumex_cantons ?? [];
    foreach ($cantons as $k => $v) {
        ?>
                                <option value="{{ $k }}" {{ get_setting('sumex_canton') == $k ? 'selected' : '' }}>
                                    {{ $v }}
                                </option>
@endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
} // End If Sumex
?>

    </div>
</div>
