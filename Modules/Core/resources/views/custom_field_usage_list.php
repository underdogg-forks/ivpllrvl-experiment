<?php
// Called in custom_fields/views/form.php & custom_values/views/field.php
// Where It's used
if ($custom_field_usage) {
    $url = [
        'invoice' => 'invoices/view/',
        'quote'   => 'quotes/view/',
        'payment' => 'payments/form/',
        'user'    => 'users/form/',
        'client'  => 'clients/form/',
    ];
    // ip_*what*_custom
    // $what = explode('_', $custom_field_table)[1]; // Modern php
    $what = strtr($custom_field_table, ['ip_' => '', '_custom' => '']); // O•Al•l•d php
    $href = site_url($url[$what]);
    ?>

    <div id="used<?php echo $what; ?>" class="col-xs-12 col-md-6 col-md-offset-3">
        <div class="panel-group" id="accordion<?php echo $what; ?>" role="tablist" aria-multiselectable="true">
            <div class="panel panel-info">
                <div class="panel-heading no-padding rounded" role="tab" id="heading<?php echo $what; ?>">
                    <h5 class="panel-title" role="button" data-toggle="collapse" aria-expanded="true" style="padding:1rem 8px"
                        data-parent="#accordion<?php echo $what; ?>" href="#collapse<?php echo $what; ?>" aria-controls="collapse<?php echo $what; ?>">
                        <i class="more-less fa pull-right fa-chevron-down"></i>
                        <?php _trans('custom_used_in'); ?>
                    </h5>
                </div>
                <div id="collapse<?php echo $what; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $what; ?>">
                    <div class="panel-body">
<?php
        // Build links from custom field usage data
        // If display values were pre-fetched, use them; otherwise use IDs
        $links = [];
    foreach ($custom_field_usage as $obj) {
        $fid = $what . '_id'; // Like invoice_id
        $id  = $obj->{$fid};

        // Check if display value was pre-fetched and passed to view
        if (isset($custom_field_usage_display_values, $custom_field_usage_display_values[$id])) {
            $display = $custom_field_usage_display_values[$id];
        } else {
            // Fallback to ID if no display value provided
            $display = $id;
        }

        $links[] = anchor($href . $id, trans($what) . '&nbsp;' . $display);
    }
    echo implode(', ', $links);
    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleIcon(e) {
            $(e.target)
                .prev('.panel-heading')
                .find('.more-less')
                .toggleClass('fa-chevron-down fa-chevron-up');
        }
        $('.panel-group').on('hidden.bs.collapse', toggleIcon);
        $('.panel-group').on('shown.bs.collapse', toggleIcon);
    </script>
<?php
} // End if
