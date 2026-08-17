{{--
    Standard master-screen page header (UI Pass 3).
    Usage:
        @include('property.layouts.pageheader', [
            'hmsTitle'    => 'Room Master',
            'hmsSubtitle' => 'Manage hotel rooms and rates',
            'hmsActions'  => '<a class="btn btn-primary btn-sm" href="...">+ Add</a>', // optional, HTML
        ])
    If hmsTitle is empty the partial renders nothing (safe to include unconditionally).
--}}
@if (!empty($hmsTitle))
<div class="hms-page-header">
    <div class="hms-page-header-text">
        <h4 class="hms-page-title">{{ $hmsTitle }}</h4>
        @if (!empty($hmsSubtitle))
            <p class="hms-page-subtitle">{{ $hmsSubtitle }}</p>
        @endif
    </div>
    @if (!empty($hmsActions))
        <div class="hms-page-actions">{!! $hmsActions !!}</div>
    @endif
</div>
@endif
