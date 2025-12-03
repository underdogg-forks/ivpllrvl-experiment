<!doctype html>
<html lang="en" class="fi">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="robots" content="NOINDEX,NOFOLLOW">

    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }}</title>

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

    @stack('styles')
</head>
<body class="fi-body fi-panel-admin bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') === 'true' || localStorage.getItem('sidebarOpen') === null }" x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <x-core::layouts.sidebar />

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Header -->
            <x-core::layouts.header />

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900">
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
