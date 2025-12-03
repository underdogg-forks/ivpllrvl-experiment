@extends('core::layouts.app')

@section('content')
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <h1 class="headerbar-title text-xl font-bold">{{ trans('dashboard') }}</h1>
</div>

<div id="content" class="space-y-4">

    @include('core::layout.alerts')

    <div class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">

        <div class="panel-heading px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">{{ trans('quotes_requiring_approval') }}</div>

        <div class="panel-body">

@if($open_quotes)
            @include('crm::clients.partial_quotes_table', ['quotes' => $open_quotes])
@else
            <div class="alert text-success bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-4 py-3 rounded">{{ trans('no_quotes_requiring_approval') }}</div>
@endif

        </div>
    </div>

    <div class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="panel-heading px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">{{ trans('overdue_invoices') }}</div>
        <div class="panel-body">
@if($overdue_invoices)
            @include('crm::clients.partial_invoices_table', ['invoices' => $overdue_invoices])
@else
            <div class="alert text-success bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-4 py-3 rounded">{{ trans('no_overdue_invoices') }}</div>
@endif

        </div>
    </div>

    <div class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">

        <div class="panel-heading px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">{{ trans('open_invoices') }}</div>

        <div class="panel-body">

@if($open_invoices)
            @include('crm::clients.partial_invoices_table', ['invoices' => $open_invoices])
@else
            <div class="alert text-success bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-4 py-3 rounded">{{ trans('no_open_invoices') }}</div>
@endif

        </div>

    </div>

</div>
@endsection
