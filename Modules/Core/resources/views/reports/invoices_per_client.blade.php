<!DOCTYPE html>
<html lang="{{ trans('cldr') }}">
<head>
    <title>{{ get_setting('custom_title', 'InvoicePlane', true) }} - {{ trans('invoices_per_client') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/invoiceplane/css/reports.css') }}" type="text/css">
</head>
<body>

    <h3 class="report_title">{{ trans('invoices_per_client') }}<br><small>{{ $from_date }} - {{ $to_date }}</small></h3>

    <table>
@php $client_id = ''; @endphp
@foreach($results as $result)
    @if($client_id != $result->client_id)
        @php $client_id = $result->client_id; @endphp
        <tr>
            <th>{{ htmlspecialchars(format_client($result)) }}</th>
            <th></th>
            <th></th>
        </tr>
    @endif
        <tr>
            <td>{{ date_from_mysql($result->invoice_date_created, true) }}</td>
            <td>{{ $result->invoice_number }}</td>
            <td class="amount">{{ format_currency($result->invoice_total) }}</td>
        </tr>
@endforeach
    </table>

</body>
</html>
