@extends('core::layouts.app')

@section('content')
<div id="headerbar" class="headerbar">
    <h1 class="headerbar-title">{{ trans('users') }}</h1>

    <div class="headerbar-item">
        <a class="btn-create" href="{{ route('users.form') }}">
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
