@extends('core::layouts.app')

@section('content')
<script>
    $(function () {
        $('#user_all_clients').click(function () {
            all_client_check();
        });

        function all_client_check() {
            if ($('#user_all_clients').is(':checked')) {
                $('#list_client').hide();
            } else {
                $('#list_client').show();
            }
        }

        all_client_check();
    });
</script>

<form method="post">

    @csrf

    <div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
        <h1 class="headerbar-title text-xl font-bold">{{ trans('assign_client') }}</h1>
        @include('core::header_buttons')
    </div>

    <div id="content">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6 md:col-start-4">

                @include('core::layout.alerts')

                <input type="hidden" name="user_id" id="user_id"
                       value="{{ $user->user_id }}" required>

                <div class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="panel-heading px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">
                        {{ htmlspecialchars($user->user_name) }}
                    </div>
                    <div class="panel-body p-4">

                        <div class="alert alert-info bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-4 py-3 rounded mb-4">
                            <label class="flex items-start gap-2">
                                <input type="checkbox" name="user_all_clients" id="user_all_clients" value="1" {{ $user->user_all_clients ? 'checked' : '' }} class="mt-1">
                                <span>{{ trans('user_all_clients') }}</span>
                            </label>

                            <div class="mt-2 text-sm">
                                {{ trans('user_all_clients_text') }}
                            </div>
                        </div>

                        <div id="list_client">
                            <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ trans('client') }}</label>
                            <select name="client_id" id="client_id" class="form-control simple-select w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    autofocus="autofocus" required>
@foreach($clients as $client)
                                <option value="{{ $client->client_id }}">{{ htmlspecialchars(format_client($client)) }}</option>
@endforeach
                            </select>
                        </div>


                    </div>
                </div>

            </div>
        </div>

    </div>

</form>
@endsection
