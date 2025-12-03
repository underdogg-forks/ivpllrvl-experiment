<!DOCTYPE html>
<html lang="{{ trans('cldr') }}" class="fi">

<head>
    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }}</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="NOINDEX,NOFOLLOW">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf_token_name" content="{{ config('app.csrf_token_name') }}">
    <meta name="csrf_cookie_name" content="{{ config('app.csrf_cookie_name') }}">
    <meta name="legacy_calculation" content="{{ (int) config('app.legacy_calculation') }}">

    <link rel="icon" href="{{ asset('assets/core/img/favicon.png') }}" type="image/png">

    @vite([
        'resources/assets/core/css/style-tailwind.css',
        'resources/assets/invoiceplane/css/style-tailwind.css',
        'resources/assets/invoiceplane_blue/css/style-tailwind.css',
        'resources/assets/nord/css/nord.css',
        'resources/assets/overrides/filament-fixes.css',
        'resources/js/app.js',
    ])

@if(get_setting('monospace_amounts') == 1)
    <style>
        .amount, .currency { font-family: monospace; }
    </style>
@endif

    <script>
        const loadDarkMode = () => {
            const theme = localStorage.getItem('theme') ?? 'system';
            if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        };
        loadDarkMode();
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">

<nav class="navbar navbar-default bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-4">

        <div class="flex justify-between items-center h-16">
            <div class="navbar-brand text-lg font-semibold">
                {{ trans('online_payment_for_invoice') }} #{{ $invoice->invoice_number }}
            </div>

            <ul class="nav navbar-nav flex gap-4">
                <li>
                    <a target="_blank" href="{{ route('guest.generate-pdf', $invoice->invoice_url_key) }}" class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                        <i class="fa fa-print"></i> {{ trans('download_pdf') }}
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav>

<div class="container mx-auto px-4 py-8">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-8 md:col-start-3">

            <br>
@php
            $logo = invoice_logo();
@endphp
@if($logo)
            {!! $logo !!}<br><br>
@endif

            <div class="form-group mb-4">
                @include('core::layout.alerts', ['without_margin' => true])
            </div>

            <div class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">

                <div class="panel-body p-4">

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-7">
                            <h4 class="text-lg font-semibold mb-4">
                                {{ htmlspecialchars(format_client($invoice)) }}
                            </h4>
                            <div class="client-address">
                                @include('crm::clients.partial_client_address', ['client' => $invoice])
                            </div>
                        </div>

                        <div class="md:col-span-5">
                            <div class="block md:hidden"><br></div>
                            <div class="overflow-x-auto">
                                <table class="table table-bordered table-condensed no-margin w-full">
                                    <tbody>
                                        <tr>
                                            <td class="px-4 py-2">{{ trans('invoice_date') }}</td>
                                            <td class="px-4 py-2 text-right">{{ date_from_mysql($invoice->invoice_date_created) }}</td>
                                        </tr>
                                        <tr class="{{ $is_overdue ? 'overdue bg-red-50 dark:bg-red-900/20' : '' }}">
                                            <td class="px-4 py-2">{{ trans('due_date') }}</td>
                                            <td class="px-4 py-2 text-right">
                                                {{ date_from_mysql($invoice->invoice_date_due) }}
                                            </td>
                                        </tr>
                                        <tr class="{{ $is_overdue ? 'overdue bg-red-50 dark:bg-red-900/20' : '' }}">
                                            <td class="px-4 py-2">{{ trans('total') }}</td>
                                            <td class="px-4 py-2 text-right">{{ format_currency($invoice->invoice_total) }}</td>
                                        </tr>
                                        <tr class="{{ $is_overdue ? 'overdue bg-red-50 dark:bg-red-900/20' : '' }}">
                                            <td class="px-4 py-2">{{ trans('balance') }}</td>
                                            <td class="px-4 py-2 text-right">{{ format_currency($invoice->invoice_balance) }}</td>
                                        </tr>
@if($payment_method)
                                        <tr>
                                            <td class="px-4 py-2">{{ trans('payment_method') . ': ' }}</td>
                                            <td class="px-4 py-2 text-right">{{ htmlspecialchars($payment_method->payment_method_name) }}</td>
                                        </tr>
@endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
@if(!empty($invoice->invoice_terms))
                        <div class="md:col-span-12 text-muted">
                            <br>
                            <h4 class="text-lg font-semibold mb-2">{{ trans('terms') }}</h4>
                            <div class="text-gray-600 dark:text-gray-400">{!! htmlspecialchars(nl2br($invoice->invoice_terms)) !!}</div>
                        </div>
@endif
                    </div>

                </div>
            </div>
@if($payment_provider == null && !$disable_form)
                <div class="my-4">
                    <p>{{ trans('select_payment_method') }}</p>
                </div>
                <ul class="list-group space-y-2">
@foreach($gateways as $gateway)
                    <a class="list-group-item list-group-item-action block px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700" href="{{ route('guest.form', [$invoice->invoice_url_key, $gateway]) }}">{{ ucwords(str_replace('_', ' ', $gateway)) }}</a>
@endforeach
                </ul>
@endif
        </div>
    </div>

</div>

<div id="modal-placeholder"></div>

@include('core::includes.fullpage-loader')

@stack('scripts')
</body>
</html>
