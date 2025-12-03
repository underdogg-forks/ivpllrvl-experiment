@foreach ($client_notes as $client_note)
    <div class="panel panel-default small">
        <div class="panel-body">
            {!! nl2br(e($client_note->client_note)) !!}
        </div>
        <div class="panel-footer text-muted">
            {{ date_from_mysql($client_note->client_note_date, true) }}
            <span data-id="{{ $client_note->client_note_id }}" class="delete_client_note pull-right btn btn-xs btn-danger">
                <i class="fa fa-trash-o"></i> {{ trans('delete') }}
            </span>
        </div>
    </div>
@endforeach
