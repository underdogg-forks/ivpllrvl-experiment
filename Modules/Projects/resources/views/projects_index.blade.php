<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('projects') }}</h1>

    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="{{ route('projects.create') }}">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>
</div>

<div id="content">
    @if (isset($projects) && $projects->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('project_name') }}</th>
                    <th>{{ trans('client') }}</th>
                    <th>{{ trans('actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $project)
                <tr>
                    <td>
                        <a href="{{ route('projects.view', ['project' => $project->project_id]) }}">
                            {{ $project->project_name ?? '' }}
                        </a>
                    </td>
                    <td>{{ $project->client->client_name ?? '' }}</td>
                    <td>
                        <a href="{{ route('projects.edit', ['project' => $project->project_id]) }}"
                           class="btn btn-xs btn-default">
                            <i class="fa fa-edit"></i> {{ trans('edit') }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if (method_exists($projects, 'links'))
            <div class="text-center">
                {!! $projects->links() !!}
            </div>
        @endif
    @else
        <div class="alert alert-info">
            {{ trans('no_projects') }}
        </div>
    @endif
</div>
