<div class="table-responsive">
    <table class="table table-striped">

        <thead>
        <tr>
            <th>{{ trans('status') }}</th>
            <th>{{ trans('base_invoice') }}</th>
            <th>{{ trans('client') }}</th>
            <th>{{ trans('start_date') }}</th>
            <th>{{ trans('end_date') }}</th>
            <th>{{ trans('every') }}</th>
            <th>{{ trans('next_date') }}</th>
            <th>{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($recurring_invoices as $invoice)
            <tr>
                <td>
                    <span class="label label-{{ $invoice->recur_status != 'active' ? 'default' : 'success' }}">
                        {{ trans($invoice->recur_status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('invoices.view', $invoice->invoice_id) }}">
                        {{ $invoice->invoice_number }}
                    </a>
                </td>
                <td>
                    <a href="{{ route('clients.view', $invoice->client_id) }}">{{ format_client($invoice) }}</a>
                </td>
                <td>{{ date_from_mysql($invoice->recur_start_date) }}</td>
                <td>{{ date_from_mysql($invoice->recur_end_date) }}</td>
                <td>{{ trans($recur_frequencies[$invoice->recur_frequency]) }}</td>
                <td>{{ date_from_mysql($invoice->recur_next_date) }}</td>
                <td>
                    <div class="options btn-group">
                        <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('invoices.recurring.stop', $invoice->invoice_recurring_id) }}">
                                    <i class="fa fa-ban fa-margin"></i> {{ trans('stop') }}
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('invoices.recurring.delete', $invoice->invoice_recurring_id) }}"
                                      method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-button"
                                            onclick="return confirm('{{ trans('delete_invoice_warning') }}');">
                                        <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>
</div>
