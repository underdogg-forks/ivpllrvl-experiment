<!-- Header with Menu Navigation -->
<header class="bg-white dark:bg-gray-800 shadow-sm z-20 border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between h-16 px-4">
        <!-- Left side: Logo and toggle -->
        <div class="flex items-center">
            <button @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="ml-4 font-semibold text-xl text-blue-600 dark:text-blue-400">
                {{ get_setting('custom_title', 'InvoicePlane', true) }}
            </div>
        </div>

        <!-- Center: Main Menu Navigation -->
        <nav class="hidden lg:flex items-center space-x-1">
            <a href="{{ route('dashboard') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                <i class="fas fa-house mr-1"></i>
                {{ trans('dashboard') }}
            </a>
            <a href="{{ route('crm.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('crm*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                <i class="fas fa-users mr-1"></i>
                {{ trans('clients') }}
            </a>
            <a href="{{ route('quotes.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('quotes*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                <i class="fas fa-file-text mr-1"></i>
                {{ trans('quotes') }}
            </a>
            <a href="{{ route('invoices.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('invoices*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                <i class="fas fa-file-invoice mr-1"></i>
                {{ trans('invoices') }}
            </a>
            <a href="{{ route('payments.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('payments*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                <i class="fas fa-credit-card mr-1"></i>
                {{ trans('payments') }}
            </a>
            <a href="{{ route('products.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('products*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                <i class="fas fa-box mr-1"></i>
                {{ trans('products') }}
            </a>
        </nav>

        <!-- Right side: Search, notifications, profile -->
        <div class="flex items-center space-x-4">
            <!-- Documentation Link -->
            <a href="https://wiki.invoiceplane.com/" target="_blank" 
               class="hidden md:flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
               title="{{ trans('documentation') }}">
                <i class="fas fa-question-circle text-lg"></i>
            </a>

            <!-- Profile Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg px-2 py-1">
                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                        <span
                            class="flex h-full w-full items-center justify-center rounded-lg bg-blue-600 text-white dark:bg-blue-500 font-semibold">
                            {{ substr(session('user_name', 'U'), 0, 2) }}
                        </span>
                    </span>
                    <span class="ml-2 hidden md:block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ session('user_name', 'User') }}
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 text-gray-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" x-cloak
                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ session('user_name', 'User') }}</p>
                        @if(session('user_company'))
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ session('user_company') }}</p>
                        @endif
                    </div>

                    <a href="{{ route('users.form', ['id' => session('user_id')]) }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-user-circle mr-2"></i>
                            {{ trans('profile') }}
                        </div>
                    </a>

                    <a href="{{ route('settings') }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-cog mr-2"></i>
                            {{ trans('settings') }}
                        </div>
                    </a>

                    <div class="border-t border-gray-200 dark:border-gray-700"></div>

                    <a href="{{ route('sessions.logout') }}"
                        class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            {{ trans('logout') }}
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
