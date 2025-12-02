<div class="table-responsive">
    <table class="table table-hover table-striped">

        <thead>
        <tr>
            <th>{{ trans('invoice') }}</th>
            <th>{{ trans('created') }}</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($invoices_archive as $invoice) {
            ?>
            <tr>
                <td>
                    <a href="<?php echo site_url('invoices/download/' . basename($invoice)); ?>"
                       title="{{ trans('invoice') }}">
                        <?php echo basename($invoice); ?>
                    </a>
                </td>

                <td>
                    <?php echo date('F d Y H:i:s.', filemtime($invoice)); ?>
                </td>

            </tr>
        <?php } ?>
        </tbody>

    </table>
</div>
