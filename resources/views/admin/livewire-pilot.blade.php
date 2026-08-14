@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-3">
        <h4 class="mb-0">Livewire Pilot — Live Booking Search</h4>
        <span class="badge bg-primary ms-2">Pilot</span>
    </div>
    <p class="text-muted">
        Server-side live search + pagination without any page reload. Type in the box —
        results filter as you type (debounced 300ms).
    </p>

    <livewire:booking-search />
</div>
@endsection
