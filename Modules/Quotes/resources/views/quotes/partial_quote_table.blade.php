<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead>
        <tr>
            <th>@lang('status')</th>
            <th>@lang('quote')</th>
            <th>@lang('created')</th>
            <th>@lang('due_date')</th>
            <th>@lang('client_name')</th>
            <th class="amount last">@lang('amount')</th>
            <th>@lang('options')</th>
        </tr>
        </thead>

        <tbody>
        @php
            $quote_idx = 1;
            $quote_count = $quotes->count();
            $quote_list_split = $quote_count > 3 ? $quote_count / 2 : 9999;
        @endphp

        @foreach($quotes as $quote)
            @php
                $dropup = $quote_idx > $quote_list_split;
            @endphp
            <tr>
                <td>
                    <span class="label {{ $quote_statuses[$quote->quote_status_id]['class'] }}">
                        {{ $quote_statuses[$quote->quote_status_id]['label'] }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('quotes.view', ['quote_id' => $quote->quote_id ?? $quote->quote_number ?? 0]) }}" title="@lang('edit')">
                        {{ $quote->quote_number ?? $quote->quote_id }}
                    </a>
                </td>
                <td>{{ date_from_mysql($quote->quote_date_created) }}</td>
                <td>{{ date_from_mysql($quote->quote_date_expires) }}</td>
                <td>
                    <a href="{{ route('clients.view', $quote->client_id) }}" title="@lang('view_client')">
                        {{ $quote->client->name ?? '-' }}
                    </a>
                </td>
                <td class="amount last">{{ currency($quote->quote_total) }}</td>
                <td>
                    <div x-data="{ open: false }" class="relative inline-block text-left {{ $dropup ? 'dropup' : '' }}">
                        <button @click="open = !open"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fa fa-cog"></i> @lang('options')
                        </button>

                        <div x-show="open" @click.outside="open = false"
                             x-transition
                             class="absolute z-10 {{ $dropup ? 'bottom-full mb-2' : 'top-full mt-2' }} min-w-[160px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg overflow-hidden">
                            <a href="{{ route('quotes.view', ['quote_id' => $quote->quote_id]) }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fa fa-edit fa-margin"></i> @lang('edit')
                            </a>
                            <a href="{{ route('quotes.generate_pdf', ['quote_id' => $quote->quote_id]) }}" target="_blank"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fa fa-print fa-margin"></i> @lang('download_pdf')
                            </a>
                            <a href="{{ route('mailer.send-quote', ['quote_id' => $quote->quote_id]) }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fa fa-send fa-margin"></i> @lang('send_email')
                            </a>
                            <form action="{{ route('quotes.delete', ['quote_id' => $quote->quote_id]) }}" method="POST" onsubmit="return confirm('@lang('delete_quote_warning')');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-2">
                                    <i class="fa fa-trash-o fa-margin"></i> @lang('delete')
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @php $quote_idx++; @endphp
        @endforeach
        </tbody>
    </table>
</div>
