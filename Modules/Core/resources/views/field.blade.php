<?php
$href  = route('custom_fields/form/' . $field->custom_field_id);
$link  = anchor($href, '<i class="fa fa-edit fa-margin"></i> ' . htmlsc($field->custom_field_label), ' class="btn btn-sm btn-default"');
$alpha = strtr(mb_strtolower($field->custom_field_type), ['-' => '_']);
$table = strtr($field->custom_field_table, ['ip_' => '', '_custom' => '']);
?>

<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('custom_values') }}</h1>

    <div class="headerbar-item pull-right">
        <div class="btn-group btn-group-sm">
            <a class="btn btn-default" href="{{ route('custom-values.index') }}">
                <i class="fa fa-arrow-left"></i> {{ trans('back') }}
            </a>
            <a class="btn btn-primary" href="{{ route('custom_values/create/' . $id) }}">
                <i class="fa fa-plus"></i> {{ trans('new') }}
            </a>
        </div>
    </div>
    <div class="visible-sm visible-md visible-lg headerbar-item pull-right">
        <div class="badge">{{ trans('table') }}: {{ trans($table) }}</div>
        <div class="badge">{{ trans('position') }}: {{ $position }}</div>
        <div class="badge">{{ trans('type') }}: {{ trans($alpha) }}</div>
        {{ trans('field') }}: {{ $link }}
    </div>
</div>

<div id="content">
    <?php $this->layout->load_view('layout/alerts'); ?>

    <div class="row">
        <div class="col-xs-12 col-md-6 col-md-offset-3">

            <div class="form-group">
                <div id="filter_results">
<?php
$this->layout->load_view('custom_values/partial_custom_values_field');
?>
                </div>
            </div>

            <div class="row visible-xs">
                <div class="col-xs-12">
                    <div class="form-group">{{ trans('field') }}: {{ $link }}</div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group badge">{{ trans('table') }}: {{ trans($table) }}</div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group badge">{{ trans('position') }}: {{ $position }}</div>
                </div>

                <div class="col-xs-12">
                    <div class="form-group badge">{{ trans('type') }}: {{ trans($alpha) }}</div>
                </div>
            </div>

        </div>

<?php
$this->layout->load_view('layout/partial/custom_field_usage_list', ['custom_field_table' => $field->custom_field_table]);
?>

    </div>
</div>
