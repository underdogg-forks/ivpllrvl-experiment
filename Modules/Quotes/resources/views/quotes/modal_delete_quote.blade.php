<div id="delete-quote" class="modal modal-lg" role="dialog" aria-labelledby="modal_delete_quote" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="fi-section-title">{{ trans('delete_quote') }}</h4>
        </div>
        <div class="modal-body">

            <div class="alert alert-danger">{{ trans('delete_quote_warning') }}</div>

        </div>
        <div class="modal-footer">
            <form action="{{ route('quotes.delete', $quote->quote_id) }}"
                  method="POST">
                @csrf

                <div class="btn-group">
                    <button type="submit" class="fi-btn-danger ajax-loader">
                        <i class="fa fa-trash-o fa-margin"></i> {{ trans('confirm_deletion') }}
                    </button>
                    <a href="#" class="fi-btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> {{ trans('cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
