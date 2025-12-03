
A PHP Error was encountered

Severity:    {{ $severity }}
Message:     {{ $message }}
Filename:    {{ $filepath }}
Line Number: {{ $line }}

@if(defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE)
    Backtrace:
@foreach(debug_backtrace() as $error)
@if(isset($error['file']) && !str_starts_with($error['file'], realpath(BASEPATH)))
        File: {{ $error['file'] }}
        Line: {{ $error['line'] }}
        Function: {{ $error['function'] }}

@endif
@endforeach

@endif
