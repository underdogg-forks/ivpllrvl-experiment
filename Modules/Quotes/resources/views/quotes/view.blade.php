@extends('core::components.layouts.app')

@section('content')

    @php
        $its_mine = session('user_id') == $quote->user_id;
        $my_class = $its_mine ? 'success' : 'warning';
        $edit_user_title = trans('edit') . ' ' . trans('user') . ' (' . trans('invoicing') . '): ' . PHP_EOL . e(format_user($quote->user_id));
    @endphp

    <script>
        $(function() {
            $('.btn_add_product').click(function() {
                $('#modal-placeholder').load("{{ route('products.ajax.modal_product_lookups') }}/" + Math.floor(Math.random() * 1000));
            });

            $('.btn_add_row').click(function() {
                $('#new_row').clone().appendTo('#item_table').removeAttr('id').addClass('item').show();
                check_items_tax_usages();
            });

            @if(!$items)
            $('#new_row').clone().appendTo('#item_table').removeAttr('id').addClass('item').show();
            @endif

            $(document).on('loaded', check_items_tax_usages());

            @if($quote->quote_status_id == 1)
            $('#quote_change_client').click(function() {
                $('#modal-placeholder').load("{{ route('quotes.ajax.modal.change_client') }}", {
                    quote_id: {{ $quote_id }},
                    client_id: "{{ e($quote->client_id) }}"
                });
            });

            $('#quote_change_user').click(function() {
                $('#modal-placeholder').load("{{ route('quotes.ajax.modal.change_user') }}", {
                    quote_id: {{ $quote_id }},
                    user_id: "{{ e($quote->user_id) }}"
                });
            });
            @endif

            $('#btn_save_quote').click(function() {
                var items = [];
                var item_order = 1;

                $('#item_table .item').each(function() {
                    var row = {};
                    $(this).find('input,select,textarea').each(function() {
                        if ($(this).is(':checkbox')) {
                            row[$(this).attr('name')] = $(this).is(':checked');
                        } else {
                            row[$(this).attr('name')] = $(this).val();
                        }
                    });
                    row['item_order'] = item_order++;
                    items.push(row);
                });

                $.post("{{ route('quotes.ajax.save') }}", {
                    legacy_calculation: {{ (int) $legacy_calculation }},
                    quote_id: {{ $quote_id }},
                    quote_number: $('#quote_number').val(),
                    quote_date_created: $('#quote_date_created').val(),
                    quote_date_expires: $('#quote_date_expires').val(),
                    quote_status_id: $('#quote_status_id').val(),
                    quote_password: $('#quote_password').val(),
                    items: JSON.stringify(items),
                    quote_discount_amount: $('#quote_discount_amount').val(),
                    quote_discount_percent: $('#quote_discount_percent').val(),
                    notes: $('#notes').val(),
                    custom: $('input[name^=custom],select[name^=custom]').serializeArray()
                }, function(data) {
                    var response = json_parse(data, {{ (bool) config('app.debug') }});
                    if (response.success === 1) {
                        window.location = "{{ route('quotes.view', $quote_id) }}";
                    } else {
                        $('#fullpage-loader').hide();
                        $('.control-group').removeClass('has-error');
                        $('div.alert[class*="alert-"]').remove();
                        var resp_errors = response.validation_errors,
                            all_resp_errors = '';

                        if (typeof resp_errors === 'string') {
                            all_resp_errors = resp_errors;
                        } else {
                            for (var key in resp_errors) {
                                $('#' + key).parent().addClass('has-error');
                                all_resp_errors += resp_errors[key];
                            }
                        }

                        $('#quote_form').prepend('<div class="p-4 mb-4 text-red-700 dark:text-red-200 bg-red-100 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-lg">' + all_resp_errors + '</div>');
                    }
                });
            });

            $(document).on('click', '.btn_delete_item', function() {
                var btn = $(this);
                var item_id = btn.data('item-id');

                if (typeof item_id === 'undefined') {
                    $(this).parents('.item').remove();
                    check_items_tax_usages();
                } else {
                    $.post("{{ route('quotes.ajax.delete_item', $quote->quote_id) }}", {
                        item_id: item_id
                    }, function(data) {
                        var response = json_parse(data, {{ (bool) config('app.debug') }});
                        if (response.success === 1) {
                            btn.parents('.item').remove();
                        } else {
                            btn.removeClass('btn-link').addClass('btn-danger').prop('disabled', true);
                        }
                        check_items_tax_usages();
                    });
                }
            });

            $('#btn_generate_pdf').click(function() {
                window.open('{{ route('quotes.generate_pdf', $quote->quote_id) }}', '_blank');
            });

            if ($('#quote_discount_percent').val().length > 0) {
                $('#quote_discount_amount').prop('disabled', true);
            }
            if ($('#quote_discount_amount').val().length > 0) {
                $('#quote_discount_percent').prop('disabled', true);
            }

            $('#quote_discount_amount').keyup(function() {
                $('#quote_discount_percent').prop('disabled', this.value.length > 0);
            });
            $('#quote_discount_percent').keyup(function() {
                $('#quote_discount_amount').prop('disabled', this.value.length > 0);
            });

            @if(get_setting('show_responsive_itemlist') == 1)
            function UpR(k) {
                var parent = k.parents('.item');
                var pos = parent.prev();
                parent.insertBefore(pos);
            }

            function DownR(k) {
                var parent = k.parents('.item');
                var pos = parent.next();
                parent.insertAfter(pos);
            }

            $(document).on('click', '.up', function() {
                UpR($(this));
            });
            $(document).on('click', '.down', function() {
                DownR($(this));
            });
            @else
            var fixHelper = function(e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function(index) {
                    $(this).width($originals.eq(index).width());
                });
                return $helper;
            };

            $('#item_table').sortable({
                helper: fixHelper,
                items: 'tbody'
            });
            @endif
        });
    </script>

    {{--{!! $modal_delete_quote !!}
    {!! $legacy_calculation ? $modal_add_quote_tax : '' !!}--}}

    <div id="headerbar">
        <h1 class="headerbar-title">
        <span data-toggle="tooltip" data-placement="bottom"
              title="@lang('invoicing'): {{ PHP_EOL . format_user($quote->user_id) }}">
            {{ trans('quote') . ' ' . ($quote->quote_number ? '#' . $quote->quote_number : trans('id') . ': ' . $quote->quote_id) }}
        </span>

            @if($change_user)
                <a data-toggle="tooltip" data-placement="bottom" title="{{ $edit_user_title }}"
                   href="{{ route('users.form', $quote->user_id) }}">
                    <i class="fa fa-xs fa-user text-{{ $my_class }}"></i>
                    <span class="hidden sm:block">{!! $quote->user_name !!}</span>
                </a>

                @if($quote->quote_status_id == 1)
                    <span id="quote_change_user"
                          class="fa fa-fw fa-edit text-{{ $its_mine ? 'muted' : 'danger' }} cursor-pointer"
                          data-toggle="tooltip" data-placement="bottom"
                          title="@lang('change_user')"></span>
                @endif
            @endif
        </h1>

        <div class="headerbar-item float-right inline-flex rounded-md shadow-sm">
            <div class="options inline-flex rounded-md shadow-sm [&>*]:px-3 [&>*]:py-1.5 [&>*]:text-sm">
                <a class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                   data-toggle="dropdown" href="#">
                    <i class="fa fa-caret-down no-margin"></i> @lang('options')
                </a>
                <ul class="absolute z-10 mt-2 min-w-[160px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg overflow-hidden">
                    @if($legacy_calculation)
                        <li><a href="#add-quote-tax" data-toggle="modal"><i
                                    class="fa fa-plus fa-margin"></i> @lang('add_quote_tax')</a></li>
                    @endif
                    <li><a href="#" id="btn_generate_pdf"><i class="fa fa-print fa-margin"></i> @lang('download_pdf')</a></li>
                    <li><a href="{{ route('mailer.quote', $quote->quote_id) }}"><i
                                class="fa fa-send fa-margin"></i> @lang('send_email')</a></li>
                    <li><a href="#" id="btn_quote_to_invoice"><i
                                class="fa fa-refresh fa-margin"></i> @lang('quote_to_invoice')</a></li>
                    <li><a href="#" id="btn_copy_quote" data-client-id="{{ $quote->client_id }}"><i
                                class="fa fa-copy fa-margin"></i> @lang('copy_quote')</a></li>
                    <li><a href="#delete-quote" data-toggle="modal"><i class="fa fa-trash-o fa-margin"></i> @lang('delete')
                        </a></li>
                </ul>
            </div>

            <a href="#"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 dark:bg-green-500 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors ajax-loader"
               id="btn_save_quote">
                <i class="fa fa-check"></i>
                @lang('save')
            </a>
        </div>
    </div>

    <div id="content">
        @include('core::layout.alerts')
        <div id="quote_form">
            <div class="quote">
                <div class="flex flex-wrap -mx-4">
                    <div class="w-full px-4 col-sm-6 col-md-5">
                        <h3>
                            <a href="{{ route('clients.view', $quote->client_id) }}">
                                {!! format_client($quote) !!}
                            </a>
                            @if($quote->quote_status_id == 1)
                                <span id="quote_change_client" class="fa fa-edit cursor-pointer small" data-toggle="tooltip"
                                      title="@lang('change_client')"></span>
                            @endif
                        </h3>
                        <br>
                        <div class="client-address">
                            @include('crm::clients.partial_client_address', ['client' => $quote])
                        </div>

                        @if($quote->client_phone)
                            <div>@lang('phone'): {!! $quote->client_phone !!}</div>
                        @endif
                        @if($quote->client_email)
                            <div>@lang('email'): {!! _auto_link($quote->client_email) !!}</div>
                        @endif
                    </div>

                    <div class="w-full px-4 block sm:hidden"><br></div>

                    <div class="w-full px-4 col-sm-5 col-sm-offset-1 md:w-1/2 col-md-offset-1">
                        <div class="details-box">
                            <div class="flex flex-wrap -mx-4">
                                <div class="w-full px-4 md:w-1/2">
                                    <div class="quote-properties">
                                        <label for="quote_number">@lang('quote') #</label>
                                        <input type="text" id="quote_number" class="w-full"
                                               value="{{ $quote->quote_number ?? '' }}" placeholder="@lang('not_set')">
                                    </div>
                                    <div class="quote-properties has-feedback">
                                        <label for="quote_date_created">@lang('date')</label>
                                        <input name="quote_date_created" id="quote_date_created" class="datepicker"
                                               value="{{ date_from_mysql($quote->quote_date_created) }}">
                                    </div>
                                    <div class="quote-properties has-feedback">
                                        <label for="quote_date_expires">@lang('expires')</label>
                                        <input name="quote_date_expires" id="quote_date_expires" class="datepicker"
                                               value="{{ date_from_mysql($quote->quote_date_expires) }}">
                                    </div>
                                </div>

                                <div class="w-full px-4 md:w-1/2">
                                    <div class="quote-properties">
                                        <label for="quote_status_id">@lang('status')</label>
                                        <select name="quote_status_id" id="quote_status_id" class="simple-select">
                                            @foreach($quote_statuses as $key => $status)
                                                <option
                                                    value="{{ $key }}" {{ $key == $quote->quote_status_id ? 'selected' : '' }}>
                                                    {{ $status['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="quote-properties">
                                        <label for="quote_password">@lang('quote_password')</label>
                                        <input type="text" id="quote_password" value="{{ $quote->quote_password }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @includeWhen(get_setting('show_responsive_itemlist'), 'quotes::quotes.partial_itemlist_responsive')
            </div>

            <hr />

            <div class="flex flex-wrap -mx-4">
                <div class="w-full px-4 md:w-1/2">
                    <div class="bg-white dark:bg-gray-800 border rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b bg-gray-50 dark:bg-gray-900">@lang('notes')</div>
                        <div class="p-6">
                            <textarea name="notes" id="notes" rows="3" class="w-full">{{ $quote->notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="w-full px-4 md:w-1/2">
                    @php _dropzone_html(false) @endphp
                </div>
            </div>
        </div>
    </div>

    @php _dropzone_script($quote->quote_url_key, $quote->client_id); @endphp

@endsection
