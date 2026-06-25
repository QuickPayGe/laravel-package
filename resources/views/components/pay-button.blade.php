@php
    $padding = match($size) {
        'sm' => '8px 16px',
        'lg' => '14px 28px',
        default => '11px 22px',
    };
    $fontSize = match($size) {
        'sm' => '13px',
        'lg' => '16px',
        default => '15px',
    };
    $display = $fullWidth ? 'flex' : 'inline-flex';
    $width   = $fullWidth ? '100%' : 'auto';
@endphp

<a
    href="{{ $url }}"
    role="button"
    target="{{ $target }}"
    rel="{{ $target === '_blank' ? 'noopener noreferrer' : '' }}"
    style="
        display: {{ $display }};
        align-items: center;
        justify-content: center;
        width: {{ $width }};
        padding: {{ $padding }};
        background-color: {{ $color }};
        color: {{ $textColor }};
        font-size: {{ $fontSize }};
        font-weight: 600;
        text-decoration: none;
        border-radius: {{ $radius }};
        border: none;
        cursor: pointer;
        line-height: 1.4;
        box-sizing: border-box;
    "
>{{ $label }}</a>
