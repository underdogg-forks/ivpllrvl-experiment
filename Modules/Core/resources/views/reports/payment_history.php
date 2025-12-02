<!DOCTYPE html>
<html lang="{{ trans('cldr') }}">
<head>
    <title><?php echo get_setting('custom_title', 'InvoicePlane', true); ?> - {{ trans('payment_history') }}</title>
    <link rel="stylesheet" href="<?php _theme_asset('css/reports.css'); ?>" type="text/css">
</head>
<body>

    <h3 class="report_title">{{ trans('payment_history') }}<br><small><?php echo $from_date . ' - ' . $to_date ?></small></h3>

    <table>
        <tr>
            <th>{{ trans('date') }}</th>
            <th>{{ trans('invoice') }}</th>
            <th>{{ trans('client') }}</th>
            <th>{{ trans('payment_method') }}</th>
            <th>{{ trans('note') }}</th>
            <th class="amount">{{ trans('amount') }}</th>
        </tr>
<?php
$sum = 0;
    foreach ($results as $result) {
        ?>
        <tr>
            <td><?php echo date_from_mysql($result->payment_date, true); ?></td>
            <td><?php echo $result->invoice_number; ?></td>
            <td><?php echo format_client($result); ?></td>
            <td><?php _htmlsc($result->payment_method_name); ?></td>
            <td><?php echo nl2br(htmlsc($result->payment_note)); ?></td>
            <td class="amount"><?php echo format_currency($result->payment_amount); ?></td>
        </tr>
<?php
            $sum += $result->payment_amount;
    }

    if ( ! empty($results)) {
        ?>
        <tr>
            <td colspan=5>{{ trans('total') }}</td>
            <td class="amount"><?php echo format_currency($sum); ?></td>
        </tr>
<?php
    }
    ?>
    </table>

</body>
</html>
