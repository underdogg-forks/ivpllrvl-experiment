<div class="table-responsive">
    <table class="table table-hover table-striped">

        <thead>
        <tr>
            <th>{{ trans('status') }}</th>
            <th>{{ trans('quote') }}</th>
            <th>{{ trans('created') }}</th>
            <th>{{ trans('due_date') }}</th>
            <th>{{ trans('client_name') }}</th>
            <th class="amount last">{{ trans('amount') }}</th>
            <th>{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
        @php
            $quote_idx = 1;
            $quote_count = count($quotes);
            $quote_list_split = $quote_count > 3 ? $quote_count / 2 : 9999;
        @endphp

        @foreach ($quotes as $quote)
            @php
                // Convert the dropdown menu to a dropup if quote is after the invoice split
                $dropup = $quote_idx > $quote_list_split;
            @endphp
            <tr class="hover:bg-hover">
                <td>
                    <span class="label {{ $quote_statuses[$quote->quote_status_id]['class'] }}">
                        {{ $quote_statuses[$quote->quote_status_id]['label'] }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('quotes.view', $quote->quote_id) }}"
                       class="text-accent hover:underline"
                       title="{{ trans('edit') }}">
                        {{ $quote->quote_number ? $quote->quote_number : $quote->quote_id }}
                    </a>
                </td>
                <td>
                    {{ date_from_mysql($quote->quote_date_created) }}
                </td>
                <td>
                    {{ date_from_mysql($quote->quote_date_expires) }}
                </td>
                <td>
                    <a href="{{ route('clients.view', $quote->client_id) }}"
                       class="text-accent hover:underline"
                       title="{{ trans('view_client') }}">
                        {{ format_client($quote) }}
                    </a>
                </td>
                <td class="amount last">
                    {{ format_currency($quote->quote_total) }}
                </td>
                <td>
                    <div x-data="{ quoteMenuOpen: false }" class="relative inline-block text-left">
                        <button @click="quoteMenuOpen = !open" type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-elevated border border-primary-dark rounded-md text-sm font-medium text-primary hover:bg-hover focus:outline-none focus:ring-2 focus:ring-primary transition-colors">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </button>
                        <ul x-show="quoteMenuOpen" @click.away="quoteMenuOpen = false" x-cloak
                            class="absolute {{ $dropup ? 'bottom-full mb-1' : 'mt-1' }} right-0 w-48 bg-elevated border border-primary rounded-md shadow-lg z-50">
                            <li>
                                <a href="{{ route('quotes.view', $quote->quote_id) }}"
                                   class="block px-4 py-2 text-sm text-primary hover:bg-hover">
                                    <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('quotes.generate_pdf', $quote->quote_id) }}"
                                   target="_blank"
                                   class="block px-4 py-2 text-sm text-primary hover:bg-hover">
                                    <i class="fa fa-print fa-margin"></i> {{ trans('download_pdf') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mailer.quote', $quote->quote_id) }}"
                                   class="block px-4 py-2 text-sm text-primary hover:bg-hover">
                                    <i class="fa fa-send fa-margin"></i> {{ trans('send_email') }}
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('quotes.delete', $quote->quote_id) }}"
                                      method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-hover"
                                            onclick="return confirm('{{ trans('delete_quote_warning') }}');">
                                        <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            @php
                $quote_idx++;
            @endphp
        @endforeach
        </tbody>

    </table>
</div>
