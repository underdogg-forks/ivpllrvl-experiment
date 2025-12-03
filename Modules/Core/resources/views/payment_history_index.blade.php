<div id="headerbar">
    <h1 class="headerbar-title">{{ trans('payment_history') }}</h1>
</div>

<div id="content">

    <div class="row">
        <div class="col-xs-12 col-md-6 col-md-offset-3">

            <?php $this->layout->load_view('layout/alerts'); ?>

            <div id="report_options" class="panel panel-default">

                <div class="panel-heading">
                    <i class="fa fa-print"></i>
                    {{ trans('report_options') }}
                </div>

                <div class="panel-body">

                    <form method="post" action="{{ route($this->uri->uri_string()) }}"
                        {{ get_setting('reports_in_new_tab', false) ? 'target="_blank"' : '' }}>

                        <?php _csrf_field(); ?>

                        <div class="form-group has-feedback">
                            <label for="from_date">
                                {{ trans('from_date') }}
                            </label>

                            <div class="input-group">
                                <input name="from_date" id="from_date"
                                       class="form-control datepicker">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar fa-fw"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group has-feedback">
                            <label for="to_date">
                                {{ trans('to_date') }}
                            </label>

                            <div class="input-group">
                                <input name="to_date" id="to_date" class="form-control datepicker">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar fa-fw"></i>
                                </span>
                            </div>
                        </div>

                        <input type="submit" class="btn btn-success" name="btn_submit"
                               value="{{ trans('run_report') }}">

                    </form>

                </div>

            </div>

        </div>
    </div>

</div>
