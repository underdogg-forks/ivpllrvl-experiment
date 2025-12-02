@extends('core::components.layouts.app')

@section('content')
    <div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">

        <h1 class="headerbar-title text-xl font-bold">@lang('quotes')</h1>

        <div class="flex items-center space-x-2">
            <button type="button"
                    class="submenu-toggle lg:hidden inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    data-toggle="collapse" data-target="#ip-submenu-collapse">
                <i class="fa fa-bars"></i> @lang('submenu')
            </button>

            <a class="create-quote inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
               href="{{ route('quotes.modal.create') }}">
                <i class="fa fa-plus"></i> @lang('new')
            </a>
        </div>

        <div class="headerbar-item hidden lg:block ml-auto">
            {{ $quotes->links() }}
        </div>

        <div class="headerbar-item hidden lg:block ml-4">
            <div class="inline-flex rounded-md shadow-sm index-options [&>*]:px-3 [&>*]:py-1.5 [&>*]:text-sm">
                @foreach (['all','draft','sent','viewed','approved','rejected','canceled'] as $s)
                    <a href="{{ route('quotes.status.'. $s) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors
                   {{ request('status', 'all') === $s ? 'bg-blue-600 dark:bg-blue-500 text-white border-transparent hover:bg-blue-700 dark:hover:bg-blue-600 focus:ring-blue-500' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-blue-500' }}">
                        @lang($s)
                    </a>
                @endforeach
            </div>
        </div>

    </div>

    <div id="submenu">
        <div class="collapse" id="ip-submenu-collapse">

            <div class="submenu-row mb-2">
                {{ $quotes->links() }}
            </div>

            <div class="submenu-row">
                <div class="inline-flex rounded-md shadow-sm index-options [&>*]:px-3 [&>*]:py-1.5 [&>*]:text-sm">
                    @foreach (['all','draft','sent','viewed','approved','rejected','canceled'] as $s)
                        <a href="{{ route('quotes.status.'.$s) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors
                       {{ request('status', 'all') === $s ? 'bg-blue-600 dark:bg-blue-500 text-white border-transparent hover:bg-blue-700 dark:hover:bg-blue-600 focus:ring-blue-500' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-blue-500' }}">
                            @lang($s)
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <div id="content" class="table-content mt-4">
        <div id="filter_results">
            @include('quotes::quotes.partial_quote_table')
        </div>
    </div>
@endsection
