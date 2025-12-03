@extends('core::layouts.app')

@section('content')
<div id="headerbar">

    <h1 class="headerbar-title">{{ trans('invoices') }}</h1>

    <div class="headerbar-item pull-right">
        <button type="button" class="fi-btn-secondary fi-size-sm submenu-toggle hidden-lg"
                data-toggle="collapse" data-target="#ip-submenu-collapse">
            <i class="fa fa-bars"></i> {{ trans('submenu') }}
        </button>
        <a class="create-invoice btn fi-size-sm fi-btn-primary" href="#">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>

    <div class="headerbar-item pull-right visible-lg">
        {!! $invoices->links() !!}
    </div>

    <div class="headerbar-item pull-right visible-lg">
        <div class="btn-group btn-group-sm index-options">
            <a href="{{ route('invoices.status.all') }}"
               class="btn {{ $status == 'all' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                {{ trans('all') }}
            </a>
            <a href="{{ route('invoices.status.draft') }}"
               class="btn {{ $status == 'draft' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                {{ trans('draft') }}
            </a>
            <a href="{{ route('invoices.status.sent') }}"
               class="btn {{ $status == 'sent' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                {{ trans('sent') }}
            </a>
            <a href="{{ route('invoices.status.viewed') }}"
               class="btn {{ $status == 'viewed' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                {{ trans('viewed') }}
            </a>
            <a href="{{ route('invoices.status.paid') }}"
               class="btn {{ $status == 'paid' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                {{ trans('paid') }}
            </a>
            <a href="{{ route('invoices.status.overdue') }}"
               class="btn {{ $status == 'overdue' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                {{ trans('overdue') }}
            </a>
        </div>
    </div>

</div>

<div id="submenu">
    <div class="collapse clearfix" id="ip-submenu-collapse">

        <div class="submenu-row">
            {!! $invoices->links() !!}
        </div>

        <div class="submenu-row">
            <div class="btn-group btn-group-sm index-options">
                <a href="{{ route('invoices.status.all') }}"
                   class="btn {{ $status == 'all' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                    {{ trans('all') }}
                </a>
                <a href="{{ route('invoices.status.draft') }}"
                   class="btn  {{ $status == 'draft' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                    {{ trans('draft') }}
                </a>
                <a href="{{ route('invoices.status.sent') }}"
                   class="btn  {{ $status == 'sent' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                    {{ trans('sent') }}
                </a>
                <a href="{{ route('invoices.status.viewed') }}"
                   class="btn  {{ $status == 'viewed' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                    {{ trans('viewed') }}
                </a>
                <a href="{{ route('invoices.status.paid') }}"
                   class="btn  {{ $status == 'paid' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                    {{ trans('paid') }}
                </a>
                <a href="{{ route('invoices.status.overdue') }}"
                   class="btn  {{ $status == 'overdue' ? 'fi-btn-primary' : 'fi-btn-secondary' }}">
                    {{ trans('overdue') }}
                </a>
            </div>
        </div>

    </div>
</div>

<div id="content" class="table-content">
    <div id="filter_results">
        @include('invoices::partial_invoice_table')
    </div>
</div>
@endsection
