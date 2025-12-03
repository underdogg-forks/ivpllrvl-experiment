<!doctype html>
<html lang="en" class="h-full">

<head>
    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }} - {{ trans('password_reset') }}</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <meta name="robots" content="NOINDEX,NOFOLLOW">

    <link rel="icon" href="{{ asset('assets/core/img/favicon.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('assets/invoiceplane/css/style.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/core/css/custom.css') }}" type="text/css">
</head>

<body class="min-h-full">

<noscript>
    <div class="alert alert-danger no-margin bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 px-4 py-3 rounded">{{ trans('please_enable_js') }}</div>
</noscript>

<br>

<div class="container mx-auto px-4">

    <div id="password_reset"
         class="fi-section fi-section-body max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-6">

        <div class="row">@include('core::layout.alerts')</div>

        <h3 class="text-2xl font-semibold mb-4">{{ trans('password_reset') }}</h3>

        <br/>

        <p class="mb-4">{{ trans('password_reset_info') }}</p>

        <form method="post" action="{{ route('sessions.passwordreset') }}">

            @csrf

            <div class="fi-field-wrp mb-4">
                <label for="email" class="hidden">{{ trans('email') }}</label>
                <input type="text" name="email" id="email" class="fi-input w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
                       placeholder="{{ trans('email') }}" required autofocus>
            </div>

            <input type="hidden" name="btn_reset" value="true">

            <button type="submit" class="fi-btn-danger inline-flex items-center gap-2 px-4 py-2 bg-red-600 dark:bg-red-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 dark:hover:bg-red-600">
                <i class="fa fa-key fa-margin"></i> {{ trans('reset_password') }}
            </button>

        </form>

    </div>

</div>

</body>
</html>
