<div id="headerbar">

    <h1 class="headerbar-title">{{ trans('invoices') }}</h1>

    <div class="headerbar-item pull-right">
        {!! $invoices->links() !!}
    </div>

    <div class="headerbar-item pull-right">
        <div class="btn-group btn-group-sm index-options">
            <a href="{{ route('guest.status', 'open') }}"
               class="btn {{ $status == 'open' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('open') }}
            </a>
            <a href="{{ route('guest.status', 'overdue') }}"
               class="btn {{ $status == 'overdue' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('overdue') }}
            </a>
            <a href="{{ route('guest.status', 'paid') }}"
               class="btn  {{ $status == 'paid' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('paid') }}
            </a>
            <a href="{{ route('guest.status', 'all') }}"
               class="btn  {{ $status == 'all' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('all') }}
            </a>
        </div>
    </div>

</div>

<div id="content" class="table-content">

    @include('core::alerts')

    <div id="filter_results">

        @include('crm::partial_invoices_table')

    </div>

</div>
