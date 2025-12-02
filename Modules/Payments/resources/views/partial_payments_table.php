<div class="table-responsive">
    <table class="table table-hover table-striped">

        <thead>
        <tr>
            <th>{{ trans('payment_date') }}</th>
            <th>{{ trans('invoice_date') }}</th>
            <th>{{ trans('invoice') }}</th>
            <th>{{ trans('client') }}</th>
            <th class="amount last">{{ trans('amount') }}</th>
            <th>{{ trans('payment_method') }}</th>
            <th>{{ trans('note') }}</th>
            <th>{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
<?php
foreach ($payments as $payment) {
    ?>
            <tr>
                <td><?php echo date_from_mysql($payment->payment_date); ?></td>
                <td><?php echo date_from_mysql($payment->invoice_date_created); ?></td>
                <td><?php echo anchor('invoices/view/' . $payment->invoice_id, $payment->invoice_number); ?></td>
                <td>
                    <a href="<?php echo site_url('clients/view/' . $payment->client_id); ?>"
                       title="{{ trans('view_client') }}">
                        <?php _htmlsc(format_client($payment)); ?>
                    </a>
                </td>
                <td class="amount last"><?php echo format_currency($payment->payment_amount); ?></td>
                <td><?php _htmlsc($payment->payment_method_name); ?></td>
                <td><?php _htmlsc($payment->payment_note); ?></td>
                <td>
                    <div class="options btn-group">
                        <a class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?php echo site_url('payments/form/' . $payment->payment_id); ?>">
                                    <i class="fa fa-edit fa-margin"></i>
                                    {{ trans('edit') }}
                                </a>
                            </li>
                            <li>
                                <form action="<?php echo site_url('payments/delete/' . $payment->payment_id); ?>"
                                      method="POST">
                                    <?php _csrf_field(); ?>
                                    <button type="submit" class="dropdown-button"
                                            onclick="return confirm('{{ trans('delete_record_warning') }}');">
                                        <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
<?php
} // End foreach
?>
        </tbody>

    </table>
</div>
