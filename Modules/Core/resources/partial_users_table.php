    <div class="table-responsive">
        <table class="table table-hover table-striped">

            <thead>
            <tr>
                <th>{{ trans('name') }}</th>
                <th>{{ trans('user_type') }}</th>
                <th>{{ trans('email_address') }}</th>
                <th>{{ trans('options') }}</th>
            </tr>
            </thead>

            <tbody>
<?php
foreach ($users as $user) {
    ?>
                <tr>
                    <td><?php _htmlsc($user->user_name); ?></td>
                    <td><?php echo $user_types[$user->user_type]; ?></td>
                    <td><?php echo $user->user_email; ?></td>
                    <td>
                        <div class="options btn-group btn-group-sm">
<?php
        if ($user->user_type == 2) {
            ?>
                        <a href="<?php echo site_url('user_clients/user/' . $user->user_id); ?>"
                           class="btn btn-default">
                            <i class="fa fa-list fa-margin"></i> {{ trans('assigned_clients') }}
                        </a>
<?php
        } // Endif
    ?>
                            <a class="btn btn-default dropdown-toggle"
                               data-toggle="dropdown" href="#">
                                <i class="fa fa-cog"></i> {{ trans('options') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo site_url('users/form/' . $user->user_id); ?>">
                                        <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                    </a>
                                </li>
<?php
        if ($user->user_id !== 1) {
            ?>
                                    <li>
                                        <form action="<?php echo site_url('users/delete/' . $user->user_id); ?>"
                                              method="POST">
                                            <?php _csrf_field(); ?>
                                            <button type="submit" class="dropdown-button"
                                                    onclick="return confirm('{{ trans('delete_record_warning') }}');">
                                                <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                            </button>
                                        </form>
                                    </li>
<?php
        }
    ?>
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
