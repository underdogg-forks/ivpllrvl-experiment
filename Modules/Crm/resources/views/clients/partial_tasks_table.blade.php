<div class="overflow-x-auto">
    <table class="table table-hover table-striped w-full">

        <thead>
        <tr>
            <th class="px-4 py-2">{{ trans('status') }}</th>
            <th class="px-4 py-2">{{ trans('task_name') }}</th>
            <th class="px-4 py-2">{{ trans('task_finish_date') }}</th>
            <th class="px-4 py-2">{{ trans('project') }}</th>
            <th class="px-4 py-2 amount last">{{ trans('task_price') }}</th>
            <th class="px-4 py-2">{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
@foreach($tasks as $task)
    @php
        $label_class = $task_statuses[$task->task_status]['class'] ?? '';
    @endphp
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-2">
                    <span class="label {{ $label_class }} px-2 py-1 rounded-md text-xs font-semibold">
                        {{ $task_statuses[$task->task_status]['label'] ?? '' }}
                    </span>
                </td>
                <td class="px-4 py-2">
                    <a href="{{ route('tasks.form', $task->task_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        <i class="fa fa-edit"></i> {{ htmlspecialchars($task->task_name) }}
                    </a>
                </td>
                <td class="px-4 py-2">
                    <div class="{{ $task->is_overdue ? 'text-danger text-red-600 dark:text-red-400' : '' }}">
                        {{ date_from_mysql($task->task_finish_date) }}
                    </div>
                </td>
                <td class="px-4 py-2">
@if(!empty($task->project_id))
                    <a href="{{ route('projects.view', $task->project_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ htmlspecialchars($task->project_name) }}
                    </a>
@endif
                </td>
                <td class="px-4 py-2 amount last">
                    {{ format_currency($task->task_price) }}
                </td>
                <td class="px-4 py-2">
                    <div class="options btn-group">
                        <a class="btn btn-default btn-sm dropdown-toggle inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                           data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
                        <ul class="dropdown-menu bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg">
                            <li>
                                <a href="{{ route('tasks.form', $task->task_id) }}"
                                   title="{{ trans('edit') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                </a>
                            </li>
@if(!($task->task_status == 4 && config('app.enable_invoice_deletion') !== true))
                            <li>
                                <form action="{{ route('tasks.delete', $task->task_id) }}"
                                      method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="dropdown-button w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            onclick="return confirm('{{ $task->task_status == 4 ? trans('alert_task_delete') : trans('delete_record_warning') }}');">
                                        <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                    </button>
                                </form>
                            </li>
@endif
                        </ul>
                    </div>

                </td>
            </tr>
@endforeach
        </tbody>

    </table>
</div>
