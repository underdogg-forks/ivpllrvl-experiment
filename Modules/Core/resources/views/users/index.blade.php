@extends('core::layouts.app')

@section('content')
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <h1 class="headerbar-title text-xl font-bold">{{ trans('users') }}</h1>

    <div class="headerbar-item">
        <a class="btn btn-sm btn-primary inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 dark:hover:bg-blue-600" href="{{ route('users.form') }}">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>

    <div class="headerbar-item">
        {!! pager(route('users.index'), $users) !!}
    </div>

</div>

<div id="content" class="table-content">

    @include('core::layout.alerts')

    <div id="filter_results">
        @include('core::users.partial_users_table')
    </div>

</div>
@endsection
