    <div class="table-responsive">
        <table class="table table-hover table-striped">

            <thead>
            <tr>
                <th>{{ trans('status') }}</th>
                <th>{{ trans('task_name') }}</th>
                <th>{{ trans('task_finish_date') }}</th>
                <th>{{ trans('project') }}</th>
                <th class="amount last">{{ trans('task_price') }}</th>
                <th>{{ trans('options') }}</th>
            </tr>
            </thead>

            <tbody>
<?php
foreach ($tasks as $task) {
    $label_class = $task_statuses[$task->task_status]['class'] ?? '';
    ?>
                <tr>
                    <td>
                        <span class="label <?php echo $label_class; ?>">
                            <?php echo $task_statuses[$task->task_status]['label'] ?? ''; ?>
                        </span>
                    </td>
                    <td><a href="<?php echo site_url('tasks/form/' . $task->task_id); ?>"><i class="fa fa-edit"></i> <?php _htmlsc($task->task_name); ?></a></td>
                    <td>
                        <div class="<?php echo $task->is_overdue ? 'text-danger' : ''; ?>">
                            <?php echo date_from_mysql($task->task_finish_date); ?>
                        </div>
                    </td>
                    <td>
                        <?php echo empty($task->project_id) ? '' : anchor('projects/view/' . $task->project_id, htmlsc($task->project_name)); ?>
                    </td>
                    <td class="amount last">
                        <?php echo format_currency($task->task_price); ?>
                    </td>
                    <td>
                        <div class="options btn-group">
                            <a class="btn btn-default btn-sm dropdown-toggle"
                               data-toggle="dropdown" href="#">
                                <i class="fa fa-cog"></i> {{ trans('options') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo site_url('tasks/form/' . $task->task_id); ?>"
                                       title="{{ trans('edit') }}">
                                        <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                    </a>
                                </li>
<?php
        if ( ! ($task->task_status == 4 && $this->config->item('enable_invoice_deletion') !== true)) {
            ?>
                                <li>
                                    <form action="<?php echo site_url('tasks/delete/' . $task->task_id); ?>"
                                          method="POST">
                                        <?php _csrf_field(); ?>
                                        <button type="submit" class="dropdown-button"
                                                onclick="return confirm('<?php echo $task->task_status == 4 ? trans('alert_task_delete') : trans('delete_record_warning') ?>');">
                                            <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                        </button>
                                    </form>
                                </li>
<?php
        } // end if
    ?>
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
