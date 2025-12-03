<div class="table-responsive">
    <table class="table table-hover table-striped">

        <thead>
        <tr>
            <th>{{ trans('status') }}</th>
            <th>{{ trans('invoice') }}</th>
            <th>{{ trans('created') }}</th>
            <th>{{ trans('due_date') }}</th>
            <th>{{ trans('client_name') }}</th>
            <th class="amount">{{ trans('amount') }}</th>
            <th class="amount last">{{ trans('balance') }}</th>
            <th>{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
        @php
            $invoice_idx = 1;
            $invoice_count = count($invoices);
            $invoice_list_split = $invoice_count > 3 ? $invoice_count / 2 : 9999;
        @endphp

        @foreach ($invoices as $invoice)
            @php
                // Disable read-only if not applicable
                if (config('invoiceplane.disable_read_only') == true) {
                    $invoice->is_read_only = 0;
                }
                // Convert the dropdown menu to a dropup if invoice is after the invoice split
                $dropup = $invoice_idx > $invoice_list_split;
            @endphp
            <tr>
                <td>
                    <span class="label {{ $invoice_statuses[$invoice->invoice_status_id]['class'] }}">
                        {{ $invoice_statuses[$invoice->invoice_status_id]['label'] }}
                        @if ($invoice->invoice_sign == '-1')
                            &nbsp;<i class="fa fa-credit-invoice" title="{{ trans('credit_invoice') }}"></i>
                        @endif
                        @if ($invoice->is_read_only)
                            &nbsp;<i class="fa fa-read-only" title="{{ trans('read_only') }}"></i>
                        @endif
                        @if ($invoice->invoice_is_recurring)
                            &nbsp;<i class="fa fa-refresh" title="{{ trans('recurring') }}"></i>
                        @endif
                    </span>
                </td>

                <td>
                    <a href="{{ route('invoices.view', $invoice->invoice_id) }}"
                       title="{{ trans('edit') }}">
                        {{ $invoice->invoice_number ? $invoice->invoice_number : $invoice->invoice_id }}
                    </a>
                </td>

                <td>
                    {{ date_from_mysql($invoice->invoice_date_created) }}
                </td>

                <td>
                    <span class="{{ $invoice->is_overdue ? 'font-overdue' : '' }}">
                        {{ date_from_mysql($invoice->invoice_date_due) }}
                    </span>
                </td>

                <td>
                    <a href="{{ route('clients.view', $invoice->client_id) }}"
                       title="{{ trans('view_client') }}">
                        {{ format_client($invoice) }}
                    </a>
                </td>

                <td class="amount {{ ($invoice->invoice_sign == '-1') ? 'text-danger' : '' }}">
                    {{ format_currency($invoice->invoice_total) }}
                </td>

                <td class="amount last">
                    {{ format_currency($invoice->invoice_balance) }}
                </td>

                <td>
                    <div class="options btn-group{{ $dropup ? ' dropup' : '' }}">
                        <a class="fi-btn-secondary fi-size-sm dropdown-toggle" data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
                        <ul class="dropdown-menu">
                            @if ($invoice->is_read_only != 1)
                                <li>
                                    <a href="{{ route('invoices.view', $invoice->invoice_id) }}">
                                        <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ route('invoices.generate_pdf', $invoice->invoice_id) }}"
                                   target="_blank">
                                    <i class="fa fa-print fa-margin"></i> {{ trans('download_pdf') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mailer.invoice', $invoice->invoice_id) }}">
                                    <i class="fa fa-send fa-margin"></i> {{ trans('send_email') }}
                                </a>
                            </li>
                            <li>
                                <a href="#" class="invoice-add-payment"
                                   data-invoice-id="{{ $invoice->invoice_id }}"
                                   data-invoice-balance="{{ $invoice->invoice_balance }}"
                                   data-invoice-payment-method="{{ $invoice->payment_method }}">
                                    <i class="fa fa-money fa-margin"></i>
                                    {{ trans('enter_payment') }}
                                </a>
                            </li>
                            @if (
                                $invoice->invoice_status_id == 1
                                || (config('invoiceplane.enable_invoice_deletion') === true && $invoice->is_read_only != 1)
                            )
                                <li>
                                    <form action="{{ route('invoices.delete', $invoice->invoice_id) }}"
                                          method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-button"
                                                onclick="return confirm('{{ trans('delete_invoice_warning') }}');">
                                            <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                        </button>
                                    </form>
                                </li>
                            @endif
                        </ul>
                    </div>
                </td>
            </tr>
            @php
                $invoice_idx++;
            @endphp
        @endforeach
        </tbody>

    </table>
</div>
