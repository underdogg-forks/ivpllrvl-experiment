<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
    class="bg-sidebar text-sidebar-foreground border-r border-gray-200 dark:border-gray-700 sidebar-transition overflow-hidden">
    <!-- Sidebar Content -->
    <div class="h-full flex flex-col">
        <!-- Sidebar Menu -->
        <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
            <ul class="space-y-1 px-2">
                <!-- Dashboard -->
                <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon='fas-house'
                    :active="request()->routeIs('dashboard*')">{{ trans('dashboard') }}</x-layouts.sidebar-link>

                <!-- Clients -->
                <x-layouts.sidebar-link href="{{ route('crm.index') }}" icon='fas-users'
                    :active="request()->routeIs('crm*')">{{ trans('clients') }}</x-layouts.sidebar-link>

                <!-- Quotes -->
                <x-layouts.sidebar-link href="{{ route('quotes.index') }}" icon='fas-file-text'
                    :active="request()->routeIs('quotes*')">{{ trans('quotes') }}</x-layouts.sidebar-link>

                <!-- Invoices -->
                <x-layouts.sidebar-link href="{{ route('invoices.index') }}" icon='fas-file-invoice'
                    :active="request()->routeIs('invoices*')">{{ trans('invoices') }}</x-layouts.sidebar-link>

                <!-- Payments -->
                <x-layouts.sidebar-link href="{{ route('payments.index') }}" icon='fas-credit-card'
                    :active="request()->routeIs('payments*')">{{ trans('payments') }}</x-layouts.sidebar-link>

                <!-- Products -->
                <x-layouts.sidebar-link href="{{ route('products.index') }}" icon='fas-box'
                    :active="request()->routeIs('products*')">{{ trans('products') }}</x-layouts.sidebar-link>

                <!-- Projects -->
                <x-layouts.sidebar-link href="{{ route('projects.index') }}" icon='fas-tasks'
                    :active="request()->routeIs('projects*')">{{ trans('projects') }}</x-layouts.sidebar-link>
            </ul>
        </nav>
    </div>
</aside>
