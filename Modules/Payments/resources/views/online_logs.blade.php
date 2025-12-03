<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('payment_logs') }}</h1>

    <div class="headerbar-item pull-right">
        {!! $payment_logs->links() !!}
    </div>

</div>

<div id="content" class="table-content">

    @include('core::alerts')

    <div id="filter_results">
        @include('payments::partial_online_logs_table')
    </div>

</div>
