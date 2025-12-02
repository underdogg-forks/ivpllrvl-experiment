    <div class="table-responsive">
        <table class="table table-hover table-striped">

            <thead>
            <tr>
                <th>{{ trans('project_name') }}</th>
                <th>{{ trans('client_name') }}</th>
                <th>{{ trans('options') }}</th>
            </tr>
            </thead>

            <tbody>
<?php
foreach ($projects as $project) {
    ?>
                <tr>
                    <td><?php echo anchor('projects/view/' . $project->project_id, htmlsc($project->project_name)); ?></td>
                    <td><?php echo ($project->client_id) ? htmlsc(format_client($project)) : trans('none'); ?></td>
                    <td>
                        <div class="options btn-group">
                            <a class="btn btn-default btn-sm dropdown-toggle"
                               data-toggle="dropdown" href="#">
                                <i class="fa fa-cog"></i> {{ trans('options') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo site_url('projects/form/' . $project->project_id); ?>">
                                        <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                    </a>
                                </li>
                                <li>
                                    <form action="<?php echo site_url('projects/delete/' . $project->project_id); ?>"
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
}
?>
            </tbody>

        </table>
    </div>
