<div class="table-responsive">
    <table class="table table-hover table-striped">

        <thead>
        <tr>
            <th>{{ trans('label') }}</th>
            <th>{{ trans('table') }}</th>
            <th>{{ trans('position') }}</th>
            <th>{{ trans('type') }}</th>
            <th>{{ trans('order') }}</th>
            <th>{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
<?php
foreach ($custom_fields as $custom_field) {
    $alpha    = str_replace('-', '_', mb_strtolower($custom_field->custom_field_type));
    $position = $positions[$custom_field->custom_field_table][$custom_field->custom_field_location];
    ?>
            <tr>
                <td><?php _htmlsc($custom_field->custom_field_label); ?></td>
                <td>{{ trans($custom_tables[$custom_field->custom_field_table]) }}</td>
                <td>{{ $position }}</td>
                <td>{{ trans($alpha) }}</td>
                <td>{{ $custom_field->custom_field_order }}</td>
                <td>
                    <div class="options btn-group btn-group-sm">
                        <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
@if(in_array($custom_field->custom_field_type, $custom_value_fields))
                        <a href="{{ route('custom_values/field/' . $custom_field->custom_field_id) }}"
                           class="btn btn-default">
                            <i class="fa fa-list fa-margin"></i> {{ trans('values') }}
                        </a>
@endif
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('custom_fields/form/' . $custom_field->custom_field_id) }}">
                                    <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('custom_fields/delete/' . $custom_field->custom_field_id) }}"
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
@endif
        </tbody>

    </table>

</div>
