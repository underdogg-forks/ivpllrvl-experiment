<script>
    $(function () {
        // Display the create quote modal
        $('#create-quote').modal('show');

        // Select2 for all select inputs
        $('.simple-select').select2();

        @include('crm::clients.script_select2_client_id')

        // Creates the quote
        $('#quote_create_confirm').click(function () {
            show_loader(); // Show spinner
            // Posts the data to validate and create the quote;
            // will create the new client if necessary
            $.post("{{ route('quotes.ajax.create') }}", {
                    client_id: $('#create_quote_client_id').val(),
                    quote_date_created: $('#quote_date_created').val(),
                    quote_password: $('#quote_password').val(),
                    user_id: '{{ auth()->id() }}',
                    invoice_group_id: $('#invoice_group_id').val()
                },
                function (data) {
                    var response = json_parse(data, {{ (int) config('app.debug') }});
                    if (response.success === 1) {
                        // The validation was successful and quote was created
                        window.location = "{{ route('quotes.view', '') }}/" + response.quote_id;
                    }
                    else {
                        // The validation was not successful
                        close_loader();
                        $('.control-group').removeClass('has-error');
                        for (var key in response.validation_errors) {
                            $('#' + key).parent().parent().addClass('has-error');
                        }
                    }
                });
        });
    });
</script>

<div id="create-quote" class="modal modal-lg" role="dialog" aria-labelledby="modal_create_quote" aria-hidden="true">
    <form class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="panel-title">{{ trans('create_quote') }}</h4>
        </div>
        <div class="modal-body">

            <input class="hidden" id="input_permissive_search_clients"
                   value="{{ get_setting('enable_permissive_search_clients') }}">

            <div class="form-group has-feedback">
                <label for="create_quote_client_id">{{ trans('client') }}</label>
                <div class="input-group">
                    <span id="toggle_permissive_search_clients" class="input-group-addon" title="{{ trans('enable_permissive_search_clients') }}" style="cursor:pointer;">
                        <i class="fa fa-toggle-{{ get_setting('enable_permissive_search_clients') ? 'on' : 'off' }} fa-fw" ></i>
                    </span>
                    <select name="client_id" id="create_quote_client_id" class="client-id-select form-control"
                            autofocus="autofocus" required>
                        @if (!empty($client))
                            <option value="{{ $client->client_id }}">{{ format_client($client, false) }}</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="form-group has-feedback">
                <label for="quote_date_created">
                    {{ trans('quote_date') }}
                </label>

                <div class="input-group">
                    <input name="quote_date_created" id="quote_date_created"
                           class="form-control datepicker"
                           value="{{ date(date_format_setting()) }}" required>
                    <span class="input-group-addon">
                        <i class="fa fa-calendar fa-fw"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="quote_password">{{ trans('quote_password') }}</label>
                <input type="text" name="quote_password" id="quote_password" class="form-control"
                       value="{{ get_setting('quote_pre_password') ? '' : get_setting('quote_pre_password') }}"
                       autocomplete="off">
            </div>

            <div class="form-group">
                <label for="invoice_group_id">{{ trans('invoice_group') }}: </label>
                <select name="invoice_group_id" id="invoice_group_id"
                    class="form-control simple-select" data-minimum-results-for-search="Infinity" required>
                    @foreach ($invoice_groups as $invoice_group)
                        <option value="{{ $invoice_group->invoice_group_id }}"
                            {{ check_select(get_setting('default_quote_group'), $invoice_group->invoice_group_id) }}>
                            {{ $invoice_group->invoice_group_name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="modal-footer">
            <div class="btn-group">
                <button class="btn btn-success ajax-loader" id="quote_create_confirm" type="button">
                    <i class="fa fa-check"></i> {{ trans('submit') }}
                </button>
                <button class="btn btn-danger" type="button" data-dismiss="modal">
                    <i class="fa fa-times"></i> {{ trans('cancel') }}
                </button>
            </div>
        </div>

    </form>

</div>
