<div class="table-responsive">

    <table class="table table-hover table-striped">

        <thead>
        <tr>
            <th>{{ trans('id') }}</th>
            <th>{{ trans('field') }}</th>
            <th>{{ trans('elements') }}</th>
            <th>{{ trans('table') }}</th>
            <th>{{ trans('position') }}</th>
            <th>{{ trans('type') }}</th>
            <th>{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
<?php
foreach ($custom_values as $custom_values) {
    $href     = route('custom-fields.form', ['custom_field_id' => $custom_values->custom_field_id]);
    $alpha    = str_replace('-', '_', mb_strtolower($custom_values->custom_field_type));
    $position = $positions[$custom_values->custom_field_table][$custom_values->custom_field_location];
    ?>
            <tr>
                <td>{{ anchor($href, $custom_values->custom_field_id, ' title="' . trans('edit') . '"') }}</td>
                <td>{{ anchor($href, '<i class="fa fa-edit fa-margin"></i> ' . htmlsc($custom_values->custom_field_label), ' class="btn fi-size-sm fi-btn-secondary"') }}</td>
                <td>{{ $custom_values->count }}</td>
                <td>{{ trans($custom_tables[$custom_values->custom_field_table]) }}</td>
                <td>{{ $position }}</td>
                <td>{{ trans($alpha) }}</td>
                <td>
                    <div class="options btn-group">
                        <a class="fi-btn-secondary fi-size-sm dropdown-toggle" data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('custom-values.field', ['custom_field_id' => $custom_values->custom_field_id]) }}">
                                    <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }} ({{ trans('values') }})
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('custom-fields.delete', ['custom_field_id' => $custom_values->custom_field_id]) }}"
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
