<div class="headerbar-item">
    <div class="btn-group btn-group-sm flex gap-2">
@if(!isset($hide_submit_button))
        <button id="btn-submit" name="btn_submit" class="fi-btn-success ajax-loader inline-flex items-center gap-2 px-3 py-1.5 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 dark:hover:bg-green-600" value="1">
            <i class="fa fa-check"></i> {{ trans('save') }}
        </button>
@endif
@if(!isset($hide_cancel_button))
    @php
        $attribute_cancel = empty($attribute_cancel) ? 'onclick="window.history.back()"' : $attribute_cancel;
    @endphp
        <button type="button" {!! $attribute_cancel !!} id="btn-cancel" name="btn_cancel" class="fi-btn-danger ajax-loader inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 dark:bg-red-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 dark:hover:bg-red-600" value="1">
            <i class="fa fa-times"></i> {{ trans('cancel') }}
        </button>
@endif
    </div>
</div>
