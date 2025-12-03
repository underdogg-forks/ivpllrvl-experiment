<!DOCTYPE html>
<html lang="{{ trans('cldr') }}" class="h-full">

<head>
@include('core::layout.includes.head')
</head>
<body class="{{ get_setting('disable_sidebar') ? 'hidden-sidebar' : '' }} min-h-full">

<noscript>
    <div class="alert alert-danger no-margin bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 px-4 py-3 rounded">{{ trans('please_enable_js') }}</div>
</noscript>

@include('core::layout.includes.navbar')

    <div id="main-area">
@if(get_setting('disable_sidebar') != 1)
    @include('core::layout.includes.sidebar')
@endif
         <div id="main-content">
{!! $content !!}
         </div>

    </div>

    <div id="modal-placeholder"></div>

@include('core::includes.fullpage-loader')

    <script defer src="{{ asset('assets/core/js/scripts.min.js') }}"></script>
@if(trans('cldr') != 'en')
    <script src="{{ asset('assets/core/js/locales/bootstrap-datepicker.' . trans('cldr') . '.js') }}"></script>
@endif

</body>
</html>
