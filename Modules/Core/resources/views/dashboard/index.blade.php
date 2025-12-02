@extends('core::components.layouts.app')

@section('content')
    <div id="content" class="space-y-6">

        @include('core::layout.alerts')

        {{-- Quick Actions --}}
        <div class="{{ get_setting('disable_quickactions') == 1 ? 'hidden' : '' }}">
            <div id="panel-quick-actions" class="bg-white border rounded shadow">
                <div class="px-4 py-2 border-b font-bold">{{ trans('quick_actions') }}</div>
                <div class="grid grid-cols-4 gap-1 text-center">
                    <a href="{{ route('clients.form') }}" class="py-3 hover:bg-gray-100 border-r">
                        <i class="fa fa-user mb-1 block"></i>
                        <span class="hidden sm:inline">{{ trans('add_client') }}</span>
                    </a>
                    <a href="javascript:void(0)" class="create-quote py-3 hover:bg-gray-100 border-r">
                        <i class="fa fa-file mb-1 block"></i>
                        <span class="hidden sm:inline">{{ trans('create_quote') }}</span>
                    </a>
                    <a href="javascript:void(0)" class="create-invoice py-3 hover:bg-gray-100 border-r">
                        <i class="fa fa-file-text mb-1 block"></i>
                        <span class="hidden sm:inline">{{ trans('create_invoice') }}</span>
                    </a>
                    <a href="{{ route('payments.form') }}" class="py-3 hover:bg-gray-100">
                        <i class="fa fa-credit-card mb-1 block"></i>
                        <span class="hidden sm:inline">{{ trans('enter_payment') }}</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Quote & Invoice Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div id="panel-quote-overview" class="bg-white border rounded shadow">
                <div class="px-4 py-2 border-b font-bold flex justify-between items-center">
                    <span><i class="fa fa-bar-chart mr-2"></i>{{ trans('quote_overview') }}</span>
                    <span class="text-gray-500">{{ $quote_status_period }}</span>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    @foreach ($quote_status_totals as $total)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <a href="{{ route($total['href']) }}">{{ $total['label'] }}</a>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <span class="{{ $total['class'] }}">{{ format_currency($total['sum_total']) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <div id="panel-invoice-overview" class="bg-white border rounded shadow">
                <div class="px-4 py-2 border-b font-bold flex justify-between items-center">
                    <span><i class="fa fa-bar-chart mr-2"></i>{{ trans('invoice_overview') }}</span>
                    <span class="text-gray-500">{{ $invoice_status_period }}</span>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    @foreach ($invoice_status_totals as $total)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <a href="{{ route($total['href']) }}">{{ $total['label'] }}</a>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <span class="{{ $total['class'] }}">{{ format_currency($total['sum_total']) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </table>

                @if(empty($overdue_invoices))
                    <div class="px-4 py-2 border-t text-gray-500">{{ trans('no_overdue_invoices') }}</div>
                @else
                    @php
                        $overdue_invoices_total = collect($overdue_invoices)->sum(fn($invoice) => $invoice['invoice_balance'] * $invoice['invoice_sign']);
                    @endphp
                    <div class="px-4 py-2 border-t bg-red-50 text-red-700 flex justify-between items-center">
                        <a href="{{ route('invoices.status.overdue') }}" class="font-semibold">
                            <i class="fa fa-external-link mr-1"></i>{{ trans('overdue_invoices') }}
                        </a>
                        <span>{{ format_currency($overdue_invoices_total) }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Quotes & Invoices --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div id="panel-recent-quotes" class="bg-white border rounded shadow overflow-x-auto">
                <div class="px-4 py-2 border-b font-bold flex items-center">
                    <i class="fa fa-history mr-2"></i>{{ trans('recent_quotes') }}
                </div>
                <table class="min-w-full divide-y divide-gray-200 table-auto">
                    <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">{{ trans('status') }}</th>
                        <th class="px-4 py-2">{{ trans('date') }}</th>
                        <th class="px-4 py-2">{{ trans('quote') }}</th>
                        <th class="px-4 py-2">{{ trans('client') }}</th>
                        <th class="px-4 py-2 text-right">{{ trans('balance') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($quotes as $quote)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <span class="label {{ $quote_statuses[$quote->quote_status_id]['class'] }}">
                                    {{ $quote_statuses[$quote->quote_status_id]['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ date_from_mysql($quote->quote_date_created) }}</td>
                            <td class="px-4 py-2"><a href="{{ route('quotes.view', ['quote_id' => $quote['quote_id']]) }}">{{ $quote->quote_number ?: $quote->quote_id }}</a></td>
                            <td class="px-4 py-2"><a href="{{ route('clients.view', ['client_id' => $quote->client_id]) }}">{{ htmlsc(format_client($quote)) }}</a></td>
                            <td class="px-4 py-2 text-right">{{ format_currency($quote->quote_total) }}</td>
                            <td class="px-4 py-2 text-center">
                                <a href="{{ route('quotes.generate_pdf', ['quote_id' => $quote->quote_id]) }}" target="_blank">
                                    <i class="fa fa-file-pdf-o"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6" class="px-4 py-2 text-right text-sm">
                            <a href="{{ route('quotes.status.all') }}">{{ trans('view_all') }}</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div id="panel-recent-invoices" class="bg-white border rounded shadow overflow-x-auto">
                <div class="px-4 py-2 border-b font-bold flex items-center">
                    <i class="fa fa-history mr-2"></i>{{ trans('recent_invoices') }}
                </div>
                <table class="min-w-full divide-y divide-gray-200 table-auto">
                    <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2">{{ trans('status') }}</th>
                        <th class="px-4 py-2">{{ trans('due_date') }}</th>
                        <th class="px-4 py-2">{{ trans('invoice') }}</th>
                        <th class="px-4 py-2">{{ trans('client') }}</th>
                        <th class="px-4 py-2 text-right">{{ trans('balance') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($invoices as $invoice)
                        @php if(config('disable_read_only')) $invoice->is_read_only = 0; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <span class="label {{ $invoice_statuses[$invoice->invoice_status_id]['class'] }}">
                                    {{ $invoice_statuses[$invoice->invoice_status_id]['label'] }}
                                    @if($invoice->invoice_sign == '-1')
                                        &nbsp;<i class="fa fa-credit-invoice"></i>
                                    @endif
                                    @if($invoice->is_read_only)
                                        &nbsp;<i class="fa fa-read-only"></i>
                                    @endif
                                    @if($invoice->invoice_is_recurring)
                                        &nbsp;<i class="fa fa-refresh"></i>
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-2"><span class="{{ $invoice->is_overdue ? 'text-red-600' : '' }}">{{ date_from_mysql($invoice->invoice_date_due) }}</span></td>
                            <td class="px-4 py-2"><a href="{{ route('invoices.view', ['id' => $invoice->invoice_id]) }}">{{ $invoice->invoice_number ?: $invoice->invoice_id }}</a></td>
                            <td class="px-4 py-2"><a href="{{ route('clients.view', ['id' => $invoice->client_id]) }}">{{ htmlsc(format_client($invoice)) }}</a></td>
                            <td class="px-4 py-2 text-right">{{ format_currency($invoice->invoice_balance * $invoice->invoice_sign) }}</td>
                            <td class="px-4 py-2 text-center">
                                <a href="{{ $invoice->sumex_id ? route('invoices.generate_sumex_pdf', ['id' => $invoice->invoice_id]) : route('invoices.generate_pdf', ['id' => $invoice->invoice_id]) }}"
                                   target="_blank">
                                    <i class="fa fa-file-pdf-o"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6" class="px-4 py-2 text-right text-sm">
                            <a href="{{ route('invoices.status.all') }}">{{ trans('view_all') }}</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
