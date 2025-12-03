<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('recurring_invoices') }}</h1>

    <div class="headerbar-item pull-right">
        {!! $invoices->links() !!}
    </div>
</div>

<div id="content" class="table-content">
    <div id="filter_results">
        @include('invoices::partial_invoices_recurring_table')
    </div>
</div>
