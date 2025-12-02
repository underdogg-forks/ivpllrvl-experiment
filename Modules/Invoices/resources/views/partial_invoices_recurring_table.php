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
<?php
foreach ($recurring_invoices as $invoice) {
    ?>
                    <tr>
                        <td>
                            <span class="label label-<?php echo $invoice->recur_status != 'active' ? 'default' : 'success'; ?>">
                                <?php _trans($invoice->recur_status); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo site_url('invoices/view/' . $invoice->invoice_id); ?>">
                                <?php echo $invoice->invoice_number; ?>
                            </a>
                        </td>
                        <td><?php echo anchor('clients/view/' . $invoice->client_id, htmlsc(format_client($invoice))); ?></td>
                        <td><?php echo date_from_mysql($invoice->recur_start_date); ?></td>
                        <td><?php echo date_from_mysql($invoice->recur_end_date); ?></td>
                        <td><?php _trans($recur_frequencies[$invoice->recur_frequency]); ?></td>
                        <td><?php echo date_from_mysql($invoice->recur_next_date); ?></td>
                        <td>
                            <div class="options btn-group">
                                <a href="#" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-cog"></i> {{ trans('options') }}
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="<?php echo site_url('invoices/recurring/stop/' . $invoice->invoice_recurring_id); ?>">
                                            <i class="fa fa-ban fa-margin"></i> {{ trans('stop') }}
                                        </a>
                                    </li>
                                    <li>
                                        <form action="<?php echo site_url('invoices/recurring/delete/' . $invoice->invoice_recurring_id); ?>"
                                              method="POST">
                                            <?php _csrf_field(); ?>
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
<?php
}
?>
                </tbody>

            </table>
        </div>
