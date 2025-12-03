<div class="container mx-auto px-4 py-8">
    <div class="install-panel max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-8">

        <h1 id="logo" class="text-4xl font-bold text-center mb-6"><span>InvoicePlane</span></h1>

        <form method="post" action="{{ route('setup.calculation_info') }}">

            @csrf

            <h2 class="text-2xl font-semibold mb-4">{{ trans('setup_calculation_info') }}</h2>

            <p class="mb-4">
                {{ trans('setup_calculation_info_message') }}
            </p>

            <p class="alert alert-warning bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded mb-4">
                {{ trans('setup_calculation_info_note') }}
            </p>

            <input type="submit" class="btn btn-success inline-flex items-center gap-2 px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 dark:hover:bg-green-600 mr-2" name="btn_agree"
                   value="{{ trans('setup_calculation_info_btn_agree') }}">

            <input type="submit" class="btn btn-warning inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 dark:bg-yellow-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-yellow-700 dark:hover:bg-yellow-600" name="btn_continue"
                   value="{{ trans('setup_calculation_info_btn_disagree') }}">

        </form>
    </div>
</div>
