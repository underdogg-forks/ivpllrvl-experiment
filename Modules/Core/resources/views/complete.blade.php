<div class="container mx-auto px-4 py-8">
    <div class="install-fi-section max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-8">

        <h1 id="logo" class="text-4xl font-bold text-center mb-6"><span>InvoicePlane</span></h1>

        <h2 class="text-2xl font-semibold mb-4">{{ trans('setup_complete') }}</h2>

        <p class="mb-4">
            {{ trans('setup_complete_message') }}
        </p>

        <p class="alert alert-info bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-4 py-3 rounded mb-4">
            {{ trans('setup_complete_support_note') }}
        </p>

        <p class="alert alert-warning bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded mb-4">
            {{ trans('setup_complete_secure_setup') }}
        </p>

@if(session('setup_notice'))
    @php $setup_notice = session('setup_notice'); @endphp
        <div class="alert {{ $setup_notice['type'] }} px-4 py-3 rounded mb-4">
            {!! $setup_notice['content'] !!}
        </div>
@endif

        <a href="{{ route('sessions.login') }}" class="fi-btn-success inline-flex items-center gap-2 px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 dark:hover:bg-green-600">
            <i class="fa fa-check fa-margin"></i> {{ trans('login') }}
        </a>

    </div>
</div>
