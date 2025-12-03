<div class="table-responsive">
    <table class="table table-hover table-striped">

        <thead>
        <tr>
            <th>{{ trans('invoice') }}</th>
            <th>{{ trans('created') }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($invoices_archive as $invoice)
            <tr>
                <td>
                    <a href="{{ route('invoices.download', basename($invoice)) }}"
                       title="{{ trans('invoice') }}">
                        {{ basename($invoice) }}
                    </a>
                </td>

                <td>
                    {{ date('F d Y H:i:s.', filemtime($invoice)) }}
                </td>

            </tr>
        @endforeach
        </tbody>

    </table>
</div>
