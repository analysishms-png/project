{{--
    Standard master-screen page header using pure Bootstrap utility classes.
    Usage:
        @include('property.layouts.pageheader', [
            'hmsTitle'    => 'Room Master',
            'hmsSubtitle' => 'Manage hotel rooms and rates',
            'hmsActions'  => '<a class="btn btn-primary btn-sm" href="...">+ Add</a>',
        ])
--}}
@if (!empty($hmsTitle))
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="mb-1 text-primary font-weight-bold"><i class="fas fa-layer-group mr-2"></i>{{ $hmsTitle }}</h4>
        @if (!empty($hmsSubtitle))
            <p class="mb-0 text-muted small">{{ $hmsSubtitle }}</p>
        @endif
    </div>
    @if (!empty($hmsActions))
        <div class="d-flex align-items-center">{!! $hmsActions !!}</div>
    @endif
</div>
@endif
