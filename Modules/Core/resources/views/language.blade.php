<div class="container mx-auto px-4 py-8">
    <div class="install-panel max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-8">

        <h1 id="logo" class="text-4xl font-bold text-center mb-6"><span>InvoicePlane</span></h1>

        <form method="post" action="{{ route('setup.language') }}">

            @csrf

            <legend class="text-2xl font-semibold mb-4">{{ trans('setup_choose_language') }}</legend>

            <p class="mb-4">{{ trans('setup_choose_language_message') }}</p>

            <select name="ip_lang" class="form-control simple-select w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white mb-4">
@foreach($languages as $language)
                <option value="{{ $language }}"{{ $language == 'english' ? ' selected="selected"' : '' }}>{{ ucfirst(str_replace('/', '', $language)) }}</option>
@endforeach
            </select>

            <br/>

            <input class="btn btn-success inline-flex items-center gap-2 px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 dark:hover:bg-green-600" type="submit" name="btn_continue" value="{{ trans('continue') }}">

        </form>

    </div>
</div>
