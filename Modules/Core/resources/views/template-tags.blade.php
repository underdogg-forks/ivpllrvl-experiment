<div class="fi-section">
    <div class="fi-section-header">{{ trans('email_template_tags') }}</div>
    <div class="fi-section-body">

        <p class="small">{{ trans('email_template_tags_instructions') }}</p>

        <div class="fi-field-wrp">
            <label for="tags_client">{{ trans('client') }}</label>
            <select id="tags_client" class="tag-select fi-input">
                <option value="{{{client_name}}}">
                    {{ trans('client_name') }}
                </option>
                <option value="{{{client_surname}}}">
                    {{ trans('client_surname') }}
                </option>
                <optgroup label="{{ trans('address') }}">
                    <option value="{{{client_address_1}}}">
                        {{ trans('street_address') }}
                    </option>
                    <option value="{{{client_address_2}}}">
                        {{ trans('street_address_2') }}
                    </option>
                    <option value="{{{client_city}}}">
                        {{ trans('city') }}
                    </option>
                    <option value="{{{client_state}}}">
                        {{ trans('state') }}
                    </option>
                    <option value="{{{client_zip}}}">
                        {{ trans('zip') }}
                    </option>
                    <option value="{{{client_country}}}">
                        {{ trans('country') }}
                    </option>
                </optgroup>
                <optgroup label="{{ trans('contact_information') }}">
                    <option value="{{{client_phone}}}">
                        {{ trans('phone') }}
                    </option>
                    <option value="{{{client_fax}}}">
                        {{ trans('fax') }}
                    </option>
                    <option value="{{{client_mobile}}}">
                        {{ trans('mobile') }}
                    </option>
                    <option value="{{{client_email}}}">
                        {{ trans('email') }}
                    </option>
                    <option value="{{{client_web}}}">
                        {{ trans('web_address') }}
                    </option>
                </optgroup>
                <optgroup label="{{ trans('tax_information') }}">
                    <option value="{{{client_vat_id}}}">
                        {{ trans('vat_id') }}
                    </option>
                    <option value="{{{client_tax_code}}}">
                        {{ trans('tax_code') }}
                    </option>
                </optgroup>
<?php
$sumex = get_setting('sumex') == '1';
if ($sumex) {
    ?>
                <optgroup label="{{ trans('sumex_information') }}">
                    <option value="{{{client_avs}}}">
                        {{ trans('sumex_ssn') }}
                    </option>
                    <option value="{{{client_insurednumber}}}">
                        {{ trans('sumex_insurednumber') }}
                    </option>
                    <option value="{{{client_weka}}}">
                        {{ trans('sumex_veka') }}
                    </option>
                </optgroup>
<?php
}
if ($custom_fields['ip_client_custom']) {
    ?>
                <optgroup label="{{ trans('custom_fields') }}">
                    @foreach($custom_fields['ip_client_custom'] as $custom)
                        <option value="{{{{{ 'ip_cf_' . $custom->custom_field_id }}}}}">
                            {{ $custom->custom_field_label . ' (ID ' . $custom->custom_field_id . ')' }}
                        </option>
                    @endif
                </optgroup>
@endif
            </select>
        </div>

        <div class="fi-field-wrp">
            <label for="tags_user">{{ trans('user') }}</label>
            <select id="tags_user" class="tag-select fi-input">
                <option value="{{{user_name}}}">
                    {{ trans('name') }}
                </option>
                <option value="{{{user_company}}}">
                    {{ trans('company') }}
                </option>
                <optgroup label="{{ trans('address') }}">
                    <option value="{{{user_address_1}}}">
                        {{ trans('street_address') }}
                    </option>
                    <option value="{{{user_address_2}}}">
                        {{ trans('street_address_2') }}
                    </option>
                    <option value="{{{user_city}}}">
                        {{ trans('city') }}
                    </option>
                    <option value="{{{user_state}}}">
                        {{ trans('state') }}
                    </option>
                    <option value="{{{user_zip}}}">
                        {{ trans('zip') }}
                    </option>
                    <option value="{{{user_country}}}">
                        {{ trans('country') }}
                    </option>
                </optgroup>
                <optgroup label="{{ trans('contact_information') }}">
                    <option value="{{{user_phone}}}">
                        {{ trans('phone') }}
                    </option>
                    <option value="{{{user_fax}}}">
                        {{ trans('fax') }}
                    </option>
                    <option value="{{{user_mobile}}}">
                        {{ trans('mobile') }}
                    </option>
                    <option value="{{{user_email}}}">
                        {{ trans('email') }}
                    </option>
                    <option value="{{{user_web}}}">
                        {{ trans('web_address') }}
                    </option>
                </optgroup>
                <optgroup label="{{ trans('tax_information') }}">
                    <option value="{{{user_vat_id}}}">
                        {{ trans('vat_id') }}
                    </option>
                    <option value="{{{user_tax_code}}}">
                        {{ trans('tax_code') }}
                    </option>
                </optgroup>
                <optgroup label="{{ trans('bank_information') }}">
                    <option value="{{{user_bank}}}">
                        {{ trans('bank') }}
                    </option>
                    <option value="{{{user_iban}}}">
                        IBAN
                    </option>
                    <option value="{{{user_bic}}}">
                        BIC
                    </option>
                </optgroup>
@if($sumex)
                <optgroup label="{{ trans('sumex_information') }}">
                    <option value="{{{user_subscribernumber}}}">
                        {{ trans('user_subscriber_number') }}
                    </option>
                    <option value="{{{user_gln}}}">
                        {{ trans('gln') }}
                    </option>
                    <option value="{{{user_rcc}}}">
                        {{ trans('sumex_rcc') }}
                    </option>
                </optgroup>
<?php
}
if ($custom_fields['ip_user_custom']) {
    ?>
                <optgroup label="{{ trans('custom_fields') }}">
                    @foreach($custom_fields['ip_user_custom'] as $custom)
                        <option value="{{{{{ 'ip_cf_' . $custom->custom_field_id }}}}}">
                            {{ $custom->custom_field_label . ' (ID ' . $custom->custom_field_id . ')' }}
                        </option>
                    @endif
                </optgroup>
@endif
            </select>
        </div>

        <?php $this->layout->load_view('email_templates/template-tags-invoices'); ?>

        <div class="fi-field-wrp">
            <label for="tags_quote">{{ trans('quotes') }}</label>
            <select id="tags_quote" class="tag-select fi-input">
                <option value="{{{quote_number}}}">
                    {{ trans('id') }}
                </option>
                <optgroup label="{{ trans('quote_dates') }}">
                    <option value="{{{quote_date_created}}}">
                        {{ trans('quote_date') }}
                    </option>
                    <option value="{{{quote_date_expires}}}">
                        {{ trans('expires') }}
                    </option>
                </optgroup>
                <optgroup label="{{ trans('quote_amounts') }}">
                    <option value="{{{quote_item_subtotal}}}">
                        {{ trans('subtotal') }}
                    </option>
                    <option value="{{{quote_tax_total}}}">
                        {{ trans('quote_tax') }}
                    </option>
                    <option value="{{{quote_item_discount}}}">
                        {{ trans('discount') }}
                    </option>
                    <option value="{{{quote_total}}}">
                        {{ trans('total') }}
                    </option>
                </optgroup>

                <optgroup label="{{ trans('extra_information') }}">
                    <option value="{{{quote_guest_url}}}">
                        {{ trans('guest_url') }}
                    </option>
                </optgroup>
@if($custom_fields['ip_quote_custom'])

                <optgroup label="{{ trans('custom_fields') }}">
                    @foreach($custom_fields['ip_quote_custom'] as $custom)
                        <option value="{{{{{ 'ip_cf_' . $custom->custom_field_id }}}}}">
                            {{ $custom->custom_field_label . ' (ID ' . $custom->custom_field_id . ')' }}
                        </option>
                    @endif
                </optgroup>
@endif
            </select>
        </div>
@if($sumex)
        <div class="fi-field-wrp">
            <label for="tags_sumex">{{ trans('invoice_sumex') }}</label>
            <select id="tags_sumex" class="tag-select fi-input">
                <option value="{{{sumex_reason}}}">
                    {{ trans('reason') }}
                </option>
                <option value="{{{sumex_diagnosis}}}">
                    {{ trans('invoice_sumex_diagnosis') }}
                </option>
                <option value="{{{sumex_observations}}}">
                    {{ trans('sumex_observations') }}
                </option>
                <option value="{{{sumex_treatmentstart}}}">
                    {{ trans('treatment_start') }}
                </option>
                <option value="{{{sumex_treatmentend}}}">
                    {{ trans('treatment_end') }}
                </option>
                <option value="{{{sumex_casedate}}}">
                    {{ trans('case_date') }}
                </option>
                <option value="{{{sumex_casenumber}}}">
                    {{ trans('case_number') }}
                </option>
            </select>
        </div>
@endif
    </div>
</div>
