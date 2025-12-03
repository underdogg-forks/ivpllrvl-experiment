<!DOCTYPE html>
<html lang="{{ trans('cldr') }}" class="h-full">

<head>
    <title>InvoicePlane Setup</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <meta name="robots" content="NOINDEX,NOFOLLOW">

    <link rel="icon" href="{{ asset('assets/core/img/favicon.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('assets/invoiceplane/css/welcome.css') }}" type="text/css">

    <script src="{{ asset('assets/core/js/dependencies.min.js') }}"></script>

</head>
<body class="min-h-full">

<noscript>
    <div class="alert alert-danger no-margin bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 px-4 py-3 rounded">{{ trans('please_enable_js') }}</div>
</noscript>

{!! $content !!}

<script>$('.simple-select').select2();</script>

</body>
</html>
