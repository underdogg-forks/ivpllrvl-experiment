<div class="overflow-x-auto">
    <table class="table table-hover table-striped w-full">
        <thead>
        <tr>
            <th class="px-4 py-2">{{ trans('active') }}</th>
            <th class="px-4 py-2">{{ trans('client_name') }}</th>
            <th class="px-4 py-2">{{ trans('email_address') }}</th>
@if($einvoicing)
            <th class="px-4 py-2">{{ 'e-' . trans('invoicing') . ' ' . ucfirst(trans('version')) }}</th>
            <th class="px-4 py-2">{{ 'e-' . trans('invoicing') . ' ' . trans('active') }}</th>
@endif
            <th class="px-4 py-2">{{ trans('phone_number') }}</th>
            <th class="px-4 py-2 amount last">{{ trans('balance') }}</th>
            <th class="px-4 py-2">{{ trans('options') }}</th>
        </tr>
        </thead>
        <tbody>
@php
$class_checks = ['fa fa-lg fa-check-square-o text-success', 'fa fa-lg fa-edit text-warning']; // e-invoice
@endphp
@foreach($records as $client)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-2">
@if($client->client_active)
                    <span class="label active px-2 py-1 rounded-md text-xs font-semibold bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">{{ trans('yes') }}</span>
@else
                    <span class="label inactive px-2 py-1 rounded-md text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">{{ trans('no') }}</span>
@endif
                </td>
                <td class="px-4 py-2">
                    <a href="{{ route('clients.show', $client->client_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ htmlspecialchars(format_client($client)) }}
                    </a>
                </td>
                <td class="px-4 py-2">{{ htmlspecialchars($client->client_email) }}</td>
@if($einvoicing)
                <td class="px-4 py-2">{{ htmlspecialchars($client->client_einvoicing_version) }}</td>
                <td class="px-4 py-2">
@if($client->client_einvoicing_active == 1)
                    <i class="{{ $class_checks[0] }}"></i>
@elseif($client->client_einvoicing_version != '')
                    <i class="{{ $class_checks[1] }}"></i>
@endif
                </td>
@endif
                <td class="px-4 py-2">{{ htmlspecialchars($client->client_phone ? $client->client_phone : ($client->client_mobile ? $client->client_mobile : '')) }}</td>
                <td class="px-4 py-2 amount last">{{ format_currency($client->client_invoice_balance) }}</td>
                <td class="px-4 py-2">
                    <div class="options btn-group">
                        <a class="btn btn-default btn-sm dropdown-toggle inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600" data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> {{ trans('options') }}
                        </a>
                        <ul class="dropdown-menu bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg">
                            <li>
                                <a href="{{ route('clients.show', $client->client_id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fa fa-eye fa-margin"></i> {{ trans('view') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('clients.edit', $client->client_id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fa fa-edit fa-margin"></i> {{ trans('edit') }}
                                </a>
                            </li>
                            <li>
                                <a href="#" class="client-create-quote block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                   data-client-id="{{ $client->client_id }}">
                                    <i class="fa fa-file fa-margin"></i> {{ trans('create_quote') }}
                                </a>
                            </li>
                            <li>
                                <a href="#" class="client-create-invoice block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                   data-client-id="{{ $client->client_id }}">
                                    <i class="fa fa-file-text fa-margin"></i> {{ trans('create_invoice') }}
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('clients.destroy', $client->client_id) }}"
                                      method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-button w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            onclick="return confirm('{{ trans('delete_client_warning') }}');">
                                        <i class="fa fa-trash-o fa-margin"></i> {{ trans('delete') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
@endforeach
        </tbody>
    </table>
</div>
