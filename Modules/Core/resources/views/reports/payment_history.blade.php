<!DOCTYPE html>
<html lang="{{ trans('cldr') }}">
<head>
    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }} - {{ trans('payment_history') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/invoiceplane/css/reports.css') }}" type="text/css">
</head>
<body>

    <h3 class="report_title">{{ trans('payment_history') }}<br><small>{{ $from_date }} - {{ $to_date }}</small></h3>

    <table>
        <tr>
            <th>{{ trans('date') }}</th>
            <th>{{ trans('invoice') }}</th>
            <th>{{ trans('client') }}</th>
            <th>{{ trans('payment_method') }}</th>
            <th>{{ trans('note') }}</th>
            <th class="amount">{{ trans('amount') }}</th>
        </tr>
@php $sum = 0; @endphp
@foreach($results as $result)
        <tr>
            <td>{{ date_from_mysql($result->payment_date, true) }}</td>
            <td>{{ $result->invoice_number }}</td>
            <td>{{ format_client($result) }}</td>
            <td>{{ htmlspecialchars($result->payment_method_name) }}</td>
            <td>{!! nl2br(htmlspecialchars($result->payment_note)) !!}</td>
            <td class="amount">{{ format_currency($result->payment_amount) }}</td>
        </tr>
    @php $sum += $result->payment_amount; @endphp
@endforeach

@if(!empty($results))
        <tr>
            <td colspan=5>{{ trans('total') }}</td>
            <td class="amount">{{ format_currency($sum) }}</td>
        </tr>
@endif
    </table>

</body>
</html>
