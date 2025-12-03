@extends('core::layouts.app')

@section('content')
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <h1 class="headerbar-title text-xl font-bold">{{ trans('assigned_clients') }}</h1>

    <div class="headerbar-item">
        <div class="btn-group btn-group-sm flex gap-2">
            <a class="btn btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600" href="{{ route('users.index') }}">
                <i class="fa fa-arrow-left"></i> {{ trans('back') }}
            </a>
            <a class="btn btn-primary inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 dark:hover:bg-blue-600" href="{{ route('users.modal-add-user-client', $id) }}">
                <i class="fa fa-plus"></i> {{ trans('new') }}
            </a>
        </div>
    </div>
</div>

<div id="content">

    @include('core::layout.alerts')

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-6 md:col-start-4">

            <div class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="panel-heading px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">
                    {{ trans('user') }}: {{ htmlspecialchars($user->user_name) }}
                </div>

                <div class="panel-body table-content">
                    <div class="overflow-x-auto no-margin">
                        <table class="table table-hover table-striped no-margin w-full">

                            <thead>
                            <tr>
                                <th class="px-4 py-2">{{ trans('client') }}</th>
                                <th class="px-4 py-2">{{ trans('options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($user_clients as $user_client)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('clients.view', $user_client->client_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ htmlspecialchars(format_client($user_client)) }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <form
                                            action="{{ route('users.delete-user-client', $user_client->user_client_id) }}"
                                            method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-default btn-sm inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                                                    onclick="return confirm('{{ trans('delete_user_client_warning') }}');">
                                                <i class="fa fa-trash-o fa-margin"></i> {{ trans('remove') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
