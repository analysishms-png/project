@extends('property.layouts.main')
@section('main-container')

<style>
#lr-receivedate-wrap .datepicker {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: auto !important;
    margin-top: 2px;
    z-index: 9999 !important;
}
#lr-receivedate-wrap .datepicker table td,
#lr-receivedate-wrap .datepicker table th {
    padding: 0 !important;
    width: 30px !important;
    height: 30px !important;
    line-height: 30px !important;
}
#lr-receivedate-wrap .datepicker {
    padding: 4px !important;
}
</style>

<div class="content-body">
<div class="container-fluid px-4 py-3">

    {{-- BANNER --}}
    <div class="d-flex align-items-center justify-content-between rounded p-3 mb-3 shadow-sm text-white"
         style="background:linear-gradient(135deg,#0f5132,#198754) !important;">
        <div>
            <h3 class="mb-0 fw-bold text-white">
                <i class="fa-solid fa-box-open me-2"></i>Laundry Receive
            </h3>
            <small class="text-white-50">Receive cleaned linen / garments back from the laundry</small>
        </div>
        <div class="text-end">
            <div class="small text-uppercase text-white-50">Auto Voucher No.</div>
            <div class="h4 fw-bold mb-0 text-white">{{ $voucherNo }}</div>
        </div>
    </div>

    {{-- ENTRY FORM --}}
    <form id="lrForm" novalidate>
        @csrf
        <input type="hidden" id="lr-csrf" value="{{ csrf_token() }}">

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Receive Date <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm position-relative" id="lr-receivedate-wrap">
                            <input type="text" id="lr-receivedate" class="form-control" placeholder="DD-MM-YYYY"
                                   readonly style="background:#fff;cursor:pointer;" value="{{ \Carbon\Carbon::parse($asOnDate)->format('d-m-Y') }}">
                            <span class="input-group-text" onclick="$('#lr-receivedate').datepicker('show')" style="cursor:pointer;">
                                <i class="fa-regular fa-calendar"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback" id="err-receivedate">Required.</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Room No <span class="text-danger">*</span></label>
                        <input type="text" id="lr-roomno" class="form-control form-control-sm" placeholder="e.g. 305" maxlength="20"
                               value="{{ $preRoomno ?? '' }}" list="lr-roomlist">
                        <datalist id="lr-roomlist">
                            @foreach($rooms as $r)
                                <option value="{{ $r }}"></option>
                            @endforeach
                        </datalist>
                        <div class="invalid-feedback" id="err-roomno">Required.</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Item Name <span class="text-danger">*</span></label>
                        <input type="text" id="lr-itemname" class="form-control form-control-sm" maxlength="50"
                               placeholder="e.g. Bed Sheet, Towel, Kurta" list="lr-itemlist">
                        <datalist id="lr-itemlist">
                            <option value="Bed Sheet"></option>
                            <option value="Pillow Cover"></option>
                            <option value="Towel"></option>
                            <option value="Bath Towel"></option>
                            <option value="Hand Towel"></option>
                            <option value="Bathrobe"></option>
                            <option value="Duvet Cover"></option>
                            <option value="Table Cloth"></option>
                            <option value="Napkin"></option>
                            <option value="Saree"></option>
                            <option value="Shirt"></option>
                            <option value="Trouser"></option>
                            <option value="Kurta"></option>
                            <option value="Jeans"></option>
                            <option value="Suit"></option>
                        </datalist>
                        <div class="invalid-feedback" id="err-itemname">Required.</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Received By</label>
                        <input type="text" id="lr-receivedby" class="form-control form-control-sm" maxlength="50" placeholder="Staff name">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Qty Received <span class="text-danger">*</span></label>
                        <input type="number" id="lr-quantity" class="form-control form-control-sm" value="0" min="0" step="0.01">
                        <div class="invalid-feedback" id="err-quantity">Required.</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Damaged Qty</label>
                        <input type="number" id="lr-damagedqty" class="form-control form-control-sm" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Missing Qty</label>
                        <input type="number" id="lr-missingqty" class="form-control form-control-sm" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">&nbsp;</label>
                        <button type="button" class="btn btn-success btn-block fw-bold" onclick="lrSubmit()">
                            <i class="fa-solid fa-floppy-disk mr-1"></i>SAVE
                        </button>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Remarks</label>
                        <textarea id="lr-remarks" class="form-control form-control-sm" rows="2" maxlength="100"
                                  placeholder="Additional remarks..." style="resize:none;"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ALL RECORDS TABLE --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2"
             style="background:linear-gradient(135deg,#0f5132,#198754);">
            <span class="fw-bold text-white small text-uppercase">
                <i class="fa-solid fa-list me-1"></i>Laundry Receive Entries
            </span>
            <span class="badge bg-light text-dark">{{ $items->total() }} Records</span>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-hover table-sm table-bordered align-middle mb-0" style="font-size:12px;">
                    <thead class="thead-dark">
                        <tr>
                            <th>Voucher</th>
                            <th>Receive Date</th>
                            <th>Room</th>
                            <th>Item</th>
                            <th>Qty Received</th>
                            <th>Damaged</th>
                            <th>Missing</th>
                            <th>Received By</th>
                            <th>Remarks</th>
                            <th style="width:60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="fw-bold text-primary">LR-{{ str_pad($item->vno, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->receivedate)->format('d-M-Y') }}</td>
                            <td>{{ $item->roomno ?: '' }}</td>
                            <td>{{ $item->itemname ?: '' }}</td>
                            <td class="fw-bold">{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</td>
                            <td>@if($item->damagedqty > 0)<span class="badge badge-danger">{{ rtrim(rtrim(number_format($item->damagedqty, 2, '.', ''), '0'), '.') }}</span>@else 0 @endif</td>
                            <td>@if($item->missingqty > 0)<span class="badge badge-warning text-dark">{{ rtrim(rtrim(number_format($item->missingqty, 2, '.', ''), '0'), '.') }}</span>@else 0 @endif</td>
                            <td>{{ $item->receivedby ?: '' }}</td>
                            <td>{{ $item->remarks ?: '' }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm"
                                        onclick="lrEdit({{ $item->sn }})" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fa-solid fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                                No laundry receive entries yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="mt-3 px-2">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
</div>

{{-- Toast --}}
<div id="lr-toast" class="alert shadow-lg position-fixed"
     style="bottom:24px;right:24px;z-index:9999;display:none;min-width:240px;" role="alert"></div>

<script>
// Datepicker
$(function () {
    $('#lr-receivedate').datepicker({ format: 'dd-mm-yyyy', autoclose: true });
});

// Validation
function lrValidate() {
    var ok = true;
    var required = [
        { id: 'lr-receivedate', err: 'err-receivedate' },
        { id: 'lr-roomno',      err: 'err-roomno'      },
        { id: 'lr-itemname',    err: 'err-itemname'    },
        { id: 'lr-quantity',    err: 'err-quantity'    },
    ];
    required.forEach(function (f) {
        var el = document.getElementById(f.id);
        var errEl = document.getElementById(f.err);
        if (!el || !el.value.trim() || parseFloat(el.value) <= 0) {
            if (el) el.classList.add('is-invalid');
            if (errEl) errEl.style.display = 'block';
            ok = false;
        } else {
            el.classList.remove('is-invalid');
            if (errEl) errEl.style.display = 'none';
        }
    });
    return ok;
}

// Edit mode state
var lrEditId = null;

// Load record into form for editing
function lrEdit(id) {
    $.ajax({
        url:  '{{ route("laundryreceiveedit", "ID") }}'.replace('ID', id),
        type: 'GET',
        success: function (res) {
            if (!res.success) { lrToast(res.message || 'Record not found.', 'danger'); return; }
            var d = res.data;
            lrEditId = id;
            document.getElementById('lr-receivedate').value = (function () {
                try { return new Date(d.receivedate).toLocaleDateString('en-GB'); } catch (e) { return d.receivedate; }
            })();
            document.getElementById('lr-roomno').value = d.roomno || '';
            document.getElementById('lr-itemname').value = d.itemname || '';
            document.getElementById('lr-receivedby').value = d.receivedby || '';
            document.getElementById('lr-quantity').value = d.quantity;
            document.getElementById('lr-damagedqty').value = d.damagedqty || 0;
            document.getElementById('lr-missingqty').value = d.missingqty || 0;
            document.getElementById('lr-remarks').value = d.remarks || '';
            document.getElementById('lrForm').scrollIntoView({ behavior: 'smooth' });
            var btn = document.querySelector('button[onclick="lrSubmit()"]');
            btn.innerHTML = '<i class="fa-solid fa-pen mr-1"></i>UPDATE';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            lrToast('Editing voucher LR-' + String(d.vno).padStart(2, '0') + '.', 'info');
        },
        error: function () { lrToast('Could not load record.', 'danger'); }
    });
}

// Submit (create or update)
function lrSubmit() {
    if (!lrValidate()) { lrToast('Please fill all required fields.', 'danger'); return; }
    var btn = document.querySelector('button[onclick="lrSubmit()"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Saving...';
    var url = lrEditId ? '{{ route("updatelaundryreceive") }}' : '{{ route("storelaundryreceive") }}';
    var payload = {
        _token:      document.getElementById('lr-csrf').value,
        receivedate: document.getElementById('lr-receivedate').value,
        roomno:      document.getElementById('lr-roomno').value,
        itemname:    document.getElementById('lr-itemname').value,
        receivedby:  document.getElementById('lr-receivedby').value,
        quantity:    document.getElementById('lr-quantity').value,
        damagedqty:  document.getElementById('lr-damagedqty').value,
        missingqty:  document.getElementById('lr-missingqty').value,
        remarks:     document.getElementById('lr-remarks').value,
    };
    if (lrEditId) payload.id = lrEditId;
    $.ajax({
        url:  url,
        type: 'POST',
        data: payload,
        success: function (res) {
            btn.disabled = false;
            btn.innerHTML = lrEditId ? '<i class="fa-solid fa-pen mr-1"></i>UPDATE' : '<i class="fa-solid fa-floppy-disk mr-1"></i>SAVE';
            if (res.success) {
                lrToast((lrEditId ? 'Updated! Voucher: ' : 'Saved! Voucher: ') + res.voucherno, 'success');
                setTimeout(function () { location.reload(); }, 1400);
            } else {
                lrToast(res.message || 'Error saving.', 'danger');
            }
        },
        error: function (xhr) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i>SAVE';
            lrToast('Server error: ' + ((xhr.responseJSON && xhr.responseJSON.message) || xhr.status), 'danger');
        }
    });
}

// Reset edit mode when user clears the form
function lrClear() {
    document.getElementById('lrForm').reset();
    document.getElementById('lr-quantity').value = '0';
    document.getElementById('lr-damagedqty').value = '0';
    document.getElementById('lr-missingqty').value = '0';
    lrEditId = null;
    var btn = document.querySelector('button[onclick="lrSubmit()"]');
    btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i>SAVE';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');
}

// Toast
function lrToast(msg, type) {
    var t = document.getElementById('lr-toast');
    var cls = { success: 'alert-success', danger: 'alert-danger', warning: 'alert-warning', info: 'alert-info' };
    t.className = 'alert shadow-lg position-fixed ' + (cls[type] || 'alert-info');
    t.innerHTML = msg;
    t.style.display = 'block';
    clearTimeout(t._timer);
    t._timer = setTimeout(function () { t.style.display = 'none'; }, 3500);
}

// Keep inputs editable (kisi global CSS/theme se readonly na ho)
setInterval(function () {
    $('input[type="text"], input[type="number"], input[type="email"], textarea').prop('readonly', false);
}, 1000);

</script>

@endsection
