@extends('core::components.layouts.app')

@section('content')
<script>
    $(function () {
        @include('crm::clients.script_select2_client_id')
    });
</script>

<form method="post">

    @csrf

    <div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
        <h1 class="headerbar-title text-xl font-bold">{{ trans('projects_form') }}</h1>
        @include('core::header_buttons')
    </div>

    <div id="content">

        @include('core::layout.alerts')

        <div class="form-group mb-4">
            <label for="project_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ trans('project_name') }}</label>
            <input type="text" name="project_name" id="project_name" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                   value="{{ old('project_name', $project->project_name ?? '') }}" required>
        </div>

        <div class="form-group has-feedback mb-4">
            <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ trans('client') }}</label>
            <div class="input-group flex">
                <span id="toggle_permissive_search_clients" class="input-group-addon px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-md cursor-pointer" title="{{ trans('enable_permissive_search_clients') }}">
                    <i class="fa fa-toggle-{{ get_setting('enable_permissive_search_clients') ? 'on' : 'off' }} fa-fw"></i>
                </span>
                <select name="client_id" id="client_id" class="client-id-select form-control flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-r-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" autofocus="autofocus">
@php
$permissive = get_setting('enable_permissive_search_users');
@endphp
@if(!empty($project->client_id))
                    <option value="{{ $project->client_id }}">{{ htmlspecialchars(format_client($project)) }}</option>
@endif
                </select>
            </div>
        </div>

        <input class="hidden" id="input_permissive_search_clients"
               value="{{ get_setting('enable_permissive_search_clients') }}">
    </div>

</form>
@endsection
