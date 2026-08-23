@extends('property.layouts.property')
@section('content')
<style>.label-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}.mail-label{border:1px solid #e2e8f0;border-radius:8px;padding:12px;font-size:11px;min-height:80px;page-break-inside:avoid;}.mail-label .name{font-weight:700;font-size:13px;}.print-only{display:none;}@media print{.no-print{display:none!important;}.print-only{display:block;}.label-grid{grid-template-columns:repeat(3,1fr);}}</style>

<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;" class="no-print"><i class="fa-solid fa-tag" style="color:#667eea;margin-right:8px;"></i> Member Mailing Labels</h4>

    <form class="d-flex gap-3 mb-4 no-print" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">Category</label>
            <select name="category" class="form-select form-select-sm" style="border-radius:8px;">
                <option value="">All Categories</option>
                @foreach($categories as $c)<option value="{{ $c->code }}" {{ $category == $c->code ? 'selected' : '' }}>{{ $c->title }}</option>@endforeach
            </select>
        </div>
        <div class="d-flex align-items-end">
            <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">Generate</button>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-primary ms-2" style="border-radius:8px;"><i class="fa-solid fa-print"></i> Print</button>
        </div>
    </form>

    <div class="label-grid">
        @forelse($members as $m)
            <div class="mail-label">
                <div class="name">{{ $m->name }}</div>
                <div>{{ $m->address1 ?? '' }}</div>
                <div>{{ $m->city ?? '' }}, {{ $m->state ?? '' }}</div>
                <div>{{ $m->pin ?? '' }}</div>
                <div style="margin-top:4px;"><i class="fa-solid fa-phone" style="font-size:9px;"></i> {{ $m->mobile ?? '' }}</div>
                @if($m->category_name)<div style="color:#667eea;font-weight:600;">{{ $m->category_name }}</div>@endif
            </div>
        @empty
            <div class="text-center text-muted" style="padding:40px;grid-column:1/-1;">No members found</div>
        @endforelse
    </div>
</div>
@endsection
