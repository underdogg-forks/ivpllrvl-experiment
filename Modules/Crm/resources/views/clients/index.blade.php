@extends('core::layouts.app')

@section('content')
<div id="headerbar" class="headerbar">
    <h1 class="headerbar-title">{{ trans('dashboard') }}</h1>
</div>

<div id="content" class="space-y-4">

    @include('core::layout.alerts')

    <div class="fi-section">

        <div class="fi-section-header">{{ trans('quotes_requiring_approval') }}</div>

        <div class="fi-section-body">

@if($open_quotes)
            @include('crm::clients.partial_quotes_table', ['quotes' => $open_quotes])
@else
            <div class="alert alert-success">{{ trans('no_quotes_requiring_approval') }}</div>
@endif

        </div>
    </div>

    <div class="fi-section">
        <div class="fi-section-header">{{ trans('overdue_invoices') }}</div>
        <div class="fi-section-body">
@if($overdue_invoices)
            @include('crm::clients.partial_invoices_table', ['invoices' => $overdue_invoices])
@else
            <div class="alert alert-success">{{ trans('no_overdue_invoices') }}</div>
@endif

        </div>
    </div>

    <div class="fi-section">

        <div class="fi-section-header">{{ trans('open_invoices') }}</div>

        <div class="fi-section-body">

@if($open_invoices)
            @include('crm::clients.partial_invoices_table', ['invoices' => $open_invoices])
@else
            <div class="alert alert-success">{{ trans('no_open_invoices') }}</div>
@endif

        </div>

    </div>

</div>
@endsection
