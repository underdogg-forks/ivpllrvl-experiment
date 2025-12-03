@php
$alert_class = 'alert';
$alert_class .= isset($without_margin) ? ' no-margin' : '';
@endphp

{{-- Get validation errors --}}
@if(function_exists('validation_errors') && validation_errors())
    {!! validation_errors('<div class="' . $alert_class . ' alert-danger bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 px-4 py-3 rounded">', '</div>') !!}
@endif

{{-- Get flash alert messages and show them --}}
@php
$types = explode(' ', 'success info warning error');
$class = explode(' ', 'success info warning danger');
$icons = explode(' ', 'info-circle info-circle exclamation-circle warning');
@endphp
@foreach($types as $x => $type)
    @if(session()->has('alert_' . $type))
        <div class="{{ $alert_class }} alert-{{ $class[$x] }} alert-dismissible bg-{{ $class[$x] == 'danger' ? 'red' : ($class[$x] == 'warning' ? 'yellow' : ($class[$x] == 'info' ? 'blue' : 'green')) }}-50 dark:bg-{{ $class[$x] == 'danger' ? 'red' : ($class[$x] == 'warning' ? 'yellow' : ($class[$x] == 'info' ? 'blue' : 'green')) }}-900/20 text-{{ $class[$x] == 'danger' ? 'red' : ($class[$x] == 'warning' ? 'yellow' : ($class[$x] == 'info' ? 'blue' : 'green')) }}-700 dark:text-{{ $class[$x] == 'danger' ? 'red' : ($class[$x] == 'warning' ? 'yellow' : ($class[$x] == 'info' ? 'blue' : 'green')) }}-300 px-4 py-3 rounded" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fa fa-fw fa-lg fa-{{ $icons[$x] }}"></i><span>{{ session('alert_' . $type) }}</span>
        </div>
    @endif
@endforeach
