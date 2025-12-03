                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>{{ trans('id') }}</th>
                            <th>{{ trans('label') }}</th>
                            <th>{{ trans('options') }}</th>
                        </tr>
                    </thead>

                    <tbody>
@foreach($elements as $element)
                        <tr>
                            <td>{{ $element->custom_values_id }}</td>
                            <td><?php _htmlsc($element->custom_values_value); ?></td>
                            <td>
                                <div class="options btn-group">
                                    <a class="fi-btn-secondary fi-size-sm dropdown-toggle" data-toggle="dropdown"
                                       href="#">
                                        <i class="fa fa-cog"></i> {{ trans('options') }}
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{ route('custom-values.edit', ['custom_values_id' => $element->custom_values_id]) }}">
                                                <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('custom-values.delete', ['custom_values_id' => $element->custom_values_id]) }}"
                                                  method="POST">
                                                <?php _csrf_field(); ?>
                                                <input type="hidden" name="custom_field_id" value="{{ $id }}">
                                                <button type="submit" class="dropdown-button"
                                                        onclick="return confirm(`{{ trans('delete_record_warning') }}`);">
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

