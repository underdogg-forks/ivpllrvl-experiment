@extends('core::layouts.app')

@section('content')
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <h1 class="headerbar-title text-xl font-bold">{{ trans('import_data') }}</h1>
</div>

<div id="content">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-6 md:col-start-4">

            @include('core::layout.alerts')

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5 class="font-semibold">{{ trans('import_from_csv') }}</h5>
                </div>

                <div class="panel-body p-4">
                    <form method="post" action="{{ route('import.index') }}">

                        @csrf
@foreach($files as $file)
                        <div class="checkbox mb-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="files[]" value="{{ $file }}" class="mr-2">
                                {{ $file }}
                            </label>
                        </div>
@endforeach
                        <input type="submit" class="btn btn-default inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 mt-4" name="btn_submit" value="{{ trans('import') }}">

                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
