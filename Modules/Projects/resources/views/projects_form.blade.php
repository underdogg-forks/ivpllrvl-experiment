<div id="headerbar">
    <h1 class="headerbar-title">
        {{ isset($project->project_id) && $project->project_id ? trans('edit_project') : trans('new_project') }}
    </h1>
</div>

<div id="content">
    <form method="post" action="{{ isset($project->project_id) && $project->project_id ? route('projects.update', ['project' => $project->project_id]) : route('projects.store') }}">
        @csrf
        @if (isset($project->project_id) && $project->project_id)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="project_name">{{ trans('project_name') }} *</label>
            <input type="text"
                   name="project_name"
                   id="project_name"
                   class="form-control"
                   value="{{ $project->project_name ?? '' }}"
                   required>
        </div>

        <div class="form-group">
            <label for="client_id">{{ trans('client') }} *</label>
            <select name="client_id" id="client_id" class="form-control" required>
                <option value="">{{ trans('select_client') }}</option>
                @if (isset($clients))
                    @foreach ($clients as $client)
                        <option value="{{ $client->client_id }}"
                                {{ (isset($project->client_id) && $project->client_id == $client->client_id) ? 'selected' : '' }}>
                            {{ $client->client_name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> {{ trans('save') }}
            </button>
            <a href="{{ route('projects.index') }}" class="btn btn-default">
                <i class="fa fa-times"></i> {{ trans('cancel') }}
            </a>
        </div>
    </form>
</div>
