<div id="fullpage-loader" class="hidden">
    <div class="loader-content">
        <i id="loader-icon" class="fa fa-cog fa-spin"></i>
        <div id="loader-error" class="hidden">
            {{ trans('loading_error') }}<br/>
            <a href="https://wiki.invoiceplane.com/{{ trans('cldr') }}/1.0/general/faq"
               class="fi-btn-primary fi-size-sm" target="_blank">
                <i class="fa fa-support"></i> {{ trans('loading_error_help') }}
            </a>
        </div>
    </div>
    <div class="text-right">
        <button type="button" class="fullpage-loader-close btn fi-btn-link tip" aria-label="{{ trans('close') }}"
                title="{{ trans('close') }}" data-placement="left">
            <span aria-hidden="true"><i class="fa fa-close"></i></span>
        </button>
    </div>
</div>
