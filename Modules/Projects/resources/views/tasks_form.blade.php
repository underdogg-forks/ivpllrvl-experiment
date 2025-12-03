<div id="headerbar">
    <h1 class="headerbar-title">
        {{ isset($task->task_id) && $task->task_id ? trans('edit_task') : trans('new_task') }}
    </h1>
</div>

<div id="content">
    <form method="post" action="{{ isset($task->task_id) && $task->task_id ? route('tasks.update', ['task' => $task->task_id]) : route('tasks.store') }}">
        @csrf
        @if (isset($task->task_id) && $task->task_id)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="task_name">{{ trans('task_name') }} *</label>
            <input type="text"
                   name="task_name"
                   id="task_name"
                   class="form-control"
                   value="{{ $task->task_name ?? '' }}"
                   required>
        </div>

        <div class="form-group">
            <label for="project_id">{{ trans('project') }}</label>
            <select name="project_id" id="project_id" class="form-control">
                <option value="">{{ trans('select_project') }}</option>
                @if (isset($projects))
                    @foreach ($projects as $project)
                        <option value="{{ $project->project_id }}"
                                {{ (isset($task->project_id) && $task->project_id == $project->project_id) ? 'selected' : '' }}>
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <label for="task_status">{{ trans('status') }}</label>
            <select name="task_status" id="task_status" class="form-control">
                @if (isset($task_statuses))
                    @foreach ($task_statuses as $status_id => $status)
                        <option value="{{ $status_id }}"
                                {{ (isset($task->task_status) && $task->task_status == $status_id) ? 'selected' : '' }}>
                            {{ $status['label'] ?? $status }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <label for="task_finish_date">{{ trans('finish_date') }}</label>
            <input type="date"
                   name="task_finish_date"
                   id="task_finish_date"
                   class="form-control"
                   value="{{ $task->task_finish_date ?? '' }}">
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> {{ trans('save') }}
            </button>
            <a href="{{ route('tasks.index') }}" class="btn btn-default">
                <i class="fa fa-times"></i> {{ trans('cancel') }}
            </a>
        </div>
    </form>
</div>
