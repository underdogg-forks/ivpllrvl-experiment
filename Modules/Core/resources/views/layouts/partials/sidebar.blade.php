<!-- Sidebar Navigation -->
<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
    class="bg-elevated border-r border-primary transition-all duration-300 ease-in-out overflow-hidden">
    <!-- Sidebar Content -->
    <div class="h-full flex flex-col">
        <!-- Sidebar Menu -->
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-2">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('dashboard*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-house w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('dashboard') }}</span>
                    </a>
                </li>

                <!-- Clients -->
                <li>
                    <a href="{{ route('crm.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('crm*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-users w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('clients') }}</span>
                    </a>
                </li>

                <!-- Quotes -->
                <li>
                    <a href="{{ route('quotes.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('quotes*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-file-text w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('quotes') }}</span>
                    </a>
                </li>

                <!-- Invoices -->
                <li>
                    <a href="{{ route('invoices.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('invoices*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-file-invoice w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('invoices') }}</span>
                    </a>
                </li>

                <!-- Payments -->
                <li>
                    <a href="{{ route('payments.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('payments*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-credit-card w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('payments') }}</span>
                    </a>
                </li>

                <!-- Products -->
                <li>
                    <a href="{{ route('products.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('products*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-box w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('products') }}</span>
                    </a>
                </li>

                <!-- Projects -->
                @if(get_setting('projects_enabled') == 1)
                <li>
                    <a href="{{ route('projects.index') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('projects*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-tasks w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('projects') }}</span>
                    </a>
                </li>
                @endif

                <!-- Divider -->
                <li class="pt-4 pb-2">
                    <div class="border-t border-primary"></div>
                </li>

                <!-- Settings & Administration -->
                <li>
                    <a href="{{ route('settings') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('settings*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-cog w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('system_settings') }}</span>
                    </a>
                </li>

                <!-- Reports -->
                <li>
                    <a href="{{ route('reports.invoice_aging') }}" 
                       class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reports*') ? 'bg-active text-accent' : 'text-primary hover:bg-hover' }}">
                        <i class="fas fa-chart-bar w-5 text-center" :class="{ 'mr-3': sidebarOpen }"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ trans('reports') }}</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
