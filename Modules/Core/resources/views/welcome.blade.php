@php
$completed = env_bool('SETUP_COMPLETED') ? '' : ' hidden';
$disabled  = env_bool('DISABLE_SETUP') ? ' hidden' : '';
@endphp
<!doctype html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }}</title>
    <meta name="viewport" content="width=device-width">
    <link rel="icon" href="{{ asset('assets/core/img/favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/invoiceplane/css/welcome.css') }}" type="text/css">
</head>
<body class="min-h-full">

<div class="container mx-auto px-4">

    <div id="content" class="text-center py-8">
        <div id="logo" class="text-6xl font-bold mb-6"><span>InvoicePlane</span></div>
        <p class="alert alert-info bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-4 py-3 rounded mb-4{{ $completed ? '' : ' hidden' }}">
            Please install InvoicePlane.<br/>
            <span class="text-muted">Bitte installiere InvoicePlane.</span><br/>
            <span class="text-muted">S'il vous plaît installer InvoicePlane</span><br/>
            <span class="text-muted">Por favor, instale InvoicePlane</span><br/>
        </p>

        <div class="btn-group btn-group-justified flex gap-2 justify-center">
            <a href="{{ route('dashboard') }}" class="btn btn-default inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50{{ $completed }}">
                <i class="fa fa-user"></i> Enter
            </a>
            <a href="{{ route('setup.index') }}" class="btn btn-success inline-flex items-center gap-2 px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700{{ $disabled }}">
                <i class="fa fa-cogs"></i> Setup
            </a>
            <a href="https://wiki.invoiceplane.com/" class="btn btn-info inline-flex items-center gap-2 px-4 py-2 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                <i class="fa fa-info-circle"></i> Get Help
            </a>
        </div>
    </div>

</div>

</body>
</html>
