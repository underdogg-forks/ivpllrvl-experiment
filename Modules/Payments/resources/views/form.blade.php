<form method="post" class="form-horizontal">

    @csrf

    <div id="headerbar">
        <h1 class="headerbar-title">{{ trans('payment_method_form') }}</h1>
        @include('core::header_buttons')
    </div>

    <div id="content">

        @include('core::alerts')

        <input class="hidden" name="is_update" type="hidden"
            value="{{ isset($payment_method->payment_method_id) && $payment_method->payment_method_id ? '1' : '0' }}">

        <div class="fi-field-wrp">
            <div class="col-xs-12 col-sm-2 text-right text-left-xs">
                <label for="payment_method_name" class="control-label">
                    {{ trans('payment_method') }}:
                </label>
            </div>
            <div class="col-xs-12 col-sm-6">
                <input type="text" name="payment_method_name" id="payment_method_name" class="fi-input"
                       value="{{ old('payment_method_name', $payment_method->payment_method_name ?? '') }}" required>
            </div>
        </div>

    </div>

</form>
@endsection
