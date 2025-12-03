<script>
    $(function () {
        // Display the copy invoice modal
        $('#modal_copy_invoice').modal('show');

        // Select2 for all select inputs
        $(".simple-select").select2();

        @include('crm::script_select2_client_id.js')

        // Creates the invoice
        $('#copy_invoice_confirm').click(function () {
            show_loader(); // Show spinner
            $.post("{{ route('invoices.ajax.copy-invoice') }}", {
                    legacy_calculation: legacy_calculation, // Automatic. From meta (see script)
                    invoice_id: {{ $invoice_id }},
                    client_id: $('#client_id').val(),
                    user_id: $('#user_id').val(),
                    invoice_date_created: $('#invoice_date_created_modal').val(),
                    invoice_group_id: $('#invoice_group_id').val(),
                    invoice_password: $('#invoice_password').val(),
                    invoice_time_created: '{{ date('H:i:s') }}',
                    payment_method: $('#payment_method').val()
                },
                function (data) {
                    var response = json_parse(data, {{ (int) config('app.debug') }});
                    if (response.success === 1) {
                        window.location = "{{ route('invoices.view', '') }}/" + response.invoice_id;
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

<div id="modal_copy_invoice" class="modal modal-lg" role="dialog" aria-labelledby="modal_copy_invoice"
     aria-hidden="true">
    <form class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="panel-title">{{ trans('copy_invoice') }}</h4>
        </div>
        <div class="modal-body">

            <input type="hidden" name="user_id" id="user_id" value="{{ $invoice->user_id }}">
            <input type="hidden" name="payment_method" id="payment_method" class="form-control"
                   value="{{ $invoice->payment_method }}">
            <input class="hidden" id="input_permissive_search_clients"
                   value="{{ get_setting('enable_permissive_search_clients') }}">

            <div class="form-group has-feedback">
                <label for="client_id">{{ trans('client') }}</label>
                <div class="input-group">
                    <span id="toggle_permissive_search_clients" class="input-group-addon" title="{{ trans('enable_permissive_search_clients') }}" style="cursor:pointer;">
                        <i class="fa fa-toggle-{{ get_setting('enable_permissive_search_clients') ? 'on' : 'off' }} fa-fw" ></i>
                    </span>
                    <select name="client_id" id="client_id" class="client-id-select form-control" autofocus="autofocus" required="required">
                        @if (!empty($client))
                            <option value="{{ $client->client_id }}">{{ format_client($client, false) }}</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="form-group has-feedback">
                <label for="invoice_date_created_modal">{{ trans('invoice_date') }}: </label>

                <div class="input-group">
                    <input name="invoice_date_created_modal" id="invoice_date_created_modal" class="form-control datepicker"
                           value="{{ date_from_mysql(date('Y-m-d', time()), true) }}">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar fa-fw"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="invoice_password">{{ trans('invoice_password') }}</label>
                <input type="text" name="invoice_password" id="invoice_password" class="form-control"
                       value="{{ get_setting('invoice_pre_password') == '' ? '' : get_setting('invoice_pre_password') }}"
                       style="margin: 0 auto;" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="invoice_group_id">{{ trans('invoice_group') }}: </label>
                <select name="invoice_group_id" id="invoice_group_id" class="form-control simple-select">
                    @foreach ($invoice_groups as $invoice_group)
                        <option value="{{ $invoice_group->invoice_group_id }}"
                            {{ get_setting('default_invoice_group') == $invoice_group->invoice_group_id ? 'selected' : '' }}>
                            {{ $invoice_group->invoice_group_name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="modal-footer">
            <div class="btn-group">
                <button class="btn btn-success" id="copy_invoice_confirm" type="button">
                    <i class="fa fa-check"></i> {{ trans('submit') }}
                </button>
                <button class="btn btn-danger" type="button" data-dismiss="modal">
                    <i class="fa fa-times"></i> {{ trans('cancel') }}
                </button>
            </div>
        </div>

    </form>

</div>
