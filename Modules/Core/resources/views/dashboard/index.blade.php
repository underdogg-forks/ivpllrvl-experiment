@extends('core::layouts.app')

@section('content')
<div id="content">
    @include('core::alerts')

    <div class="row{{ (get_setting('disable_quickactions') == 1) ? ' hidden' : '' }}">
        <div class="col-xs-12">

            <div id="panel-quick-actions" class="panel panel-default quick-actions">

                <div class="panel-heading">
                    <b>{{ trans('quick_actions') }}</b>
                </div>

                <div class="btn-group btn-group-justified no-margin">
                    <a href="{{ route('clients.form') }}" class="btn btn-default">
                        <i class="fa fa-user fa-margin"></i>
                        <span class="hidden-xs">{{ trans('add_client') }}</span>
                    </a>
                    <a href="javascript:void(0)" class="create-quote btn btn-default">
                        <i class="fa fa-file fa-margin"></i>
                        <span class="hidden-xs">{{ trans('create_quote') }}</span>
                    </a>
                    <a href="javascript:void(0)" class="create-invoice btn btn-default">
                        <i class="fa fa-file-text fa-margin"></i>
                        <span class="hidden-xs">{{ trans('create_invoice') }}</span>
                    </a>
                    <a href="{{ route('payments.form') }}" class="btn btn-default">
                        <i class="fa fa-credit-card fa-margin"></i>
                        <span class="hidden-xs">{{ trans('enter_payment') }}</span>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-6">

            <div id="panel-quote-overview" class="panel panel-default overview">

                <div class="panel-heading">
                    <b><i class="fa fa-bar-chart fa-margin"></i> {{ trans('quote_overview') }}</b>
                    <span class="pull-right text-muted">{{ lang($quote_status_period) }}</span>
                </div>

                <table class="table table-hover table-bordered table-condensed no-margin">
@foreach($quote_status_totals as $total)
                    <tr>
                        <td>
                            <a href="{{ route($total['href']) }}">
                                {{ $total['label'] }}
                            </a>
                        </td>
                        <td class="amount">
                            <span class="{{ $total['class'] }}">
                                {{ format_currency($total['sum_total']) }}
                            </span>
                        </td>
                    </tr>
@endif
                </table>
            </div>

        </div>
        <div class="col-xs-12 col-md-6">

            <div id="panel-invoice-overview" class="panel panel-default overview">

                <div class="panel-heading">
                    <b><i class="fa fa-bar-chart fa-margin"></i> {{ trans('invoice_overview') }}</b>
                    <span class="pull-right text-muted">{{ lang($invoice_status_period) }}</span>
                </div>

                <table class="table table-hover table-bordered table-condensed no-margin">
@foreach($invoice_status_totals as $total)
                    <tr>
                        <td>
                            <a href="{{ route($total['href']) }}">
                                {{ $total['label'] }}
                            </a>
                        </td>
                        <td class="amount">
                            <span class="{{ $total['class'] }}">
                                {{ format_currency($total['sum_total']) }}
                            </span>
                        </td>
                    </tr>
@endif
                </table>
            </div>
@if(empty($overdue_invoices))
            <div class="panel panel-default panel-heading">
                <span class="text-muted">{{ trans('no_overdue_invoices') }}</span>
            </div>
@else
            @php
                $overdue_invoices_total = 0;
                foreach ($overdue_invoices as $invoice) {
                    $overdue_invoices_total += $invoice->invoice_balance;
                }
            @endphp
            <div class="panel panel-danger panel-heading">
                <a href="{{ route('invoices.status.overdue') }}" class="text-danger">
                    <i class="fa fa-external-link"></i> {{ trans('overdue_invoices') }}
                </a>
                <span class="pull-right text-danger">
                    {{ format_currency($overdue_invoices_total) }}
                </span>
            </div>
@endif
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-6">

            <div id="panel-recent-quotes" class="panel panel-default">

                <div class="panel-heading">
                    <b><i class="fa fa-history fa-margin"></i> {{ trans('recent_quotes') }}</b>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-condensed no-margin">
                        <thead>
                        <tr>
                            <th>{{ trans('status') }}</th>
                            <th style="min-width: 15%;">{{ trans('date') }}</th>
                            <th style="min-width: 15%;">{{ trans('quote') }}</th>
                            <th style="min-width: 35%;">{{ trans('client') }}</th>
                            <th class="amount">{{ trans('balance') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
@foreach($quotes as $quote)
                            <tr>
                                <td>
                                <span class="label
                                {{ $quote_statuses[$quote->quote_status_id]['class'] }}">
                                    {{ $quote_statuses[$quote->quote_status_id]['label'] }}
                                </span>
                                </td>
                                <td>
                                    {{ date_from_mysql($quote->quote_date_created) }}
                                </td>
                                <td>
                                    <a href="{{ route(\'quotes.view\', $quote->quote_id) }}">$quote->quote_number ? $quote->quote_number : $quote->quote_id</a>
                                </td>
                                <td>
                                    <a href="{{ route(\'clients.view\', $quote->client_id) }}">{{ htmlsc(format_client($quote)) }}</a>
                                </td>
                                <td class="amount">
                                    {{ format_currency($quote->quote_total) }}
                                </td>
                                <td style="text-align: center;">
                                    <a href="{{ route('quotes.generate_pdf', $quote->quote_id) }}"
                                       target="_blank" title="{{ trans('download_pdf') }}">
                                        <i class="fa fa-file-pdf-o"></i>
                                    </a>
                                </td>
                            </tr>
@endif
                        <tr>
                            <td colspan="6" class="text-right small">
                                {{ anchor('quotes/status/all', trans('view_all')) }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="col-xs-12 col-md-6">

            <div id="panel-recent-invoices" class="panel panel-default">

                <div class="panel-heading">
                    <b><i class="fa fa-history fa-margin"></i> {{ trans('recent_invoices') }}</b>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-condensed no-margin">
                        <thead>
                        <tr>
                            <th>{{ trans('status') }}</th>
                            <th style="min-width: 15%;">{{ trans('due_date') }}</th>
                            <th style="min-width: 15%;">{{ trans('invoice') }}</th>
                            <th style="min-width: 35%;">{{ trans('client') }}</th>
                            <th class="amount">{{ trans('balance') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

@foreach($invoices as $invoice)
    @php
        if (config('app.disable_read_only') == true) {
            $invoice->is_read_only = 0;
        }
    @endphp
                            <tr>
                                <td>
                                    <span class="label {{ $invoice_statuses[$invoice->invoice_status_id]['class'] }}">
                                        {{ $invoice_statuses[$invoice->invoice_status_id]['label'] }}
                                        @if($invoice->invoice_sign == '-1')
                                            &nbsp;<i class="fa fa-credit-invoice" title="{{ trans('credit_invoice') }}"></i>
                                        @endif
                                        @if($invoice->is_read_only)
                                            &nbsp;<i class="fa fa-read-only" title="{{ trans('read_only') }}"></i>
                                        @endif
                                        @if($invoice->invoice_is_recurring)
                                            &nbsp;<i class="fa fa-refresh" title="{{ trans('recurring') }}"></i>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ ($invoice->is_overdue) ? 'font-overdue' : '' }}">
                                        {{ date_from_mysql($invoice->invoice_date_due) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route(\'invoices.view\', $invoice->invoice_id) }}">$invoice->invoice_number ? $invoice->invoice_number : $invoice->invoice_id</a>
                                </td>
                                <td>
                                    <a href="{{ route(\'clients.view\', $invoice->client_id) }}">{{ htmlsc(format_client($invoice)) }}</a>
                                </td>
                                <td class="amount">
                                    {{ format_currency($invoice->invoice_balance * $invoice->invoice_sign) }}
                                </td>
                                <td style="text-align: center;">
@if($invoice->sumex_id != null)
                                    <a href="{{ route('invoices.generate-sumex-pdf', $invoice->invoice_id) }}"
                                       target="_blank" title="{{ trans('generate_sumex') }}">
                                        <i class="fa fa-file-pdf-o"></i>
                                    </a>
@else
                                    <a href="{{ route('invoices.generate_pdf', $invoice->invoice_id) }}"
                                       target="_blank" title="{{ trans('download_pdf') }}">
                                        <i class="fa fa-file-pdf-o"></i>
                                    </a>
@endif
                                </td>
                            </tr>
@endif
                        <tr>
                            <td colspan="6" class="text-right small">
                                {{ anchor('invoices/status/all', trans('view_all')) }}
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>

@if(get_setting('projects_enabled') == 1)
        <div class="row">
            <div class="col-xs-12 col-md-6">

                <div id="panel-projects" class="panel panel-default">

                    <div class="panel-heading">
                        <b><i class="fa fa-list fa-margin"></i> {{ trans('projects') }}</b>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-condensed no-margin">
                            <thead>
                            <tr>
                                <th>{{ trans('project_name') }}</th>
                                <th>{{ trans('client_name') }}</th>
                            </tr>
                            </thead>

                            <tbody>
@foreach($projects as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route(\'projects.view\', $project->project_id) }}">{{ htmlsc($project->project_name) }}</a>
                                    </td>
                                    <td>
                                        @if($project->client_id != null)
                                            {{ anchor('clients/view/' . $project->client_id, htmlsc(format_client($project))) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
@endif
                                <tr>
                                    <td colspan="6" class="text-right small">
                                        <a href=\"{{ route('projects.index') }}\">{{ trans('view_all') }}</a>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                </div>

            </div>
            <div class="col-xs-12 col-md-6">

                <div id="panel-recent-invoices" class="panel panel-default">

                    <div class="panel-heading">
                        <b><i class="fa fa-check-square-o fa-margin"></i> {{ trans('tasks') }}</b>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-condensed no-margin">

                            <thead>
                            <tr>
                                <th>{{ trans('status') }}</th>
                                <th>{{ trans('task_name') }}</th>
                                <th>{{ trans('task_finish_date') }}</th>
                                <th>{{ trans('project') }}</th>
                            </tr>
                            </thead>

                            <tbody>
@foreach($tasks as $task)
                                <tr>
                                    <td>
                                    <span class="label {{ $task_statuses[$task->task_status]['class'] ?? '' }}">
                                        @if(isset($task_statuses[$task->task_status]['label']))
                                            {{ $task_statuses[$task->task_status]['label'] }}
                                        } {{--PHPREMOVEEND--}}
                                    </span>
                                    </td>
                                    <td>
                                        {{ anchor('tasks/form/' . $task->task_id, htmlsc($task->task_name)) {{--PHPREMOVEEND--}}
                                    </td>
                                    <td>
                                    <span class="{{--PHPREMOVE--}} echo ($task->is_overdue) ? 'font-overdue' : '' }}">
                                        {{ date_from_mysql($task->task_finish_date) }}
                                    </span>
                                    </td>
                                    <td>
                                        {{ empty($task->project_id) ? '' : anchor('projects/view/' . $task->project_id, htmlsc($task->project_name)) }}
                                    </td>
                                </tr>
@endif
                                <tr>
                                    <td colspan="6" class="text-right small">
                                        <a href=\"{{ route('tasks.index') }}\">{{ trans('view_all') }}</a>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                </div>

            </div>
        </div>
@endif {{-- End if projects_enabled --}}

</div>
@endsection
