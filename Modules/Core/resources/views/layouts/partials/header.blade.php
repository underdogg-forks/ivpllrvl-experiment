<!-- Header with Menu Navigation -->
<header class="bg-elevated border-b border-primary z-20 shadow-sm">
    <div class="flex items-center justify-between h-16 px-4">
        <!-- Left side: Logo and toggle -->
        <div class="flex items-center">
            <button @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-md text-secondary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="ml-4 font-semibold text-xl text-accent">
                {{ get_setting('custom_title', 'InvoicePlane', true) }}</div>
        </div>

        <!-- Center: Main Menu Navigation -->
        <nav class="hidden lg:flex items-center space-x-1">
            <a href="{{ route('dashboard.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('dashboard*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                <i class="fas fa-house mr-1"></i>
                {{ trans('dashboard') }}
            </a>
            <a href="{{ route('crm.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('crm*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                <i class="fas fa-users mr-1"></i>
                {{ trans('clients') }}
            </a>
            <a href="{{ route('quotes.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('quotes*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                <i class="fas fa-file-text mr-1"></i>
                {{ trans('quotes') }}
            </a>
            <a href="{{ route('invoices.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('invoices*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                <i class="fas fa-file-invoice mr-1"></i>
                {{ trans('invoices') }}
            </a>
            <a href="{{ route('payments.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('payments*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                <i class="fas fa-credit-card mr-1"></i>
                {{ trans('payments') }}
            </a>
            <a href="{{ route('products.index') }}" 
               class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('products*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                <i class="fas fa-box mr-1"></i>
                {{ trans('products') }}
            </a>
        </nav>

        <!-- Right side: Theme switcher, documentation, profile -->
        <div class="flex items-center space-x-4">
            <!-- Theme Switcher -->
            <div x-data="{ themeSwitcherOpen: false }" class="relative">
                <button @click="themeSwitcherOpen = !themeSwitcherOpen" 
                    class="p-2 rounded-md text-secondary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary"
                    title="Change theme">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </button>
                
                <div x-show="themeSwitcherOpen" @click.away="themeSwitcherOpen = false" x-cloak
                    class="absolute right-0 mt-2 w-40 bg-elevated border border-primary rounded-md shadow-lg py-1 z-50">
                    <button @click="theme = 'blue'; localStorage.setItem('color-theme', 'blue'); location.reload()"
                        class="block w-full text-left px-4 py-2 text-sm text-primary hover:bg-hover"
                        :class="{ 'bg-active text-accent': theme === 'blue' }">
                        <i class="fas fa-circle text-blue-500 mr-2"></i> Blue
                    </button>
                    <button @click="theme = 'orange'; localStorage.setItem('color-theme', 'orange'); location.reload()"
                        class="block w-full text-left px-4 py-2 text-sm text-primary hover:bg-hover"
                        :class="{ 'bg-active text-accent': theme === 'orange' }">
                        <i class="fas fa-circle text-orange-500 mr-2"></i> Orange
                    </button>
                    <button @click="theme = 'reddit'; localStorage.setItem('color-theme', 'reddit'); location.reload()"
                        class="block w-full text-left px-4 py-2 text-sm text-primary hover:bg-hover"
                        :class="{ 'bg-active text-accent': theme === 'reddit' }">
                        <i class="fas fa-circle" style="color: #FF4500" mr-2"></i> Reddit
                    </button>
                    <button @click="theme = 'nord'; localStorage.setItem('color-theme', 'nord'); document.documentElement.classList.add('dark'); location.reload()"
                        class="block w-full text-left px-4 py-2 text-sm text-primary hover:bg-hover"
                        :class="{ 'bg-active text-accent': theme === 'nord' }">
                        <i class="fas fa-circle text-cyan-400 mr-2"></i> Nord Dark
                    </button>
                </div>
            </div>

            <!-- Documentation Link -->
            <a href="https://wiki.invoiceplane.com/" target="_blank" 
               class="hidden md:flex items-center text-secondary hover:text-primary"
               title="{{ trans('documentation') }}">
                <i class="fas fa-question-circle text-lg"></i>
            </a>

            <!-- Profile Dropdown -->
            <div x-data="{ profileMenuOpen: false }" class="relative">
                <button @click="profileMenuOpen = !profileMenuOpen" class="flex items-center focus:outline-none focus:ring-2 focus:ring-primary rounded-lg px-2 py-1">
                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                        <span
                            class="flex h-full w-full items-center justify-center rounded-lg bg-primary text-white font-semibold">
                            {{ substr(session('user_name', 'U'), 0, 2) }}
                        </span>
                    </span>
                    <span class="ml-2 hidden md:block text-sm font-medium text-primary">
                        {{ session('user_name', 'User') }}
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 text-secondary" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="profileMenuOpen" @click.away="profileMenuOpen = false" x-cloak
                    class="absolute right-0 mt-2 w-56 bg-elevated rounded-md shadow-lg py-1 z-50 border border-primary">
                    <div class="px-4 py-2 border-b border-primary">
                        <p class="text-sm font-medium text-primary">{{ session('user_name', 'User') }}</p>
                        @if(session('user_company'))
                            <p class="text-xs text-secondary">{{ session('user_company') }}</p>
                        @endif
                    </div>

                    <a href="{{ route('users.form', ['user_id' => session('user_id')]) }}"
                        class="block px-4 py-2 text-sm text-primary hover:bg-hover">
                        <div class="flex items-center">
                            <i class="fas fa-user-circle mr-2"></i>
                            {{ trans('profile') }}
                        </div>
                    </a>

                    <a href="{{ route('settings.index') }}"
                        class="block px-4 py-2 text-sm text-primary hover:bg-hover">
                        <div class="flex items-center">
                            <i class="fas fa-cog mr-2"></i>
                            {{ trans('settings') }}
                        </div>
                    </a>

                    <div class="border-t border-primary"></div>

                    <a href="{{ route('sessions.logout') }}"
                        class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-hover">
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
