@php
    $completed = env('SETUP_COMPLETED') ? '' : ' hidden';
    $disabled = env('DISABLE_SETUP') ? ' hidden' : '';
@endphp
    <!doctype html>

<!--[if lt IE 7]>
<html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>
<html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>
<html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->

<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>{{ 'test' }}</title>

    <!-- Mobile viewport optimized: j.mp/bplateviewport -->
    <meta name="viewport" content="width=device-width">

    <link rel="icon" href="{{-- _core_asset('img/favicon.png') --}}" type="image/png">

    <!-- CSS: implied media=all -->
    <link rel="stylesheet" href="{{-- _theme_asset('css/welcome.css') --}}" type="text/css">
    <!-- end CSS-->
</head>
<body>

<div class="container">

    <div id="content">
        <div id="logo"><span>InvoicePlane</span></div>
        <p class="p-4 mb-4 text-cyan-700 dark:text-cyan-200 bg-cyan-100 dark:bg-cyan-900/50 border border-cyan-200 dark:border-cyan-800 rounded-lg text-center {{ $completed ? '' : ' hidden' }}>
            Please install InvoicePlane.<br/>
            <span class=" text-muted">Bitte installiere InvoicePlane.</span><br />
        <span class="text-muted">S'il vous plaît installer InvoicePlane</span><br />
        <span class="text-muted">Por favor, instale InvoicePlane</span><br />
        </p>

        <div class="-group inline-flex rounded-md shadow-sm -justified">
            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors {{ $completed }}>
                <i class=" fa fa-user"></i> Enter
            </a>
            <a href="{{ url('setup') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors {{ $disabled }}>
                <i class=" fa fa-cogs"></i> Setup
            </a>
            <a href="https://wiki.invoiceplane.com/"
               class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 dark:bg-cyan-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-cyan-700 dark:hover:bg-cyan-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transition-colors">
                <i class="fa fa-info-circle"></i> Get Help
            </a>
        </div>
    </div>

</div>

</body>
</html>
