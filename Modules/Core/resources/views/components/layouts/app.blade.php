<!doctype html>
<html lang="en" class="fi">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="robots" content="NOINDEX,NOFOLLOW">

    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }} - {{ trans('login') }}</title>

    @vite([
        'resources/assets/core/css/style-tailwind.css',
        'resources/assets/invoiceplane/css/style-tailwind.css',
        'resources/assets/invoiceplane_blue/css/style-tailwind.css',
        'resources/assets/nord/css/nord.css',
        'resources/assets/overrides/filament-fixes.css',
        'resources/js/app.js',
    ])
    <style>[x-cloak] {
            display: none !important
        }</style>

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
<body class="fi-body fi-panel-admin">
<div class="container">
    <div id="login" class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 mx-auto py-8">
        @if (! empty($login_logo))
            <img src="{{ asset('uploads/'.$login_logo) }}" alt="logo" class="login-logo img-responsive mx-auto block mb-6" />
        @else
            <h1 class="text-2xl font-semibold text-center mb-6">{{ get_setting('custom_title', 'InvoicePlane', true) }} — {{ trans('login') }}</h1>
        @endif

        {{-- alerts / messages --}}
        <div class="mb-4">
            @includeWhen(View::exists('core::layout.alerts'), 'core::layout.alerts')
        </div>

        {{-- main slot --}}
        <div>
            @yield('content')
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
