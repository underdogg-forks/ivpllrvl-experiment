<script>
    $(function () {
        // Display the copy quote modal
        $('#modal_copy_quote').modal('show');

        // Select2 for all select inputs
        $(".simple-select").select2();

        @include('crm::clients.script_select2_client_id')

        // Creates the quote
        $('#copy_quote_confirm').click(function () {
            show_loader(); // Show spinner
            $.post("{{ route('quotes.ajax.copy') }}", {
                    legacy_calculation: legacy_calculation, // Automatic. From meta (see script)
                    quote_id: {{ $quote_id }},
                    client_id: $('#client_id').val(),
                    user_id: $('#user_id').val(),
                    quote_date_created: $('#quote_date_created_modal').val(),
                    invoice_group_id: $('#invoice_group_id').val(),
                    quote_password: $('#quote_password').val(),
                },
                function (data) {
                    var response = json_parse(data, {{ (int) config('app.debug') }});
                    if (response.success === 1) {
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
                }
            );
        });
    });

</script>

<div id="modal_copy_quote" class="modal modal-lg" role="dialog" aria-labelledby="modal_copy_quote" aria-hidden="true">
    <form class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="fi-section-title">{{ trans('copy_quote') }}</h4>
        </div>
        <div class="modal-body">

            <input type="hidden" name="user_id" id="user_id" value="{{ $quote->user_id }}">

            <input class="hidden" id="input_permissive_search_clients"
                   value="{{ get_setting('enable_permissive_search_clients') }}">

            <div class="fi-field-wrp has-feedback">
                <label for="client_id">{{ trans('client') }}</label>
                <div class="input-group">
                    <span id="toggle_permissive_search_clients" class="input-group-addon" title="{{ trans('enable_permissive_search_clients') }}" style="cursor:pointer;">
                        <i class="fa fa-toggle-{{ get_setting('enable_permissive_search_clients') ? 'on' : 'off' }} fa-fw" ></i>
                    </span>
                    <select name="client_id" id="client_id" class="client-id-select fi-input" autofocus="autofocus">
                        @if (!empty($client))
                            <option value="{{ $client->client_id }}">{{ format_client($client, false) }}</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="fi-field-wrp has-feedback">
                <label for="quote_date_created_modal">{{ trans('quote_date') }}</label>
                <div class="input-group">
                    <input name="quote_date_created_modal" id="quote_date_created_modal"
                           class="fi-input datepicker"
                           value="{{ date_from_mysql(date('Y-m-d', time()), true) }}">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar fa-fw"></i>
                    </span>
                </div>
            </div>

            <div class="fi-field-wrp">
                <label for="invoice_group_id">{{ trans('invoice_group') }}</label>
                <select name="invoice_group_id" id="invoice_group_id" class="fi-input simple-select">
                    @foreach ($invoice_groups as $invoice_group)
                        <option value="{{ $invoice_group->invoice_group_id }}"
                            {{ get_setting('default_quote_group') != $invoice_group->invoice_group_id ? '' : 'selected="selected"' }}>
                            {{ $invoice_group->invoice_group_name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="modal-footer">
            <div class="btn-group">
                <button class="fi-btn-success" id="copy_quote_confirm" type="button">
                    <i class="fa fa-check"></i> {{ trans('submit') }}
                </button>
                <button class="fi-btn-danger" type="button" data-dismiss="modal">
                    <i class="fa fa-times"></i> {{ trans('cancel') }}
                </button>
            </div>
        </div>

    </form>

</div>
