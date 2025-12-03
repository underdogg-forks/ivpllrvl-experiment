<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('sales_by_date') }}</h1>
</div>

<div id="content">

    <div class="row">
        <div class="col-xs-12 col-md-6 col-md-offset-3">

            <?php $this->layout->load_view('layout/alerts'); ?>

            <div id="report_options" class="fi-section">

                <div class="fi-section-header">
                    <i class="fa fa-print fa-margin"></i>
                    {{ trans('report_options') }}
                </div>

                <div class="fi-section-body">

                    <form method="post" action="{{ route($this->uri->uri_string()) }}"
                        {{ get_setting('reports_in_new_tab', false) ? 'target="_blank"' : '' }}>

                        <?php _csrf_field(); ?>

                        <div class="fi-field-wrp has-feedback">
                            <label for="from_date">
                                {{ trans('from_date') }}
                            </label>

                            <div class="input-group">
                                <input name="from_date" id="from_date" class="fi-input datepicker">
                                <span class="input-group-addon">
                            <i class="fa fa-calendar fa-fw"></i>
                        </span>
                            </div>
                        </div>

                        <div class="fi-field-wrp has-feedback">
                            <label for="to_date">
                                {{ trans('to_date') }}
                            </label>

                            <div class="input-group">
                                <input name="to_date" id="to_date" class="fi-input datepicker">
                                <span class="input-group-addon">
                            <i class="fa fa-calendar fa-fw"></i>
                        </span>
                            </div>
                        </div>


                        <div class="clearfix">
                            <div class="col-xs-12 col-md-2" style="margin-right:10px; padding-left:0px;">
                                <label for="minQuantity">
                                    {{ trans('min_quantity') }}
                                </label>

                                <div>
                                    <input type="number" id="minQuantity" name="minQuantity" min="0"
                                           class="fi-input">
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-2" style=padding-left:0px;>
                                <label for="maxQuantity">
                                    {{ trans('max_quantity') }}
                                </label>

                                <div>
                                    <input type="number" id="maxQuantity" name="maxQuantity" min="0"
                                           class="fi-input">
                                </div>
                            </div>
                        </div>

                        <div class="fi-field-wrp">
                            <div class="checkbox">
                                <label for="checkboxTax">
                                    <input type="checkbox" id="checkboxTax" name="checkboxTax">
                                    {{ trans('values_with_taxes') }}
                                </label>
                            </div>
                        </div>

                        <input type="submit" class="fi-btn-success" name="btn_submit"
                               value="{{ trans('run_report') }}">

                    </form>
                </div>
            </div>

        </div>
