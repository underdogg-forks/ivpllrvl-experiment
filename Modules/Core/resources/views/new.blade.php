@extends('core::layouts.app')

@section('content')
@php
$href  = route('custom_fields.form', $field->custom_field_id);
$link  = '<a href="' . $href . '" class="btn btn-default inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50"><i class="fa fa-edit fa-margin"></i> ' . htmlspecialchars($field->custom_field_label) . '</a>';
$alpha = strtr(mb_strtolower($field->custom_field_type), ['-' => '_']);
@endphp
<form method="post">

    @csrf

    <div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
        <h1 class="headerbar-title text-xl font-bold">{{ trans('custom_values_new') }}</h1>
        @include('core::layout.header_buttons')
        <div class="hidden sm:block headerbar-item">
            <div class="badge px-2 py-1 rounded-md text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 mr-2">{{ trans('table') }}: {{ trans($table) }}</div>
            <div class="badge px-2 py-1 rounded-md text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 mr-2">{{ trans('position') }}: {{ $position }}</div>
            <div class="badge px-2 py-1 rounded-md text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 mr-2">{{ trans('type') }}: {{ trans($alpha) }}</div>
            {{ trans('field') }}: {!! $link !!}
        </div>
    </div>

    <div id="content">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6 md:col-start-4">

               @include('core::layout.alerts')

                <div class="form-group mb-4">
                    <label for="custom_values_value" class="block mb-2 font-medium">{{ trans('value') }}:</label>
                    <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white" name="custom_values_value" id="custom_values_value" required>
                </div>

                <div class="sm:hidden">
                    <div class="form-group mb-4">{{ trans('field') }}: {!! $link !!}</div>
                    <div class="form-group mb-4 badge px-2 py-1 rounded-md text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">{{ trans('table') }}: {{ trans($table) }}</div>
                    <div class="form-group mb-4 badge px-2 py-1 rounded-md text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">{{ trans('position') }}: {{ $position }}</div>
                    <div class="form-group mb-4 badge px-2 py-1 rounded-md text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">{{ trans('type') }}: {{ trans($alpha) }}</div>
                </div>
            </div>
        </div>

    </div>

</form>
@endsection
