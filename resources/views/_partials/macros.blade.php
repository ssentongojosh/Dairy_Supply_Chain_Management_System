@php
$color = $color ?? '#2563eb';
$height = $height ?? 32;
@endphp

<span style="color:{{ $color }};">
  <svg width="{{ $height * 3.5 }}" height="{{ $height }}" viewBox="0 0 140 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- D -->
    <g transform="translate(0, 6)">
      <path d="M0 0h10c8 0 14 4 14 14s-6 14-14 14H0V0zm6 6v16h4c4 0 8-2 8-8s-4-8-8-8H6z" fill="currentColor" font-weight="bold"/>
    </g>

    <!-- S -->
    <g transform="translate(28, 6)">
      <path d="M0 10c0-5 3-10 10-10h8c5 0 8 3 8 8v2h-6v-2c0-1.5-1.5-3-3-3h-6c-1.5 0-3 1.5-3 3v6c0 1.5 1.5 3 3 3h8c5 0 8 3 8 8v6c0 5-3 8-8 8h-8c-7 0-10-5-10-10v-2h6v2c0 1.5 1.5 3 3 3h6c1.5 0 3-1.5 3-3v-6c0-1.5-1.5-3-3-3h-8c-5 0-8-3-8-8v-6z" fill="currentColor"/>
    </g>

    <!-- C -->
    <g transform="translate(56, 6)">
      <path d="M0 10c0-5 3-10 10-10h8c5 0 8 3 8 8v2h-6v-2c0-1.5-1.5-3-3-3h-6c-1.5 0-3 1.5-3 3v20c0 1.5 1.5 3 3 3h6c1.5 0 3-1.5 3-3v-2h6v2c0 5-3 8-8 8h-8c-7 0-10-5-10-10V10z" fill="currentColor"/>
    </g>

    <!-- M -->
    <g transform="translate(84, 6)">
      <path d="M0 0h8l6 20 6-20h8v28h-6V12l-4 16h-6l-4-16v16H0V0z" fill="currentColor"/>
    </g>

    <!-- S -->
    <g transform="translate(112, 6)">
      <path d="M0 10c0-5 3-10 10-10h8c5 0 8 3 8 8v2h-6v-2c0-1.5-1.5-3-3-3h-6c-1.5 0-3 1.5-3 3v6c0 1.5 1.5 3 3 3h8c5 0 8 3 8 8v6c0 5-3 8-8 8h-8c-7 0-10-5-10-10v-2h6v2c0 1.5 1.5 3 3 3h6c1.5 0 3-1.5 3-3v-6c0-1.5-1.5-3-3-3h-8c-5 0-8-3-8-8v-6z" fill="currentColor"/>
    </g>
  </svg>
</span>
