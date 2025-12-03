<div class="container mx-auto px-4 py-8">
    <div class="install-fi-section max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-8">

        <h1 id="logo" class="text-4xl font-bold text-center mb-6"><span>InvoicePlane</span></h1>

        <form method="post" class="form-horizontal" action="{{ route('setup.upgrade-tables') }}">

            @csrf

            <legend class="text-2xl font-semibold mb-4">{{ trans('setup_upgrade_tables') }}</legend>

@if($errors)
                <p class="mb-4">
                    {{ trans('setup_upgrade_message') }}
                </p>

@foreach($errors as $error)
                    <p class="mb-2">
                        <i class="fa fa-close text-danger fa-margin"></i>
                        {{ $error }}
                    </p>
@endforeach

@else
                <p class="mb-4">
                    <i class="fa fa-check text-success fa-margin"></i>
                    {{ trans('setup_upgrade_success') }}
                </p>
@endif

@if($errors)
                <input type="submit" class="fi-btn-danger inline-flex items-center gap-2 px-4 py-2 bg-red-600 dark:bg-red-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 dark:hover:bg-red-600" name="btn_try_again"
                       value="{{ trans('try_again') }}">
@else
                <input type="submit" class="fi-btn-success inline-flex items-center gap-2 px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 dark:hover:bg-green-600" name="btn_continue"
                       value="{{ trans('continue') }}">
@endif

        </form>

    </div>
</div>
<script>window.scrollTo(0, document.body.scrollHeight);</script>
