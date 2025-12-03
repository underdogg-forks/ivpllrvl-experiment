<div id="headerbar">

    <h1 class="headerbar-title">{{ trans('quotes') }}</h1>

    <div class="headerbar-item pull-right">
        {!! $quotes->links() !!}
    </div>

    <div class="headerbar-item pull-right">
        <div class="btn-group btn-group-sm index-options">
            <a href="{{ route('guest.quotes.status', 'open') }}"
               class="btn {{ $status == 'open' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('open') }}
            </a>
            <a href="{{ route('guest.quotes.status', 'approved') }}"
               class="btn  {{ $status == 'approved' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('approved') }}
            </a>
            <a href="{{ route('guest.quotes.status', 'rejected') }}"
               class="btn  {{ $status == 'rejected' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('rejected') }}
            </a>
            <a href="{{ route('guest.quotes.status', 'viewed') }}"
               class="btn  {{ $status == 'viewed' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('viewed') }}
            </a>
            <a href="{{ route('guest.quotes.status', 'all') }}"
               class="btn  {{ $status == 'all' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('all') }}
            </a>
        </div>
    </div>

</div>

<div id="content" class="table-content">

    @include('core::alerts')

    <div id="filter_results">

        @include('crm::partial_quotes_table')

    </div>

</div>
