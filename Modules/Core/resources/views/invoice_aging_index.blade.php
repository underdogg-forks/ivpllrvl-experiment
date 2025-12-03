@extends('core::components.layouts.app')

@section('content')
<div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
    <h1 class="headerbar-title text-xl font-bold">{{ trans('invoice_aging') }}</h1>
</div>

<div id="content">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-6 md:col-start-4">

            @include('core::layout.alerts')

            <div id="report_options" class="panel panel-default bg-white dark:bg-gray-800 rounded-lg shadow">

                <div class="panel-heading px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold">
                    <i class="fa fa-print"></i>
                    {{ trans('report_options') }}
                </div>

                <div class="panel-body p-4">
                    <form method="post" action="{{ route('reports.invoice_aging') }}"
                        {{ get_setting('reports_in_new_tab', false) ? 'target="_blank"' : '' }}>

                        @csrf

                        <input type="submit" class="btn btn-success inline-flex items-center gap-2 px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 dark:hover:bg-green-600"
                               name="btn_submit" value="{{ trans('run_report') }}">

                    </form>
                </div>

            </div>

        </div>
    </div>

</div>
@endsection
