<div class="overflow-x-auto">
    <table class="table table-hover table-striped no-margin w-full">

        <thead>
        <tr>
            <th class="px-4 py-2">{{ trans('quote') }}</th>
            <th class="px-4 py-2">{{ trans('created') }}</th>
            <th class="px-4 py-2">{{ trans('due_date') }}</th>
            <th class="px-4 py-2">{{ trans('client_name') }}</th>
            <th class="px-4 py-2">{{ trans('amount') }}</th>
            <th class="px-4 py-2">{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
@foreach($quotes as $quote)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-2">
                    <a href="{{ route('guest.view', $quote->quote_id) }}"
                       title="{{ trans('edit') }}"
                       class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $quote->quote_number }}
                    </a>
@if($quote->quote_status_id == 4)
                    <span class="text-success text-green-600 dark:text-green-400">{{ trans('approved') }}</span>
@elseif($quote->quote_status_id == 5)
                    <span class="text-danger text-red-600 dark:text-red-400">{{ trans('rejected') }}</span>
@endif
                </td>
                <td class="px-4 py-2">{{ date_from_mysql($quote->quote_date_created) }}</td>
                <td class="px-4 py-2">{{ date_from_mysql($quote->quote_date_expires) }}</td>
                <td class="px-4 py-2">{{ htmlspecialchars($quote->client_name) }}</td>
                <td class="px-4 py-2">{{ format_currency($quote->quote_total) }}</td>
                <td class="px-4 py-2">
                    <div class="options btn-group btn-group-sm flex gap-2">
                        <a class="fi-btn-secondary inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600" href="{{ route('guest.view', $quote->quote_id) }}">
                            <i class="fa fa-eye"></i> {{ trans('view') }}
                        </a>
                        <a class="fi-btn-secondary inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600" target="_blank" href="{{ route('guest.generate-pdf', $quote->quote_id) }}">
                            <i class="fa fa-print"></i> {{ trans('pdf') }}
                        </a>
@if(in_array($quote->quote_status_id, [2, 3]))
                        <a class="fi-btn-success" href="{{ route('guest.approve', $quote->quote_id) }}">
                            <i class="fa fa-check"></i> {{ trans('approve') }}
                        </a>
                        <a class="fi-btn-danger" href="{{ route('guest.reject', $quote->quote_id) }}">
                            <i class="fa fa-ban"></i> {{ trans('reject') }}
                        </a>
@endif
                    </div>
                </td>
            </tr>
@endforeach
        </tbody>

    </table>
</div>
