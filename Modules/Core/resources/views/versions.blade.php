@extends('core::layouts.app')

@section('content')
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <h1 class="headerbar-title text-xl font-bold">{{ trans('version_history') }}</h1>

    <div class="headerbar-item">
        {!! pager(route('settings.versions.index'), $versions) !!}
    </div>
</div>

<div id="content" class="table-content">

    <div class="overflow-x-auto">
        <table class="table w-full">

            <thead>
            <tr>
                <th class="px-4 py-2">{{ trans('date_applied') }}</th>
                <th class="px-4 py-2">{{ trans('sql_file') }}</th>
                <th class="px-4 py-2">{{ trans('errors') }}</th>
            </tr>
            </thead>

            <tbody>
@foreach($versions as $version)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-2">{{ date_from_timestamp($version->version_date_applied) }}</td>
                    <td class="px-4 py-2">{{ $version->version_file }}</td>
                    <td class="px-4 py-2">{{ $version->version_sql_errors }}</td>
                </tr>
@endforeach
            </tbody>

        </table>
    </div>

</div>
@endsection
