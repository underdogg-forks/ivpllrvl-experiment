<!doctype html>
<html lang="en" class="fi">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="robots" content="NOINDEX,NOFOLLOW">

    <title>{{ get_setting('custom_title', config('app.name'), true) }} - @yield('title')</title>

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
            const appearance = localStorage.getItem('appearance') || 'system';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (appearance === 'dark' || (appearance === 'system' && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            const setButtons = (appearance) => {
                document.querySelectorAll('button[onclick^="setAppearance"]').forEach(btn => {
                    btn.setAttribute('aria-pressed', appearance === btn.value);
                });
            };

            if (document.readyState === 'complete') setButtons(appearance);
            else document.addEventListener('DOMContentLoaded', () => setButtons(appearance));
        };
        loadDarkMode();

        window.setAppearance = function(appearance) {
            if (appearance === 'dark') localStorage.setItem('appearance', 'dark');
            else if (appearance === 'light') localStorage.setItem('appearance', 'light');
            else localStorage.removeItem('appearance');
            loadDarkMode();
        };
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased"
      x-data="{
          sidebarOpen: window.innerWidth >= 1024,
          toggleSidebar() { this.sidebarOpen = !this.sidebarOpen },
          formSubmitted: false
      }">

<div class="min-h-screen flex flex-col">

    {{-- Header --}}
    @include('core::layouts.components.header')

    <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar --}}
        @include('core::layouts.components.sidebar')

        {{-- Main Content --}}
        <main class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-900 content-transition">
            <div class="p-6">

                {{-- Session Status --}}
                @if(session('status'))
                    <div x-data="{ show: true }" x-show="show"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="mb-6 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 p-4 rounded-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500 dark:text-green-400"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                          clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 dark:text-green-200">{{ session('status') }}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <button @click="show = false"
                                        class="inline-flex rounded-md p-1.5 text-green-500 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <span class="sr-only">{{ trans('dismiss') }}</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                              clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Dynamic Content --}}
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
