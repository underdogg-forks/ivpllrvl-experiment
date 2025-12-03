<div class="overflow-x-auto">
    <table class="table table-hover table-striped w-full">

        <thead>
            <tr>
                <th class="px-4 py-2">{{ trans('invoice') }}</th>
                <th class="px-4 py-2">{{ trans('created') }}</th>
                <th class="px-4 py-2">{{ trans('due_date') }}</th>
                <th class="px-4 py-2">{{ trans('client_name') }}</th>
                <th class="px-4 py-2">{{ trans('amount') }}</th>
                <th class="px-4 py-2">{{ trans('balance') }}</th>
                <th class="px-4 py-2">{{ trans('options') }}</th>
            </tr>
        </thead>

        <tbody>
@foreach($invoices as $invoice)
    @php
        $css_class = ($invoice->invoice_status_id != 4 && $invoice->invoice_date_due < date('Y-m-d')) ? 'font-overdue text-red-600 dark:text-red-400 font-semibold' : '';
    @endphp
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-2">
                    <a href="{{ route('guest.view', $invoice->invoice_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $invoice->invoice_number }}
                    </a>
                </td>
                <td class="px-4 py-2">{{ date_from_mysql($invoice->invoice_date_created) }}</td>
                <td class="px-4 py-2 {{ $css_class }}">{{ date_from_mysql($invoice->invoice_date_due) }}</td>
                <td class="px-4 py-2">{{ htmlspecialchars(format_client($invoice)) }}</td>
                <td class="px-4 py-2">{{ format_currency($invoice->invoice_total) }}</td>
                <td class="px-4 py-2">{{ format_currency($invoice->invoice_balance) }}</td>
                <td class="px-4 py-2">
                    <div class="options btn-group btn-group-sm flex gap-2">
                        <a class="btn btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600" href="{{ route('guest.view', $invoice->invoice_id) }}">
                            <i class="fa fa-eye"></i> {{ trans('view') }}
                        </a>
                        <a class="btn btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600" target="_blank" href="{{ route('guest.generate-pdf', $invoice->invoice_id) }}">
                            <i class="fa fa-print"></i> {{ trans('pdf') }}
                        </a>
@if($enable_online_payments && $invoice->invoice_balance > 0 && $invoice->invoice_status_id != 4)
                        <a class="btn btn-primary inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 dark:hover:bg-blue-600" href="{{ route('guest.form', $invoice->invoice_url_key) }}">
                            <i class="fa fa-credit-card"></i> {{ trans('pay_now') }}
                        </a>
@elseif($invoice->invoice_balance == 0)
                        <button class="btn btn-success disabled inline-flex items-center gap-2 px-3 py-1.5 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white opacity-60 cursor-not-allowed">
                            <i class="fa fa-check"></i> {{ trans('paid') }}
                        </button>
@endif

                    </div>
                </td>
            </tr>
@endforeach
        </tbody>

    </table>
</div>
