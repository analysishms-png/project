@extends('property.layouts.main')
@section('main-container')

<style>
#ld-senddate-wrap .datepicker {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: auto !important;
    margin-top: 2px;
    z-index: 9999 !important;
}
#ld-senddate-wrap .datepicker table td,
#ld-senddate-wrap .datepicker table th {
    padding: 0 !important;
    width: 30px !important;
    height: 30px !important;
    line-height: 30px !important;
}
#ld-senddate-wrap .datepicker {
    padding: 4px !important;
}
</style>

<div class="content-body">
<div class="container-fluid px-4 py-3">

    {{-- BANNER --}}
    <div class="d-flex align-items-center justify-content-between rounded p-3 mb-3 shadow-sm text-white"
         style="background:linear-gradient(135deg,#1e3a5f,#2d6a9f) !important;">
        <div>
            <h3 class="mb-0 fw-bold text-white">
                <i class="fa-solid fa-shirt me-2"></i>Laundry Send
            </h3>
            <small class="text-white-50">Send guest / staff linen to the laundry</small>
        </div>
        <div class="text-end">
            <div class="small text-uppercase text-white-50">Auto Voucher No.</div>
            <div class="h4 fw-bold mb-0 text-white">{{ $voucherNo }}</div>
        </div>
    </div>

    {{-- ENTRY FORM --}}
    <form id="ldForm" novalidate>
        @csrf
        <input type="hidden" id="ld-csrf" value="{{ csrf_token() }}">

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Send Date <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm position-relative" id="ld-senddate-wrap">
                            <input type="text" id="ld-senddate" class="form-control" placeholder="DD-MM-YYYY"
                                   readonly style="background:#fff;cursor:pointer;" value="{{ \Carbon\Carbon::parse($asOnDate)->format('d-m-Y') }}">
                            <span class="input-group-text" onclick="$('#ld-senddate').datepicker('show')" style="cursor:pointer;">
                                <i class="fa-regular fa-calendar"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback" id="err-senddate">Required.</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Room No <span class="text-danger">*</span></label>
                        <input type="text" id="ld-roomno" class="form-control form-control-sm" placeholder="e.g. 305" maxlength="20"
                               value="{{ $preRoomno ?? '' }}" list="ld-roomlist">
                        <datalist id="ld-roomlist">
                            @foreach($rooms as $r)
                                <option value="{{ $r }}"></option>
                            @endforeach
                        </datalist>
                        <div class="invalid-feedback" id="err-roomno">Required.</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Guest Name</label>
                        <input type="text" id="ld-guestname" class="form-control form-control-sm" maxlength="50" placeholder="Guest name">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Type</label>
                        <select id="ld-laundrytype" class="form-control form-control-sm custom-select">
                            <option value="Guest" selected>Guest</option>
                            <option value="Staff">Staff</option>
                            <option value="Linen">Linen</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Item Name <span class="text-danger">*</span></label>
                        <input type="text" id="ld-itemname" class="form-control form-control-sm" maxlength="50"
                               placeholder="e.g. Bed Sheet, Towel, Kurta" list="ld-itemlist">
                        <datalist id="ld-itemlist">
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
                    <div class="col-3 col-md-2">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Qty <span class="text-danger">*</span></label>
                        <input type="number" id="ld-quantity" class="form-control form-control-sm" value="1" min="0.01" step="0.01">
                    </div>
                    <div class="col-3 col-md-2">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Rate</label>
                        <input type="number" id="ld-rate" class="form-control form-control-sm" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-3 col-md-2">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Amount</label>
                        <input type="text" id="ld-amount" class="form-control form-control-sm bg-light" value="0.00" readonly>
                    </div>
                    <div class="col-3 col-md-2">
                        <label class="form-label fw-semibold small text-uppercase mb-1">&nbsp;</label>
                        <button type="button" class="btn btn-success btn-block fw-bold" onclick="ldSubmit()">
                            <i class="fa-solid fa-floppy-disk mr-1"></i>SAVE
                        </button>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-uppercase mb-1">Remarks</label>
                        <textarea id="ld-remarks" class="form-control form-control-sm" rows="2" maxlength="100"
                                  placeholder="Additional remarks..." style="resize:none;"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ALL RECORDS TABLE --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2"
             style="background:linear-gradient(135deg,#1e3a5f,#2d6a9f);">
            <span class="fw-bold text-white small text-uppercase">
                <i class="fa-solid fa-list me-1"></i>Laundry Send Entries
            </span>
            <span class="badge bg-light text-dark">{{ $items->total() }} Records</span>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-hover table-sm table-bordered align-middle mb-0" style="font-size:12px;">
                    <thead class="thead-dark">
                        <tr>
                            <th>Voucher</th>
                            <th>Send Date</th>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Type</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th style="width:60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="fw-bold text-primary">LS-{{ str_pad($item->vno, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->senddate)->format('d-M-Y') }}</td>
                            <td>{{ $item->roomno ?: '' }}</td>
                            <td>{{ $item->guestname ?: '' }}</td>
                            <td>{{ $item->laundrytype ?: '' }}</td>
                            <td>{{ $item->itemname ?: '' }}</td>
                            <td>{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }}</td>
                            <td>{{ number_format($item->rate, 2) }}</td>
                            <td class="fw-bold">{{ number_format($item->amount, 2) }}</td>
                            <td><span class="badge {{ $item->status === 'Received' ? 'badge-success' : 'badge-warning text-dark' }}">{{ $item->status }}</span></td>
                            <td>{{ $item->remarks ?: '' }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm"
                                        onclick="ldEdit({{ $item->sn }})" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                <i class="fa-solid fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                                No laundry send entries yet.
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
<div id="ld-toast" class="alert shadow-lg position-fixed"
     style="bottom:24px;right:24px;z-index:9999;display:none;min-width:240px;" role="alert"></div>

<script>
// Datepicker
$(function () {
    $('#ld-senddate').datepicker({ format: 'dd-mm-yyyy', autoclose: true });
});

// Auto-amount = qty x rate
function ldCalc() {
    var q = parseFloat(document.getElementById('ld-quantity').value) || 0;
    var r = parseFloat(document.getElementById('ld-rate').value) || 0;
    document.getElementById('ld-amount').value = (q * r).toFixed(2);
}
document.getElementById('ld-quantity').addEventListener('input', ldCalc);
document.getElementById('ld-rate').addEventListener('input', ldCalc);

// Validation
function ldValidate() {
    var ok = true;
    var required = [
        { id: 'ld-senddate', err: 'err-senddate' },
        { id: 'ld-roomno',   err: 'err-roomno'   },
        { id: 'ld-itemname', err: 'err-itemname' },
    ];
    required.forEach(function (f) {
        var el = document.getElementById(f.id);
        var errEl = document.getElementById(f.err);
        if (!el || !el.value.trim()) {
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
var ldEditId = null;

// Load record into form for editing
function ldEdit(id) {
    $.ajax({
        url:  '{{ route("laundrysendedit", "ID") }}'.replace('ID', id),
        type: 'GET',
        success: function (res) {
            if (!res.success) { ldToast(res.message || 'Record not found.', 'danger'); return; }
            var d = res.data;
            ldEditId = id;
            document.getElementById('ld-senddate').value = (function () {
                try { return new Date(d.senddate).toLocaleDateString('en-GB'); } catch (e) { return d.senddate; }
            })();
            document.getElementById('ld-roomno').value = d.roomno || '';
            document.getElementById('ld-guestname').value = d.guestname || '';
            document.getElementById('ld-laundrytype').value = d.laundrytype || 'Guest';
            document.getElementById('ld-itemname').value = d.itemname || '';
            document.getElementById('ld-quantity').value = d.quantity;
            document.getElementById('ld-rate').value = d.rate;
            document.getElementById('ld-amount').value = parseFloat(d.amount || 0).toFixed(2);
            document.getElementById('ld-remarks').value = d.remarks || '';
            document.getElementById('ldForm').scrollIntoView({ behavior: 'smooth' });
            var btn = document.querySelector('button[onclick="ldSubmit()"]');
            btn.innerHTML = '<i class="fa-solid fa-pen mr-1"></i>UPDATE';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            ldToast('Editing voucher LS-' + String(d.vno).padStart(2, '0') + '.', 'info');
        },
        error: function () { ldToast('Could not load record.', 'danger'); }
    });
}

// Submit (create or update)
function ldSubmit() {
    if (!ldValidate()) { ldToast('Please fill all required fields.', 'danger'); return; }
    var btn = document.querySelector('button[onclick="ldSubmit()"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Saving...';
    var url = ldEditId ? '{{ route("updatelaundrysend") }}' : '{{ route("storelaundrysend") }}';
    var payload = {
        _token:      document.getElementById('ld-csrf').value,
        senddate:    document.getElementById('ld-senddate').value,
        roomno:      document.getElementById('ld-roomno').value,
        guestname:   document.getElementById('ld-guestname').value,
        laundrytype: document.getElementById('ld-laundrytype').value,
        itemname:    document.getElementById('ld-itemname').value,
        quantity:    document.getElementById('ld-quantity').value,
        rate:        document.getElementById('ld-rate').value,
        amount:      document.getElementById('ld-amount').value,
        remarks:     document.getElementById('ld-remarks').value,
    };
    if (ldEditId) payload.id = ldEditId;
    $.ajax({
        url:  url,
        type: 'POST',
        data: payload,
        success: function (res) {
            btn.disabled = false;
            btn.innerHTML = ldEditId ? '<i class="fa-solid fa-pen mr-1"></i>UPDATE' : '<i class="fa-solid fa-floppy-disk mr-1"></i>SAVE';
            if (res.success) {
                ldToast((ldEditId ? 'Updated! Voucher: ' : 'Saved! Voucher: ') + res.voucherno, 'success');
                setTimeout(function () { location.reload(); }, 1400);
            } else {
                ldToast(res.message || 'Error saving.', 'danger');
            }
        },
        error: function (xhr) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i>SAVE';
            ldToast('Server error: ' + ((xhr.responseJSON && xhr.responseJSON.message) || xhr.status), 'danger');
        }
    });
}

// Reset edit mode when user clears the form
function ldClear() {
    document.getElementById('ldForm').reset();
    document.getElementById('ld-quantity').value = '1';
    document.getElementById('ld-rate').value = '0';
    document.getElementById('ld-amount').value = '0.00';
    ldEditId = null;
    var btn = document.querySelector('button[onclick="ldSubmit()"]');
    btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i>SAVE';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');
}

// Toast
function ldToast(msg, type) {
    var t = document.getElementById('ld-toast');
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
