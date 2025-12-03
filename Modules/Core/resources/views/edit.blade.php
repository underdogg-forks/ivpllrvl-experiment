<?php
$href  = route('custom-fields.form', ['custom_field_id' => $value->custom_field_id]);
$link  = anchor($href, '<i class="fa fa-edit fa-margin"></i> ' . htmlsc($value->custom_field_label), ' class="btn fi-size-sm fi-btn-secondary"');
$alpha = strtr(mb_strtolower($value->custom_field_type), ['-' => '_']);
$table = strtr($value->custom_field_table, ['ip_' => '', '_custom' => '']);
?>
<form method="post">

    <?php _csrf_field(); ?>

    <div id="headerbar">
        <h1 class="headerbar-title">{{ trans('custom_values_edit') }}</h1>
        <?php $this->layout->load_view('layout/header_buttons'); ?>
        <div class="headerbar-item pull-right">
            <a href="{{ route('custom-values.field', ['custom_field_id' => $value->custom_field_id]) ?>" class="btn fi-size-sm fi-btn-secondary">
                                <i class="fa fa-eye fa-margin"></i> {{ trans('values') }}</a>
        </div>
        <div class="visible-sm visible-md visible-lg headerbar-item pull-right">
            <div class="badge">{{ trans('table') }}: <?php _trans($table) }}</div>
            <div class="badge">{{ trans('position') }}: {{ $position }}</div>
            <div class="badge">{{ trans('type') }}: {{ trans($alpha) }}</div>
            {{ trans('field') }}: {{ $link }}
        </div>
    </div>

    <div id="content">

        <div class="row">
            <div class="col-xs-12 col-md-6 col-md-offset-3">

                <?php $this->layout->load_view('layout/alerts'); ?>

                <div class="fi-field-wrp">
                    <label for="custom_values_value">{{ trans('label') }}:</label>
                    <input type="text" name="custom_values_value" id="custom_values_value" class="fi-input"
                           value="<?php _htmlsc($value->custom_values_value); ?>" required>
                </div>
                <hr>

                <div class="row visible-xs">
                    <div class="col-xs-12">
                        <div class="fi-field-wrp">{{ trans('field') }}: {{ $link }}</div>
                    </div>

                    <div class="col-xs-12">
                        <div class="fi-field-wrp badge">{{ trans('table') }}: {{ trans($table) }}</div>
                    </div>

                    <div class="col-xs-12">
                        <div class="fi-field-wrp badge">{{ trans('position') }}: {{ $position }}</div>
                    </div>

                    <div class="col-xs-12">
                        <div class="fi-field-wrp badge">{{ trans('type') }}: {{ trans($alpha) }}</div>
                    </div>
                </div>

            </div>

<?php $this->layout->load_view('layout/partial/custom_field_usage_list', ['custom_field_table' => $value->custom_field_table]); ?>

        </div>
    </div>

</form>
