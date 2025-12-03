<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('payment_methods') }}</h1>

    <div class="headerbar-item pull-right">
        <a class="btn btn-sm btn-primary" href="{{ route('payment-methods.form') }}">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>

    <div class="headerbar-item pull-right">
        {!! pager(route('payment-methods.index'), $payment_methods) !!}
    </div>

</div>

<div id="content" class="table-content">

    @include('core::alerts')

    <div class="table-responsive">
        <table class="table table-hover table-striped">

            <thead>
            <tr>
                <th>{{ trans('payment_method') }}</th>
                <th>{{ trans('options') }}</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($payment_methods as $payment_method)
                <tr>
                    <td>{{ $payment_method->payment_method_name }}</td>
                    <td>
                        <div class="options btn-group">
                            <a class="btn btn-default btn-sm dropdown-toggle"
                               data-toggle="dropdown" href="#">
                                <i class="fa fa-cog"></i>
                                {{ trans('options') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="{{ route('payment-methods.form', $payment_method->payment_method_id) }}">
                                        <i class="fa fa-edit fa-margin"></i>
                                        {{ trans('edit') }}
                                    </a>
                                </li>
                                <li>
                                    <form action="{{ route('payment-methods.delete', $payment_method->payment_method_id) }}"
                                          method="POST">
                                        @csrf
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
            @endforeach
            </tbody>

        </table>
    </div>

</div>
