<!DOCTYPE html>
<html lang="{{ trans('cldr') }}">
<head>
    <title><?php echo get_setting('custom_title', 'InvoicePlane', true); ?> - {{ trans('sales_by_client') }}</title>
    <link rel="stylesheet" href="<?php _theme_asset('css/reports.css'); ?>" type="text/css">
</head>
<body>

    <h3 class="report_title">{{ trans('sales_by_client') }}<br><small><?php echo $from_date . ' - ' . $to_date ?></small></h3>

    <table>
        <tr>
            <th>{{ trans('client') }}</th>
            <th class="amount">{{ trans('invoice_count') }}</th>
            <th class="amount">{{ trans('sales') }}</th>
            <th class="amount">{{ trans('sales_with_tax') }}</th>
        </tr>
<?php
foreach ($results as $result) {
    ?>
        <tr>
            <td><?php _htmlsc(format_client($result)); ?></td>
            <td class="amount"><?php echo $result->invoice_count; ?></td>
            <td class="amount"><?php echo format_currency($result->sales); ?></td>
            <td class="amount"><?php echo format_currency($result->sales_with_tax); ?></td>
        </tr>
<?php
}
    ?>
    </table>

</body>
</html>
