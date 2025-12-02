<div id="headerbar">

    <h1 class="headerbar-title">{{ trans('invoices') }}</h1>

    <div class="headerbar-item pull-right">
        <button type="button" class="btn btn-default btn-sm submenu-toggle hidden-lg"
                data-toggle="collapse" data-target="#ip-submenu-collapse">
            <i class="fa fa-bars"></i> {{ trans('submenu') }}
        </button>
        <a class="create-invoice btn btn-sm btn-primary" href="#">
            <i class="fa fa-plus"></i> {{ trans('new') }}
        </a>
    </div>

    <div class="headerbar-item pull-right visible-lg">
        <?php echo pager(site_url('invoices/status/' . $this->uri->segment(3)), $invoices); ?>
    </div>

    <div class="headerbar-item pull-right visible-lg">
        <div class="btn-group btn-group-sm index-options">
            <a href="<?php echo site_url('invoices/status/all'); ?>"
               class="btn <?php echo $status == 'all' ? 'btn-primary' : 'btn-default' ?>">
                {{ trans('all') }}
            </a>
            <a href="<?php echo site_url('invoices/status/draft'); ?>"
               class="btn <?php echo $status == 'draft' ? 'btn-primary' : 'btn-default' ?>">
                {{ trans('draft') }}
            </a>
            <a href="<?php echo site_url('invoices/status/sent'); ?>"
               class="btn <?php echo $status == 'sent' ? 'btn-primary' : 'btn-default' ?>">
                {{ trans('sent') }}
            </a>
            <a href="<?php echo site_url('invoices/status/viewed'); ?>"
               class="btn <?php echo $status == 'viewed' ? 'btn-primary' : 'btn-default' ?>">
                {{ trans('viewed') }}
            </a>
            <a href="<?php echo site_url('invoices/status/paid'); ?>"
               class="btn <?php echo $status == 'paid' ? 'btn-primary' : 'btn-default' ?>">
                {{ trans('paid') }}
            </a>
            <a href="<?php echo site_url('invoices/status/overdue'); ?>"
               class="btn <?php echo $status == 'overdue' ? 'btn-primary' : 'btn-default' ?>">
                {{ trans('overdue') }}
            </a>
        </div>
    </div>

</div>

<div id="submenu">
    <div class="collapse clearfix" id="ip-submenu-collapse">

        <div class="submenu-row">
            <?php echo pager(site_url('invoices/status/' . $this->uri->segment(3)), $invoices); ?>
        </div>

        <div class="submenu-row">
            <div class="btn-group btn-group-sm index-options">
                <a href="<?php echo site_url('invoices/status/all'); ?>"
                   class="btn <?php echo $status == 'all' ? 'btn-primary' : 'btn-default' ?>">
                    {{ trans('all') }}
                </a>
                <a href="<?php echo site_url('invoices/status/draft'); ?>"
                   class="btn  <?php echo $status == 'draft' ? 'btn-primary' : 'btn-default' ?>">
                    {{ trans('draft') }}
                </a>
                <a href="<?php echo site_url('invoices/status/sent'); ?>"
                   class="btn  <?php echo $status == 'sent' ? 'btn-primary' : 'btn-default' ?>">
                    {{ trans('sent') }}
                </a>
                <a href="<?php echo site_url('invoices/status/viewed'); ?>"
                   class="btn  <?php echo $status == 'viewed' ? 'btn-primary' : 'btn-default' ?>">
                    {{ trans('viewed') }}
                </a>
                <a href="<?php echo site_url('invoices/status/paid'); ?>"
                   class="btn  <?php echo $status == 'paid' ? 'btn-primary' : 'btn-default' ?>">
                    {{ trans('paid') }}
                </a>
                <a href="<?php echo site_url('invoices/status/overdue'); ?>"
                   class="btn  <?php echo $status == 'overdue' ? 'btn-primary' : 'btn-default' ?>">
                    {{ trans('overdue') }}
                </a>
            </div>
        </div>

    </div>
</div>

<div id="content" class="table-content">
    <div id="filter_results">
        <?php $this->layout->load_view('invoices/partial_invoice_table'); ?>
    </div>
</div>
