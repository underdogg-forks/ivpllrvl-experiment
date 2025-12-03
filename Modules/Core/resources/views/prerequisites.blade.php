<div class="container">
    <div class="install-panel">

        <h1 id="logo"><span>InvoicePlane</span></h1>
        <form method="post" class="form-horizontal" action="{{ route($this->uri->uri_string()) }}">

            <?php _csrf_field(); ?>

            <legend>{{ trans('setup_prerequisites') }}</legend>

            <p>{{ trans('setup_prerequisites_message') }}</p>

<?php
foreach ($basics as $basic) {
    if (isset($basic['warning'])) { ?>
            <p><i class="fa fa-exclamation text-warning fa-margin"></i> {{ $basic['message'] }}</p>
@elseif($basic['success'] == 1)
            <p><i class="fa fa-check text-success fa-margin"></i> {{ $basic['message'] }}</p>
<?php } else {
    $errors = true;
    ?>
            <p><i class="fa fa-close text-danger fa-margin"></i> {{ $basic['message'] }}</p>
<?php }
} ?>

            <br>

<?php foreach ($writables as $writable) {
    if ($writable['success'] === 1) { ?>
            <p><i class="fa fa-check text-success fa-margin"></i> {{ $writable['message'] }}</p>
@else
            <p><i class="fa fa-close text-danger fa-margin"></i> {{ $writable['message'] }}</p>
<?php }
} ?>

@if($errors)
            <a href="javascript:history.go(0)" class="btn btn-danger">
                {{ trans('try_again') }}
            </a>
@else
            <input class="btn btn-success" type="submit" name="btn_continue"
                   value="{{ trans('continue') }}">
@endif
        </form>

    </div>
</div>
