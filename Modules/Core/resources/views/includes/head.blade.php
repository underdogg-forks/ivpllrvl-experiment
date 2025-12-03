<title>{{ get_setting('custom_title', 'InvoicePlane', true) }}</title>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="NOINDEX,NOFOLLOW">
<meta name="csrf_token_name" content="{{ config_item('csrf_token_name') }}">
<meta name="csrf_cookie_name" content="{{ config_item('csrf_cookie_name') }}">
<meta name="legacy_calculation" content="{{ (int) (config_item('legacy_calculation')) }}">

<link rel="icon" href="<?php _core_asset('img/favicon.png'); ?>" type="image/png">

<link rel="stylesheet" href="<?php _theme_asset('css/style.css'); ?>" type="text/css">
<link rel="stylesheet" href="<?php _core_asset('css/custom.css'); ?>" type="text/css">

@if(get_setting('monospace_amounts') == 1)
    <link rel="stylesheet" href="<?php _theme_asset('css/monospace.css'); ?>" type="text/css">
@endif
<!--[if lt IE 9]>
<script src="<?php _core_asset('js/legacy.min.js'); ?>"></script>
<![endif]-->

<script src="<?php _core_asset('js/dependencies.min.js'); ?>"></script>
@if(trans('cldr') != 'en')
    <script src="<?php _core_asset('js/locales/select2/' . trans('cldr') . '.js'); ?>"></script>
@endif
<script>
    Dropzone.autoDiscover = false;

    @if(trans('cldr') != 'en')
    $.fn.select2.defaults.set('language', '{{ trans('cldr') }}');
    @endif
    $(function () {
        $('.nav-tabs').tab();
        $('.tip').tooltip();

        $('body').on('focus', '.datepicker', function () {
            $(this).datepicker({
                autoclose: true,
                format: '{{ date_format_datepicker() }}',
                language: '{{ trans('cldr') }}',
                weekStart: '{{ get_setting('first_day_of_week') }}',
                todayHighlight: true,
                todayBtn: 'linked'
            });
        });

        $(document).on('click', '.create-invoice', function () {
            $('#modal-placeholder').load("{{ route('invoices.modal-create-invoice') }}");
        });

        $(document).on('click', '.create-quote', function () {
            $('#modal-placeholder').load("{{ route('quotes.ajax.modal.create') }}");
        });

        $(document).on('click', '#btn_quote_to_invoice', function () {
            var quote_id = $(this).data('quote-id');
            $('#modal-placeholder').load("{{ route('quotes.ajax.quote_to_invoice') }}/" + quote_id);
        });

        $(document).on('click', '#btn_copy_invoice', function () {
            var invoice_id = $(this).data('invoice-id');
            var client_id = $(this).data('client-id');
            $('#modal-placeholder').load("{{ route('invoices.modal-copy-invoice') }}", {
                invoice_id: invoice_id,
                client_id: client_id
            });
        });

        $(document).on('click', '#btn_create_credit', function () {
            var invoice_id = $(this).data('invoice-id');
            $('#modal-placeholder').load("{{ route('invoices.modal-create-credit') }}", {invoice_id: invoice_id});
        });

        $(document).on('click', '#btn_copy_quote', function () {
            var quote_id = $(this).data('quote-id');
            var client_id = $(this).data('client-id');
            $('#modal-placeholder').load("{{ route('quotes.ajax.modal.copy') }}", {
                quote_id: quote_id,
                client_id: client_id
            });
        });

        $(document).on('click', '.client-create-invoice', function () {
            var client_id = $(this).data('client-id');
            $('#modal-placeholder').load("{{ route('invoices.modal-create-invoice') }}", {client_id: client_id});
        });

        $(document).on('click', '.client-create-quote', function () {
            var client_id = $(this).data('client-id');
            $('#modal-placeholder').load("{{ route('quotes.ajax.modal.create') }}", {client_id: client_id});
        });

        $(document).on('click', '.invoice-add-payment', function () {
            var invoice_id = $(this).data('invoice-id');
            var invoice_balance = $(this).data('invoice-balance');
            var invoice_payment_method = $(this).data('invoice-payment-method');
            var payment_cf_exist =  $(this).data('payment-cf-exist');
            $('#modal-placeholder').load("{{ route('payments.modal-add-payment') }}", {
                invoice_id: invoice_id,
                invoice_balance: invoice_balance,
                invoice_payment_method: invoice_payment_method,
                payment_cf_exist: payment_cf_exist
            });
        });

    });
</script>
