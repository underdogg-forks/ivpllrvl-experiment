<script type="text/javascript">
    $(function () {
        // Cache jQuery selectors
        const $client_start_einvoicing = $('#client_start_einvoicing');
        const $toggle_einvoicing = $('.toggle_einvoicing');

        // Initial toggle based on current value
        toggle_einvoicing();

        // Toggle on change event
        $client_start_einvoicing.change(function () {
            toggle_einvoicing();
        });

        // Function to toggle einvoicing visibility
        function toggle_einvoicing() {
            const start_einvoicing = $client_start_einvoicing.val();

            if (start_einvoicing === '1') {
                $toggle_einvoicing.show();
            } else {
                $toggle_einvoicing.hide();
            }
        }
    });
</script>

<div class="grid grid-cols-1 md:grid-cols-12 gap-4{{ $xml_templates ? '' : ' hidden' }}">
    <div class="md:col-span-6">

        <div class="form-group mb-4">
            <label for="client_start_einvoicing" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ trans('einvoicing_start') }}
            </label>
            <select name="client_start_einvoicing" class="form-control simple-select w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                id="client_start_einvoicing" data-minimum-results-for-search="Infinity">
                @php $active = (old('client_einvoicing_version', $client->client_einvoicing_version ?? '') == '') ? '0' : '1'; @endphp
                <option value="0" {{ $active == '0' ? 'selected' : '' }}>
                    {{ trans('no') }}
                </option>
                <option value="1" {{ $active == '1' ? 'selected' : '' }}>
                    {{ trans('yes') }}
                </option>
            </select>
@php
$disabled = ''; // hint (And little tweak for .help-block)
$client_einvoicing_version = old('client_einvoicing_version', $client->client_einvoicing_version ?? '');
// Check logged user e-invoice fields (show_table 0 = ok, 1 = no)
if ($req_einvoicing->users[session('user_id')]->show_table > 0) {
    $disabled = ' disabled';
@endphp
            <p class="help-block text-sm text-gray-600 dark:text-gray-400 mt-1">{{ trans('einvoicing_start_hint') }}</p>
@php
}
@endphp
        </div>

    </div>

    <div class="toggle_einvoicing">
        <div class="md:col-span-6">

            <div class="form-group mb-4">
                <label for="client_einvoicing_version" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ 'UBL / CII ' . trans('version') }}</label>

                <select name="client_einvoicing_version" id="client_einvoicing_version" class="form-control simple-select w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"{{ $disabled }}>
                    <option value="">{{ trans('none') }}</option>
@foreach($xml_templates as $xml_key => $xml_template)
                    <option value="{{ $xml_key }}" {{ $xml_key == $client_einvoicing_version ? 'selected' : '' }}>
                        {{ $xml_template }}
                    </option>
@endforeach
                </select>

                <p class="help-block text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $disabled ? trans('einvoicing_ubl_cii_required_help') : trans('einvoicing_ubl_cii_creation_help') }}
                </p>
            </div>
        </div>
@php
$class_checks = ['fa fa-lg fa-check-square-o text-success', 'fa fa-lg fa-edit text-warning']; // Checkboxe icons
$base         = 'address_1 zip city country company tax_code vat_id'; // Field names
$keys         = explode(' ', $base); // To array
$lang         = explode(' ', strtr($base, ['_1' => ''])); // Translation vars name
// Users loop
foreach ($req_einvoicing->users as $user_id => $user) {
    if ($user->show_table) {
        $title_tip = ' data-toggle="tooltip" data-placement="bottom" title="' . trans('edit'); // Tooltip helper ! Need add: . '"'
        $user_link = '<a href="' . route('users.form', $user_id) . '" ' . $title_tip . ' ' . htmlspecialchars($user->user_name) . '">' . trans('user') . '</a>'; // ! Need add: . '"'
        $open      = $user_id == session('user_id') && $req_einvoicing->users[session('user_id')]->show_table;
        $me        = $user_id == session('user_id');
@endphp
        <!-- Check if mandatory eInvoicing fields are empty -->
        <div class="md:col-span-6 einvoice-user-check-lists collapse{{ $open ? ' in" aria-expanded="true' : '" aria-expanded="false' }}">
            <div class="form-group mb-4" data-toggle="tooltip" data-placement="top" title="{{ htmlspecialchars($user->user_name) }}">
                <div class="overflow-x-auto">
                    <table class="table table-hover table-condensed table-bordered no-margin w-full">
                        <thead class="text-center">
                            <tr>
                                <th class="px-4 py-2">{{ trans('required_fields') }}</th>
                                <th class="px-4 py-2 text-center min-w-[20%]">{{ trans('client') }}</th>
                                <th class="px-4 py-2 text-center min-w-[20%]">{!! $user_link !!}</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="px-4 py-2 text-center alert-{{ $me ? 'danger bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'warning bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300' }}" title="{{ trans('username') }}">
                                    <small class="text-sm inline-block"><i class="fa fa-fw fa-user"></i>{{ htmlspecialchars($user->user_name) }}</small>
                                </th>
                            </tr>
                        </tfoot>

                        <tbody>
@php
                // Loop on required keys
                foreach ($keys as $l => $key) {
                    // tr_show_* (attr name)
                    $tr_show_key = 'tr_show_' . $key;
                    // Show it in Errors (1)
                    if ($user->{$tr_show_key}) {
                        // Prepare some stuff
                        $c_icon = '<i class="' . $class_checks[$req_einvoicing->clients[$client_id]->{$key}] . '"></i>';
                        $u_icon = '<i class="' . $class_checks[$user->{$key}] . '"></i>';
@endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-2">{{ trans($lang[$l]) }}</td>
                                <td class="px-4 py-2 text-center">
                                    <a href="{{ route('clients.form', ['client_id' => $client_id]) }}#client_{{ $key }}" {!! $title_tip . ' #' . trans($lang[$l]) . ' (' . mb_trim(trans('field')) . ')"' !!}>{!! $c_icon !!}</a>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <a href="{{ route('users.form', ['user_id' => $user_id]) }}#user_{{ $key }}" {!! $title_tip . ' ' . htmlspecialchars($user->user_name) . ' #' . trans($lang[$l]) . ' (' . mb_trim(trans('field')) . ')"' !!}>{!! $u_icon !!}</a>
                                </td>
                            </tr>
@php
                    } // tr show
                } // End Foreach $keys
@endphp
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@php
    } // End if user->show_table
} // End foreach einvoicing->users
@endphp
    </div>
</div>
