@extends('property.layouts.main')
@section('main-container')
@include('cdns.datatable')

<div class="content-body">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                        <form id="checklistform">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-2">
                                    <label class="col-form-label" for="sno1">Sn</label>
                                    <input type="number" name="sno1" id="sno1"
                                        class="form-control" min="1" readonly
                                        value="{{ $nextSno }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="checklistname1">
                                        Name <span class="text-danger">*</span>
                                    </label>
                                    <select name="checklistname1" id="checklistname1"
                                        class="form-control" required>
                                        <option value="">Select</option>
                                        @foreach ($predefined as $pvalue)
                                            <option value="{{ $pvalue }}">{{ $pvalue }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <div class="col-lg-8">
                                    <button type="submit" id="submitBtn" class="btn btn-primary">
                                        Submit <i class="fa-solid fa-file-export"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="checklistTable"
                            class="table table-hover table-download-with-search table-striped">
                            <thead class="bg-secondary">
                                <tr>
                                    <th>Sn.</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $row)
                                    <tr id="row-{{ $row->sn }}">
                                        <td>{{ $row->sno }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td class="ins">
                                            <button class="btn btn-success btn-sm editBtn"
                                                data-sn="{{ $row->sn }}"
                                                data-sno="{{ $row->sno }}"
                                                data-name="{{ $row->name }}"
                                                data-toggle="modal" data-target="#editModal">
                                                <i class="fa-regular fa-pen-to-square"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm deleteBtn"
                                                data-sn="{{ $row->sn }}"
                                                data-name="{{ $row->name }}">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Checklist Item</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editform">
                    @csrf
                    <input type="hidden" id="edit_sn" name="sn">
                    <div class="form-group mb-3">
                        <label class="col-form-label">Sn <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_sno" name="sno"
                            min="1" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="col-form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name"
                            maxlength="25" required
                            oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="text-center">
                        <button type="submit" id="updateBtn" class="btn btn-primary">
                            Update <i class="fa-solid fa-file-export"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    // ── DataTable ───────────────────────────────────────────────────────────────
    new DataTable('#checklistTable', {
        ordering: true,
        order: [],
        pageLength: 25,
    });

    // ── SUBMIT ──────────────────────────────────────────────────────────────────
    $('#checklistform').on('submit', function (e) {
        e.preventDefault();

        if (!$('#checklistname1').val()) {
            Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a name.' });
            return;
        }

        $('#submitBtn').prop('disabled', true);

        $.ajax({
            url:  '{{ route("checkliststore") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#submitBtn').prop('disabled', false);
                if (res.success) {
                    $('#checklistname1').val('');
                    Swal.fire({
                        icon: 'success', title: 'Success', text: res.message,
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function (xhr) {
                $('#submitBtn').prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message ?? 'Something went wrong' });
            }
        });
    });

    // ── EDIT MODAL POPULATE ─────────────────────────────────────────────────────
    $(document).on('click', '.editBtn', function () {
        $('#edit_sn').val($(this).data('sn'));
        $('#edit_sno').val($(this).data('sno'));
        $('#edit_name').val($(this).data('name'));
    });

    // ── UPDATE ──────────────────────────────────────────────────────────────────
    $('#editform').on('submit', function (e) {
        e.preventDefault();
        $('#updateBtn').prop('disabled', true);

        $.ajax({
            url:  '{{ route("checklistupdate") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#updateBtn').prop('disabled', false);
                if (res.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        icon: 'success', title: 'Success', text: res.message,
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function (xhr) {
                $('#updateBtn').prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message ?? 'Something went wrong' });
            }
        });
    });

    // ── DELETE ──────────────────────────────────────────────────────────────────
    $(document).on('click', '.deleteBtn', function () {
        const sn   = $(this).data('sn');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Delete "' + name + '"?',
            text:  'This record will be permanently deleted.',
            icon:  'warning',
            showCancelButton:   true,
            confirmButtonColor: '#d33',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:  '{{ route("checklistdelete") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', sn: sn },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success', title: 'Deleted!', text: res.message,
                                timer: 1500, showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong' });
                    }
                });
            }
        });
    });

    setTimeout(() => {
        $('.nav-control').trigger('click');
    }, 500);

});
</script>

@endsection
