@extends('core::components.layouts.auth')

@section('auth.content')
    <form method="POST" action="{{ route('sessions.authenticate') }}" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm">
        @csrf

        <div class="form-group">
            <label for="email" class="control-label block text-sm font-medium text-gray-700 dark:text-gray-300">{{ trans('email') }}</label>
            <input
                type="email"
                name="email"
                id="email"
                class="form-control mt-1 w-full"
                placeholder="{{ trans('email') }}"
                required
                autofocus
                value="{{ old('email') }}"
            />
            @error('email') <p class="text-sm text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="form-group" x-data="{ show: false }">
            <label for="password" class="control-label block text-sm font-medium text-gray-700 dark:text-gray-300">{{ trans('password') }}</label>
            <div class="relative mt-1">
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control w-full pr-10"
                    placeholder="{{ trans('password') }}"
                    required
                />
                <button type="button" x-on:click="show = ! show" x-bind:aria-pressed="show" class="absolute inset-y-0 right-0 pr-2 flex items-center">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="fi-icon" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                        <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                        <path fill-rule="evenodd"
                              d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                              clip-rule="evenodd" />
                    </svg>
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="fi-icon" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                        <path fill-rule="evenodd"
                              d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z"
                              clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            @error('password') <p class="text-sm text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <input type="hidden" name="btn_login" value="true">

        <div class="flex gap-2 items-center">
            <button type="submit" class="fi-btn fi-btn-primary w-full inline-flex items-center justify-center">
                <svg class="fi-icon fi-size-sm mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a10 10 0 100 20 10 10 0 000-20zM11 11V7h2v4h3v2h-3v4h-2v-4H8v-2h3z" />
                </svg>
                {{ trans('login') }}
            </button>

            <a href="{{ route('sessions.passwordreset') }}" class="filament-link inline-block text-sm ml-2">
                {{ trans('forgot_your_password') }}
            </a>
        </div>
    </form>
@endsection
