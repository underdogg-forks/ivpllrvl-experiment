<nav class="navbar navbar-inverse" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#ip-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <?php echo trans('menu') ?> &nbsp; <i class="fa fa-bars"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="ip-navbar-collapse">
            <ul class="nav navbar-nav">
                <li><?php echo anchor('dashboard', trans('dashboard'), 'class="hidden-md"') ?>
                    <?php echo anchor('dashboard', '<i class="fa fa-dashboard"></i>', 'class="visible-md-inline-block"') ?>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-caret-down"></i> &nbsp;
                        <span class="hidden-md">{{ trans('clients') }}</span>
                        <i class="visible-md-inline fa fa-users"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>{{ anchor('clients/form', trans('add_client')) }}</li>
                        <li>{{ anchor('clients/index', trans('view_clients')) }}</li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-caret-down"></i> &nbsp;
                        <span class="hidden-md">{{ trans('quotes') }}</span>
                        <i class="visible-md-inline fa fa-file"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="#" class="create-quote">{{ trans('create_quote') }}</a></li>
                        <li>{{ anchor('quotes/index', trans('view_quotes')) }}</li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-caret-down"></i> &nbsp;
                        <span class="hidden-md">{{ trans('invoices') }}</span>
                        <i class="visible-md-inline fa fa-file-text"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="#" class="create-invoice">{{ trans('create_invoice') }}</a></li>
                        <li>{{ anchor('invoices/index', trans('view_invoices')) }}</li>
                        <li>{{ anchor('invoices/recurring/index', trans('view_recurring_invoices')) }}</li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-caret-down"></i> &nbsp;
                        <span class="hidden-md">{{ trans('payments') }}</span>
                        <i class="visible-md-inline fa fa-credit-card"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>{{ anchor('payments/form', trans('enter_payment')) }}</li>
                        <li>{{ anchor('payments/index', trans('view_payments')) }}</li>
                        <li>{{ anchor('payments/online_logs', trans('view_payment_logs')) }}</li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-caret-down"></i> &nbsp;
                        <span class="hidden-md">{{ trans('products') }}</span>
                        <i class="visible-md-inline fa fa-database"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>{{ anchor('products/form', trans('create_product')) }}</li>
                        <li>{{ anchor('products/index', trans('view_products')) }}</li>
                        <li>{{ anchor('families/index', trans('view_product_families')) }}</li>
                        <li>{{ anchor('units/index', trans('view_product_units')) }}</li>
                    </ul>
                </li>

                <li class="dropdown{{ get_setting('projects_enabled') == 1 ? '' : ' hidden' }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-caret-down"></i> &nbsp;
                        <span class="hidden-md">{{ trans('tasks') }}</span>
                        <i class="visible-md-inline fa fa-check-square-o"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>{{ anchor('tasks/form', trans('create_task')) }}</li>
                        <li>{{ anchor('tasks/index', trans('view_tasks')) }}</li>
                        <li role="separator" class="divider"></li>
                        <li>{{ anchor('projects/form', trans('create_project')) }}</li>
                        <li>{{ anchor('projects/index', trans('view_projects')) }}</li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-caret-down"></i> &nbsp;
                        <span class="hidden-md">{{ trans('reports') }}</span>
                        <i class="visible-md-inline fa fa-bar-chart"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>{{ anchor('reports/invoice_aging', trans('invoice_aging')) }}</li>
                        <li>{{ anchor('reports/payment_history', trans('payment_history')) }}</li>
                        <li>{{ anchor('reports/sales_by_client', trans('sales_by_client')) }}</li>
                        <li>{{ anchor('reports/sales_by_year', trans('sales_by_date')) }}</li>
                        <li>{{ anchor('reports/invoices_per_client', trans('invoices_per_client')) }}</li>
                    </ul>
                </li>

            </ul>

            @if(isset($filter_display) && $filter_display == true)
                <?php $this->layout->load_view('filter/jquery_filter'); ?>
                <form class="navbar-form navbar-left" role="search" onsubmit="return false;">
                    <div class="form-group">
                        <input id="filter" type="text" class="search-query form-control"
                               placeholder="{{ $filter_placeholder }}">
                    </div>
                </form>
            @endif
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="https://wiki.invoiceplane.com/" target="_blank"
                       class="tip icon" title="{{ trans('documentation') }}"
                       data-placement="bottom">
                        <i class="fa fa-question-circle"></i>
                        <span class="visible-xs">&nbsp;{{ trans('documentation') }}</span>
                    </a>
                </li>

                <li class="dropdown">
                    <a href="#" class="tip icon dropdown-toggle" data-toggle="dropdown"
                       title="{{ trans('settings') }}"
                       data-placement="bottom">
                        <i class="fa fa-cogs"></i>
                        <span class="visible-xs">&nbsp;{{ trans('settings') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>{{ anchor('custom_fields/index', trans('custom_fields')) }}</li>
                        <li>{{ anchor('email_templates/index', trans('email_templates')) }}</li>
                        <li>{{ anchor('invoice_groups/index', trans('invoice_groups')) }}</li>
                        <li>{{ anchor('invoices/archive', trans('invoice_archive')) }}</li>
                        <!-- // temporarily disabled
                        <li>{{ anchor('item_lookups/index', trans('item_lookups')) }}</li>
                        -->
                        <li>{{ anchor('payment_methods/index', trans('payment_methods')) }}</li>
                        <li>{{ anchor('tax_rates/index', trans('tax_rates')) }}</li>
                        <li>{{ anchor('users/index', trans('user_accounts')) }}</li>
                        <li class="divider hidden-xs hidden-sm"></li>
                        <li>{{ anchor('settings', trans('system_settings')) }}</li>
                        <li>{{ anchor('import', trans('import_data')) }}</li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('users/form/'
                        . $this->session->userdata('user_id')) }}"
                       class="tip icon" data-placement="bottom"
                       title="<?php
                        _htmlsc($this->session->userdata('user_name'));
                if ($this->session->userdata('user_company')) {
                    echo ' (' . htmlsc($this->session->userdata('user_company')) . ')';
                }
                ?>">
                        <i class="fa fa-user"></i>
                        <span class="visible-xs">&nbsp;<?php
                    _htmlsc($this->session->userdata('user_name'));
                if ($this->session->userdata('user_company')) {
                    echo ' (' . htmlsc($this->session->userdata('user_company')) . ')';
                }
                ?></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('sessions/logout') }}"
                       class="tip icon logout" data-placement="bottom"
                       title="{{ trans('logout') }}">
                        <i class="fa fa-power-off"></i>
                        <span class="visible-xs">&nbsp;{{ trans('logout') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
