    <div class="table-responsive">
        <table class="table table-hover table-striped">

            <thead>
            <tr>
                <th>{{ trans('family_name') }}</th>
                <th>{{ trans('options') }}</th>
            </tr>
            </thead>

            <tbody>
<?php
foreach ($families as $family) {
    ?>
                <tr>
                    <td><a href="<?php echo site_url('families/form/' . $family->family_id); ?>"><i class="fa fa-edit"></i> <?php _htmlsc($family->family_name); ?></a></td>
                    <td>
                        <div class="options btn-group">
                            <a class="btn btn-default btn-sm dropdown-toggle"
                               data-toggle="dropdown" href="#">
                                <i class="fa fa-cog"></i> {{ trans('options') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo site_url('families/form/' . $family->family_id); ?>">
                                        <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                    </a>
                                </li>
                                <li>
                                    <form action="<?php echo site_url('families/delete/' . $family->family_id); ?>"
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
