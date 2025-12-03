<div class="overflow-x-auto">
    <table class="table table-hover table-striped w-full">

        <thead>
        <tr>
            <th class="px-4 py-2">{{ trans('project_name') }}</th>
            <th class="px-4 py-2">{{ trans('client_name') }}</th>
            <th class="px-4 py-2">{{ trans('options') }}</th>
        </tr>
        </thead>

        <tbody>
@foreach($projects as $project)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-2">
                    <a href="{{ route('projects.show', $project->project_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ htmlspecialchars($project->project_name) }}
                    </a>
                </td>
                <td class="px-4 py-2">{{ $project->client_id ? htmlspecialchars(format_client($project)) : trans('none') }}</td>
                <td class="px-4 py-2">
                    <div class="options btn-group">
                        <a class="btn btn-default btn-sm dropdown-toggle inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                           data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
                        <ul class="dropdown-menu bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg">
                            <li>
                                <a href="{{ route('projects.edit', $project->project_id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('projects.destroy', $project->project_id) }}"
                                      method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-button w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            onclick="return confirm('{{ trans('delete_record_warning') }}');">
                                        <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
@endforeach
        </tbody>

    </table>
</div>
