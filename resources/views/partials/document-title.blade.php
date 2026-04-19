{{-- Използва се от layouts.app и layouts.guest ($title е опционален именован слот от компонента <x-*-layout>) --}}
@php
    $segment = isset($title) ? trim(strip_tags((string) $title)) : '';
@endphp
@if ($segment !== '')
    {{ $segment }} · {{ config('app.name') }}
@else
    {{ config('app.name') }}
@endif
