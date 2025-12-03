
An uncaught Exception was encountered

Type:        {{ get_class($exception) }}
Message:     {{ $message }}
Filename:    {{ $exception->getFile() }}
Line Number: {{ $exception->getLine() }}

@if(defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE)
    Backtrace:
@foreach($exception->getTrace() as $error)
@if(isset($error['file']) && !str_starts_with($error['file'], realpath(BASEPATH)))
        File: {{ $error['file'] }}
        Line: {{ $error['line'] }}
        Function: {{ $error['function'] }}

@endif
@endforeach

@endif
