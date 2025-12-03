<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('tasks') }}</h1>

    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="{{ route('tasks.form') }}">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>
</div>

<div id="content">
    @if (isset($tasks) && $tasks->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('task_name') }}</th>
                    <th>{{ trans('project') }}</th>
                    <th>{{ trans('status') }}</th>
                    <th>{{ trans('actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                <tr>
                    <td>{{ $task->task_name ?? '' }}</td>
                    <td>{{ $task->project->project_name ?? '' }}</td>
                    <td>{{ $task->task_status ?? '' }}</td>
                    <td>
                        <a href="{{ route('tasks.form', ['task' => $task->task_id]) }}"
                           class="btn btn-xs btn-default">
                            <i class="fa fa-edit"></i> {{ trans('edit') }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if (method_exists($tasks, 'links'))
            <div class="text-center">
                {!! $tasks->links() !!}
            </div>
        @endif
    @else
        <div class="alert alert-info">
            {{ trans('no_tasks') }}
        </div>
    @endif
</div>
