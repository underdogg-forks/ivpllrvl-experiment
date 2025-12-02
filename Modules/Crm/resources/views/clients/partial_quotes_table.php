<div class="table-responsive">
    <table class="table table-hover table-striped no-margin">

        <thead>
        <tr>
            <th>{{ trans('quote') }}</th>
            <th>{{ trans('created') }}</th>
            <th>{{ trans('due_date') }}</th>
            <th>{{ trans('client_name') }}</th>
            <th>{{ trans('amount') }}</th>
            <th>{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
<?php
foreach ($quotes as $quote) {
    ?>
            <tr>
                <td>
                    <a href="<?php echo site_url('guest/quotes/view/' . $quote->quote_id); ?>"
                       title="{{ trans('edit') }}">
                        <?php echo $quote->quote_number; ?>
                    </a>
<?php
        if ($quote->quote_status_id == 4) {
            ?>
                    <span class="text-success">{{ trans('approved') }}</span>
<?php
        } elseif ($quote->quote_status_id == 5) {
            ?>
                    <span class="text-danger">{{ trans('rejected') }}</span>
<?php
        }
    ?>
                </td>
                <td><?php echo date_from_mysql($quote->quote_date_created); ?></td>
                <td><?php echo date_from_mysql($quote->quote_date_expires); ?></td>
                <td><?php _htmlsc($quote->client_name); ?></td>
                <td><?php echo format_currency($quote->quote_total); ?></td>
                <td>
                    <div class="options btn-group btn-group-sm">
                        <a class="btn btn-default" href="<?php echo site_url('guest/quotes/view/' . $quote->quote_id); ?>">
                            <i class="fa fa-eye"></i> {{ trans('view') }}
                        </a>
                        <a class="btn btn-default" target="_blank" href="<?php echo site_url('guest/quotes/generate_pdf/' . $quote->quote_id); ?>">
                            <i class="fa fa-print"></i> {{ trans('pdf') }}
                        </a>
<?php
        if (in_array($quote->quote_status_id, [2, 3])) {
            ?>
                        <a class="btn btn-success" href="<?php echo site_url('guest/quotes/approve/' . $quote->quote_id); ?>">
                            <i class="fa fa-check"></i> {{ trans('approve') }}
                        </a>
                        <a class="btn btn-danger" href="<?php echo site_url('guest/quotes/reject/' . $quote->quote_id); ?>">
                            <i class="fa fa-ban"></i> {{ trans('reject') }}
                        </a>
<?php
        }
    ?>
                    </div>
                </td>
            </tr>
<?php
} // End foreach
?>
        </tbody>

    </table>
</div>
