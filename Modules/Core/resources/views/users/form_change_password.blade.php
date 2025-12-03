@extends('core::layouts.app')

@section('content')
<script src="{{ asset('assets/core/js/zxcvbn.js') }}"></script>

<form method="post">

    @csrf

    <div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
        <h1 class="headerbar-title text-xl font-bold">{{ trans('change_password') }}</h1>
        @include('core::layout.header_buttons')
    </div>

    <div id="content">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6 md:col-start-4">

                @include('core::layout.alerts')

                <div class="fi-section">
                    <div class="fi-section-header">
                        {{ trans('change_password') }}
                    </div>

                    <div class="fi-section-body p-4">
                        <div class="fi-field-wrp mb-4">
                            <label for="user_password" class="block mb-2 font-medium">
                                {{ trans('password') }}
                            </label>
                            <input type="password" name="user_password" id="user_password"
                                   class="fi-input passwordmeter-input w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" required>
                            <div class="progress h-1 mt-2">
                                <div class="progress-bar progress-bar-danger passmeter passmeter-1 bg-red-600"
                                     style="width: 33%"></div>
                                <div class="progress-bar progress-bar-warning passmeter passmeter-2 bg-yellow-600 hidden"
                                     style="width: 33%"></div>
                                <div class="progress-bar progress-bar-success passmeter passmeter-3 bg-green-600 hidden"
                                     style="width: 34%"></div>
                            </div>
                        </div>

                        <div class="fi-field-wrp mb-4">
                            <label for="user_passwordv" class="block mb-2 font-medium">
                                {{ trans('verify_password') }}
                            </label>
                            <input type="password" name="user_passwordv" id="user_passwordv"
                                   class="fi-input w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" required>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

</form>
@endsection
