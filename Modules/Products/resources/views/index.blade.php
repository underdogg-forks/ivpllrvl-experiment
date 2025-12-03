@extends('core::layouts.app')

@section('content')
<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('products') }}</h1>

    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="{{ route('products.form') }}">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>

    <div class="headerbar-item pull-right">
        {!! $products->links() !!}
    </div>

</div>

<div id="content" class="table-content">

    @include('core::alerts')

    <div id="filter_results">
        @include('products::partial_products_table')
    </div>

</div>
@endsection

