@extends('core::layouts.app')

@section('content')
<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('import_data') }}</h1>

    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="{{ route('import.form') }}">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>

    <div class="headerbar-item pull-right">
        {{ pager(route('import.index'), $imports) }}
    </div>

</div>

<div id="content" class="table-content">

    {{ $this->layout->load_view('layout/alerts') }}

    <div class="table-responsive">
        <table class="table table-striped">

            <thead>
            <tr>
                <th>{{ trans('id') }}</th>
                <th>{{ trans('date') }}</th>
                <th>{{ trans('clients') }}</th>
                <th>{{ trans('invoices') }}</th>
                <th>{{ trans('invoice_items') }}</th>
                <th>{{ trans('payments') }}</th>
                <th>{{ trans('options') }}</th>
            </tr>
            </thead>

            <tbody>
@foreach($imports as $import)
                <tr>
                    <td>{{ $import->import_id }}</td>
                    <td>{{ $import->import_date }}</td>
                    <td>{{ $import->num_clients }}</td>
                    <td>{{ $import->num_invoices }}</td>
                    <td>{{ $import->num_invoice_items }}</td>
                    <td>{{ $import->num_payments }}</td>
                    <td>
                        <div class="options btn-group btn-group-sm">
                            <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-cog"></i> {{ trans('options') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('import.delete', ['import_id' => $import->import_id]) }}"
                                          method="POST">
                                        <?php _csrf_field(); ?>
                                        <button type="submit" class="dropdown-button"
                                                onclick="return confirm('{{ trans('delete_record_warning') }}');">
                                            <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
@endif
            </tbody>

        </table>
    </div>

</div>
@endsection
