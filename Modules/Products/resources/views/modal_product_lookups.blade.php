<script>
    $(function () {
        // Display the create invoice modal
        $('#modal-choose-items').modal('show');

        $(".simple-select").select2();

        // Creates the invoice
        $('.select-items-confirm').click(function () {
            var product_ids = [];

            $("input[name='product_ids[]']:checked").each(function () {
                product_ids.push(parseInt($(this).val()));
            });
            // No Check No post
            if ( ! product_ids.length) return; // todo: why not animate checkboxes

            $.post("{{ route('products.ajax.process_product_selections') }}", {
                product_ids: product_ids
            }, function (data) {
                var items = json_parse(data, {{ (int) config('app.debug') }});
                for (var key in items) {
                    // Set default tax rate id if empty
                    if (!items[key].tax_rate_id) items[key].tax_rate_id = '{{ $default_item_tax_rate }}';

                    if ($('#item_table .item:last input[name=item_name]').val() !== '') {
                        $('#new_row').clone().appendTo('#item_table').removeAttr('id').addClass('item').show();
                    }

                    var last_item_row = $('#item_table .item:last');

                    last_item_row.find('input[name=item_name]').val(items[key].product_name);
                    last_item_row.find('textarea[name=item_description]').val(items[key].product_description);
                    last_item_row.find('input[name=item_price]').val(items[key].product_price);
                    last_item_row.find('input[name=item_quantity]').val('1');
                    last_item_row.find('select[name=item_tax_rate_id]').val(items[key].tax_rate_id);
                    last_item_row.find('input[name=item_product_id]').val(items[key].product_id);
                    last_item_row.find('select[name=item_product_unit_id]').val(items[key].unit_id);

                    $('#modal-choose-items').modal('hide');
                }

                // Legacy:no: check items tax usage is correct (ReLoad on change) - since 1.6.3
                check_items_tax_usages();
            });
        });

        // Add on rows a click event to Toggle they checkbox
        function addClickTrToggleCheck (){
            $('#products_table tr').click(function (event) {
                if (event.target.type !== 'checkbox') {
                    $(':checkbox', this).trigger('click');
                }
            });
        }
        addClickTrToggleCheck(); // init row click event ! important

        // Reset the form
        $('#product-reset-button').click(function () {
            var product_table = $('#product-lookup-table');

            product_table.html('<h2 class="text-center"><i class="fa fa-spin fa-spinner"></i></h2>');

            var lookup_url = "{{ route('products.ajax.modal_product_lookups', '') }}/";
            lookup_url += Math.floor(Math.random() * 1000) + '/?';
            lookup_url += "&reset_table=true";

            // Reload to default & add rows click event
            window.setTimeout(function () {
                product_table.load(lookup_url, addClickTrToggleCheck);
            }, 250);
        });

        // Filter on search button click
        $('#filter-button').click(function () {
            products_filter();
        });

        // Filter on family dropdown change
        $("#filter_family").change(function () {
            products_filter();
        });

        // Filter products
        function products_filter() {
            var filter_family = $('#filter_family').val();
            var filter_product = $('#filter_product').val();
            var product_table = $('#product-lookup-table');

            product_table.html('<h2 class="text-center"><i class="fa fa-spin fa-spinner"></i></h2>');

            var lookup_url = "{{ route('products.ajax.modal_product_lookups', '') }}/";
            lookup_url += Math.floor(Math.random() * 1000) + '/?';

            if (filter_family) {
                lookup_url += "&filter_family=" + filter_family;
            }

            if (filter_product) {
                lookup_url += "&filter_product=" + filter_product;
            }

            // Reload by filtered & add rows click event
            window.setTimeout(function () {
                product_table.load(lookup_url, addClickTrToggleCheck);
            }, 250);
        }

        // Bind enter to product search if search field is focused
        $(document).keypress(function(e){
            if (e.which === 13 && $('#filter_product').is(':focus')){
                $('#filter-button').click();
                return false;
            }
        });
    });
</script>

<div id="modal-choose-items" class="modal col-xs-12 col-sm-10 col-sm-offset-1"
     role="dialog" aria-labelledby="modal-choose-items" aria-hidden="true">
    <form class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><i class="fa fa-close"></i></button>
            <h4 class="fi-section-title">{{ trans('add_product') }}</h4>
        </div>
        <div class="modal-body">

            <div class="form-inline">
                <div class="fi-field-wrp filter-form">
                    <select name="filter_family" id="filter_family" class="fi-input simple-select">
                        <option value="">{{ trans('any_family') }}</option>
                        @foreach ($families as $family)
                            <option value="{{ $family->family_id }}"
                                {{ (isset($filter_family) && $family->family_id == $filter_family) ? 'selected' : '' }}>
                                {{ $family->family_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="fi-field-wrp">
                    <input type="text" class="fi-input" name="filter_product" id="filter_product"
                           placeholder="{{ trans('product_name') }}"
                           value="{{ $filter_product ?? '' }}">
                </div>
                <button type="button" id="filter-button"
                        class="fi-btn-secondary">{{ trans('search_product') }}</button>
                <button type="button" id="product-reset-button" class="fi-btn-secondary">
                    {{ trans('reset') }}
                </button>
            </div>

            <br/>

            <div id="product-lookup-table">
                @include('products::partial_product_table_modal')
            </div>

        </div>
        <div class="modal-footer">
            <div class="btn-group">
                <button class="select-items-confirm fi-btn-success" type="button">
                    <i class="fa fa-check"></i>
                    {{ trans('submit') }}
                </button>
                <button class="fi-btn-danger" type="button" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                    {{ trans('cancel') }}
                </button>
            </div>
        </div>
    </form>

</div>
