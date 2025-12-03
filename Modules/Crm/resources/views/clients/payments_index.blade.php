<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('payments') }}</h1>

    <div class="headerbar-item pull-right">
        {!! $payments->links() !!}
    </div>

</div>

<div id="content" class="table-content">

    @include('core::alerts')

    <div id="filter_results">
        <div class="table-responsive">
            <table class="table table-hover table-striped">

                <thead>
                <tr>
                    <th>{{ trans('date') }}</th>
                    <th>{{ trans('invoice') }}</th>
                    <th>{{ trans('amount') }}</th>
                    <th>{{ trans('payment_method') }}</th>
                    <th>{{ trans('note') }}</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td>{{ date_from_mysql($payment->payment_date) }}</td>
                        <td>
                            <a href="{{ route('guest.view', $payment->invoice_id) }}">
                                {{ $payment->invoice_number }}
                            </a>
                        </td>
                        <td>{{ format_currency($payment->payment_amount) }}</td>
                        <td>{{ $payment->payment_method_name }}</td>
                        <td>{{ $payment->payment_note }}</td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>
