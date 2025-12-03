<script>
    $(function () {
        $('#modal-create-credit-invoice').modal('show');
        $('#create-credit-confirm').click(function () {
            show_loader(); // Show spinner
            $.post("{{ route('invoices.create-credit') }}", {
                    invoice_id: {{ $invoice_id }},
                    client_id: $('#client_id').val(),
                    invoice_date_created: $('#invoice_date_created').val(),
                    invoice_group_id: $('#invoice_group_id').val(),
                    invoice_time_created: '{{ date('H:i:s') }}',
                    invoice_password: $('#invoice_password').val(),
                    user_id: $('#user_id').val()
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

<div id="modal-create-credit-invoice" class="modal modal-lg" role="dialog" aria-labelledby="modal-create-credit-invoice"
     aria-hidden="true">
    <form class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="fi-section-title">{{ trans('create_credit_invoice') }}</h4>
        </div>
        <div class="modal-body">

            <input type="hidden" name="user_id" id="user_id" class="fi-input"
                   value="{{ $invoice->user_id }}">

            <input type="hidden" name="parent_id" id="parent_id"
                   value="{{ $invoice->invoice_id }}">

            <input type="hidden" name="client_id" id="client_id" class="hidden"
                   value="{{ $invoice->client_id }}">

            @php
                $credit_date = date_from_mysql(date('Y-m-d', time()), true);
            @endphp

            <input type="hidden" name="invoice_date_created" id="invoice_date_created"
                   value="{{ $credit_date }}">

            <div class="fi-field-wrp">
                <label for="invoice_password">{{ trans('invoice_password') }}</label>
                <input type="text" name="invoice_password" id="invoice_password" class="fi-input"
                       value="{{ get_setting('invoice_pre_password') == '' ? '' : get_setting('invoice_pre_password') }}"
                       style="margin: 0 auto;" autocomplete="off">
            </div>

            <div>
                @php
                    $credit_invoice_group = '';
                @endphp
                <select name="invoice_group_id" id="invoice_group_id" class="hidden">
                    @foreach ($invoice_groups as $invoice_group)
                        <option value="{{ $invoice_group->invoice_group_id }}"
                            {{ get_setting('default_invoice_group') == $invoice_group->invoice_group_id ? 'selected' : '' }}>
                            @if (get_setting('default_invoice_group') == $invoice_group->invoice_group_id)
                                @php
                                    $credit_invoice_group = $invoice_group->invoice_group_name;
                                @endphp
                            @endif
                            {{ $credit_invoice_group }}
                        </option>
                    @endforeach
                </select>
            </div>

            <p><strong>{{ trans('credit_invoice_details') }}</strong></p>

            <ul>
                <li>{{ trans('client') }}: {{ $invoice->client_name }}</li>
                <li>{{ trans('credit_invoice_date') }}: {{ $credit_date }}</li>
                <li>{{ trans('invoice_group') }}: {{ $credit_invoice_group }}</li>
            </ul>

            <div class="alert alert-danger no-margin">
                {{ trans('create_credit_invoice_alert') }}
            </div>

        </div>

        <div class="modal-footer">
            <div class="btn-group">
                <button class="fi-btn-success" id="create-credit-confirm" type="button">
                    <i class="fa fa-check"></i> {{ trans('confirm') }}
                </button>
                <button class="fi-btn-danger" type="button" data-dismiss="modal">
                    <i class="fa fa-times"></i> {{ trans('cancel') }}
                </button>
            </div>
        </div>

    </form>

</div>
