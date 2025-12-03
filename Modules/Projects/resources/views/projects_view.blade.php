<div id="headerbar">
    <h1 class="headerbar-title">{{ $project->project_name ?? 'Project' }}</h1>

    <div class="headerbar-item pull-right">
        <a class="btn fi-size-sm fi-btn-primary" href="{{ route('projects.edit', ['project' => $project->project_id]) }}">
            <i class="fa fa-edit"></i> {{ trans('edit') }}
        </a>
    </div>
</div>

<div id="content">
    <div class="fi-section">
        <div class="fi-section-header">{{ trans('project_details') }}</div>
        <div class="fi-section-body">
            <dl class="dl-horizontal">
                <dt>{{ trans('project_name') }}:</dt>
                <dd>{{ $project->project_name ?? '' }}</dd>

                <dt>{{ trans('client') }}:</dt>
                <dd>{{ $project->client->client_name ?? '' }}</dd>
            </dl>
        </div>
    </div>

    @if (isset($tasks) && count($tasks) > 0)
    <div class="fi-section">
        <div class="fi-section-header">{{ trans('tasks') }}</div>
        <div class="fi-section-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ trans('task_name') }}</th>
                        <th>{{ trans('status') }}</th>
                        <th>{{ trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                    <tr>
                        <td>{{ $task->task_name ?? '' }}</td>
                        <td>{{ $task->task_status ?? '' }}</td>
                        <td>
                            <a href="{{ route('tasks.form', ['task' => $task->task_id]) }}"
                               class="btn fi-size-xs fi-btn-secondary">
                                <i class="fa fa-edit"></i> {{ trans('edit') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
