@php
    $alertClass = 'alert' . (isset($without_margin) ? ' no-margin' : '');
    $types  = ['success', 'info', 'warning', 'error'];
    $classes = ['success', 'info', 'warning', 'danger'];
    $icons = ['info-circle', 'info-circle', 'exclamation-circle', 'warning'];
@endphp

@if (function_exists('validation_errors') && validation_errors())
    {!! validation_errors('<div class="' . $alertClass . ' alert-danger">', '</div>') !!}
@endif

@foreach ($types as $index => $type)
    @if (session('alert_' . $type))
        <div class="{{ $alertClass }} alert-{{ $classes[$index] }} alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

            <i class="fa fa-fw fa-lg fa-{{ $icons[$index] }}"></i>
            <span>{{ session('alert_' . $type) }}</span>
        </div>
    @endif
@endforeach
