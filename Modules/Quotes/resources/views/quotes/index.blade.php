@extends('core::layouts.app')

@section('content')
    <div id="headerbar" class="headerbar">

        <h1 class="headerbar-title">@lang('quotes')</h1>

        <div class="headerbar-actions">
            <button type="button"
                    class="btn-submenu-toggle"
                    data-toggle="collapse" data-target="#ip-submenu-collapse">
                <i class="fa fa-bars"></i> @lang('submenu')
            </button>

            <a class="btn-create"
               href="{{ route('quotes.ajax.modal.create') }}">
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
                       class="{{ request('status', 'all') === $s ? 'btn-status-active' : 'btn-status-inactive' }}">
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
                           class="{{ request('status', 'all') === $s ? 'btn-status-active' : 'btn-status-inactive' }}">
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
