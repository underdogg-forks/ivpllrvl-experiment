@extends('core::layouts.app')

@section('content')
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <h1 class="headerbar-title text-xl font-bold">{{ htmlspecialchars($project->project_name) }}</h1>

    <div class="headerbar-item">
        <div class="btn-group btn-group-sm flex gap-2">
            <a href="{{ route('tasks.form') }}" class="btn btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                <i class="fa fa-check-square-o fa-margin"></i>{{ trans('new_task') }}
            </a>
            <a href="{{ route('projects.edit', $project->project_id) }}" class="btn btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                <i class="fa fa-edit"></i> {{ trans('edit') }}
            </a>
            <a class="btn btn-danger inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 dark:bg-red-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 dark:hover:bg-red-600"
               href="{{ route('projects.destroy', $project->project_id) }}"
               onclick="return confirm('{{ trans('delete_record_warning') }}');">
                <i class="fa fa-trash-o"></i> {{ trans('delete') }}
            </a>
        </div>
    </div>
</div>

<div id="content">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-4">
@if(!empty($project->client_name))
            <div class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="panel-heading px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">
                    <strong>{{ htmlspecialchars(format_client($project)) }}</strong>
                </div>
                <div class="panel-body p-4">
                    <div class="client-address">
                        @include('crm::clients.partial_client_address', ['client' => $project])
                    </div>
                </div>
            </div>
@else
            <div class="alert alert-info bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-4 py-3 rounded">{{ trans('alert_no_client_assigned') }}</div>
@endif
        </div>
        <div class="md:col-span-8">

            <div class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="panel-heading px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">
                    {{ trans('tasks') }}
                </div>
                <div class="panel-body">

                    <div class="overflow-x-auto">
                        <table class="table table-hover table-striped no-margin w-full">

                            <thead>
                            <tr>
                                <th class="px-4 py-2">{{ trans('task_name') }}</th>
                                <th class="px-4 py-2">{{ trans('status') }}</th>
                                <th class="px-4 py-2">{{ trans('task_finish_date') }}</th>
                                <th class="px-4 py-2">{{ trans('project') }}</th>
                            </tr>
                            </thead>

                            <tbody>
@foreach($tasks as $task)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('tasks.edit', $task->task_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ htmlspecialchars($task->task_name) }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="label {{ $task_statuses[$task->task_status]['class'] }} px-2 py-1 rounded-md text-xs font-semibold">
                                            {{ $task_statuses[$task->task_status]['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="{{ $task->is_overdue ? 'text-danger text-red-600 dark:text-red-400' : '' }}">
                                            {{ date_from_mysql($task->task_finish_date) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('projects.edit', $project->project_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ htmlspecialchars($project->project_name) }}
                                        </a>
                                    </td>
                                </tr>
@endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
@if(empty($tasks))
                <div class="panel-body p-4">
                    <div class="alert alert-info no-margin bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-4 py-3 rounded">{{ trans('alert_no_tasks_found') }}</div>
                </div>
@endif
            </div>
        </div>
    </div>
</div>
@endsection
