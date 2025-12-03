<!doctype html>
<html lang="en" class="fi" x-data="{ theme: localStorage.getItem('color-theme') || 'blue' }" :data-theme="theme">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="robots" content="NOINDEX,NOFOLLOW">

    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }}</title>

    {{-- Poppins Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite([
        'resources/assets/core/css/style-tailwind.css',
        'resources/assets/invoiceplane/css/style-tailwind.css',
        'resources/assets/invoiceplane_blue/css/style-tailwind.css',
        'resources/assets/nord/css/nord.css',
        'resources/assets/orange/css/orange.css',
        'resources/assets/reddit/css/reddit.css',
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

    @stack('styles')
</head>
<body class="fi-body fi-fi-section-admin bg-base" x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') === 'true' || localStorage.getItem('sidebarOpen') === null }" x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('core::layouts.partials.sidebar')

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Header -->
            @include('core::layouts.partials.header')

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-base">
                <div class="container mx-auto px-4 py-6">
                    {{-- Alerts / Messages --}}
                    @includeWhen(View::exists('core::layout.alerts'), 'core::layout.alerts')

                    {{-- Main Content --}}
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Placeholder -->
    <div id="modal-placeholder"></div>

    @stack('scripts')
</body>
</html>
