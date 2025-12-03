<div id="headerbar">

    <h1 class="headerbar-title">{{ trans('invoices') }}</h1>

    <div class="headerbar-item pull-right">
        <a class="create-invoice btn btn-sm btn-primary" href="#">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>

    <div class="headerbar-item pull-right">
        {!! $invoices->links() !!}
    </div>

    <div class="headerbar-item pull-right">
        <div class="btn-group btn-group-sm index-options">
            <a href="{{ route('invoices.client', [$client_id, 'open']) }}"
               class="btn {{ $status == 'open' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('open') }}
            </a>
            <a href="{{ route('invoices.client', [$client_id, 'closed']) }}"
               class="btn  {{ $status == 'closed' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('closed') }}
            </a>
            <a href="{{ route('invoices.client', [$client_id, 'overdue']) }}"
               class="btn  {{ $status == 'overdue' ? 'btn-primary' : 'btn-default' }}">
                {{ trans('overdue') }}
            </a>
        </div>
    </div>

</div>

<div id="content" class="table-content">

    @include('invoices::partial_invoice_table', ['invoices' => $invoices])

</div>
