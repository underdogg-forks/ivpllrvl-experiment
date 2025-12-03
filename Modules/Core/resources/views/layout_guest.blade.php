<!DOCTYPE html>

<!--[if lt IE 7]>
<html class="no-js ie6 oldie" lang="{{ trans('cldr') }}"> <![endif]-->
<!--[if IE 7]>
<html class="no-js ie7 oldie" lang="{{ trans('cldr') }}"> <![endif]-->
<!--[if IE 8]>
<html class="no-js ie8 oldie" lang="{{ trans('cldr') }}"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="{{ trans('cldr') }}"> <!--<![endif]-->

<head>
    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }}</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="NOINDEX,NOFOLLOW">
    <meta name="_csrf" content="{{ $this->security->get_csrf_hash() ?>">
    <meta name="csrf_token_name" content="<?php echo config_item('csrf_token_name') }}">
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

</head>
<body class="{{ get_setting('disable_sidebar') ? 'hidden-sidebar' : '' }}">

<nav class="navbar navbar-inverse" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle"
                    data-toggle="collapse" data-target="#ip-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <?php echo trans('menu') ?> &nbsp; <i class="fa fa-bars"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="ip-navbar-collapse">
            <ul class="nav navbar-nav">
                <li>{{ anchor('guest', trans('dashboard')) }}</li>
                <li>{{ anchor('guest/quotes/index', trans('quotes')) }}</li>
                <li>{{ anchor('guest/invoices/index', trans('invoices')) }}</li>
                <li>{{ anchor('guest/payments/index', trans('payments')) }}</li>
            </ul>

            <ul class="nav navbar-nav navbar-right settings">
                <li>
                    <a href="{{ route('sessions/logout') }}"
                       class="tip icon logout" data-placement="bottom"
                       title="{{ trans('logout') }}">
                        <span class="visible-xs">&nbsp;{{ trans('logout') }}</span>
                        <i class="fa fa-power-off"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div id="main-area">

    <div class="sidebar hidden-xs{{ get_setting('disable_sidebar') == 1 ? ' hidden' : '' }}">
        <ul>
            <li>
                <a href="{{ route('guest') }}" title="{{ trans('dashboard') }}" class="tip"
                   data-placement="right">
                    <i class="fa fa-dashboard"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('guest/quotes/index') }}" title="{{ trans('quotes') }}"
                   class="tip"
                   data-placement="right">
                    <i class="fa fa-file"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('guest/invoices/index') }}" title="{{ trans('invoices') }}"
                   class="tip" data-placement="right">
                    <i class="fa fa-file-text"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('guest/payments/index') }}" title="{{ trans('payments') }}"
                   class="tip" data-placement="right">
                    <i class="fa fa-money"></i>
                </a>
            </li>
        </ul>
    </div>

    <div id="main-content">
        {{ $content }}
    </div>

</div>

<div id="modal-placeholder"></div>

{{ $this->layout->load_view('layout/includes/fullpage-loader') }}

<script defer src="<?php _core_asset('js/scripts.min.js'); ?>"></script>
@if(trans('cldr') != 'en')
    <script src="<?php _core_asset('js/locales/bootstrap-datepicker.' . trans('cldr') . '.js'); ?>"></script>
@endif
</body>
</html>
