<form method="post" class="form-horizontal">

    <div id="headerbar" class="flex flex-wrap justify-between items-center mb-4">
        <h1 class="headerbar-title text-xl font-bold">{{ trans('email_invoice') }}</h1>
    </div>

    <div id="content">
        <div class="alert alert-warning bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded">{{ trans('email_not_configured') }}</div>
    </div>

</form>
