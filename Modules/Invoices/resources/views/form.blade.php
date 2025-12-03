<form method="post">

    @csrf

    <div id="headerbar">
        <h1 class="headerbar-title">{{ trans('invoice_group_form') }}</h1>
        @include('core::header_buttons')
    </div>

    <div id="content">

        <div class="row">
            <div class="col-xs-12 col-md-6 col-md-offset-3">

                @include('core::alerts')

                <div class="form-group">
                    <label class="control-label" for="invoice_group_name">
                        {{ trans('name') }}
                    </label>
                    <input type="text" name="invoice_group_name" id="invoice_group_name" class="form-control"
                           value="{{ old('invoice_group_name', $invoice_group->invoice_group_name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="control-label" for="invoice_group_identifier_format">
                        {{ trans('identifier_format') }}
                    </label>
                    <input type="text" class="form-control taggable"
                           name="invoice_group_identifier_format" id="invoice_group_identifier_format"
                           value="{{ old('invoice_group_identifier_format', $invoice_group->invoice_group_identifier_format ?? '') }}"
                           placeholder="INV-{{{id}}}" required>
                </div>

                <div class="form-group">
                    <label class="control-label" for="invoice_group_next_id">
                        {{ trans('next_id') }}
                    </label>
                    <input type="number" name="invoice_group_next_id" id="invoice_group_next_id" class="form-control"
                           value="{{ old('invoice_group_next_id', $invoice_group->invoice_group_next_id ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="control-label" for="invoice_group_left_pad">
                        {{ trans('left_pad') }}
                    </label>
                    <input type="number" name="invoice_group_left_pad" id="invoice_group_left_pad" class="form-control"
                           value="{{ old('invoice_group_left_pad', $invoice_group->invoice_group_left_pad ?? '') }}" required>
                </div>

                <hr>

                <div class="form-group no-margin">

                    <label for="tags_client">{{ trans('identifier_format_template_tags') }}</label>

                    <p class="small">{{ trans('identifier_format_template_tags_instructions') }}</p>

                    <select id="tags_client" class="tag-select form-control">
                        <option value="{{{id}}}">
                            {{ trans('id') }}
                        </option>
                        <option value="{{{year}}}">
                            {{ trans('current_year') }}
                        </option>
                        <option value="{{{yy}}}">
                            {{ trans('current_yy') }}
                        </option>
                        <option value="{{{month}}}">
                            {{ trans('current_month') }}
                        </option>
                        <option value="{{{day}}}">
                            {{ trans('current_day') }}
                        </option>
                    </select>

                </div>

            </div>
        </div>

    </div>

</form>
