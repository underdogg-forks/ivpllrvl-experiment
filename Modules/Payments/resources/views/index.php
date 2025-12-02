<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('payment_methods') }}</h1>

    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="<?php echo site_url('payment_methods/form'); ?>">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>

    <div class="headerbar-item pull-right">
        <?php echo pager(site_url('payment_methods/index'), $payment_methods); ?>
    </div>

</div>

<div id="content" class="table-content">

    <?php $this->layout->load_view('layout/alerts'); ?>

    <div class="table-responsive">
        <table class="table table-hover table-striped">

            <thead>
            <tr>
                <th>{{ trans('payment_method') }}</th>
                <th>{{ trans('options') }}</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($payment_methods as $payment_method) { ?>
                <tr>
                    <td><?php _htmlsc($payment_method->payment_method_name); ?></td>
                    <td>
                        <div class="options btn-group">
                            <a class="btn btn-default btn-sm dropdown-toggle"
                               data-toggle="dropdown" href="#">
                                <i class="fa fa-cog"></i>
                                {{ trans('options') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo site_url('payment_methods/form/' . $payment_method->payment_method_id); ?>">
                                        <i class="fa fa-edit fa-margin"></i>
                                        {{ trans('edit') }}
                                    </a>
                                </li>
                                <li>
                                    <form action="<?php echo site_url('payment_methods/delete/' . $payment_method->payment_method_id); ?>"
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
            <?php } ?>
            </tbody>

        </table>
    </div>

</div>
