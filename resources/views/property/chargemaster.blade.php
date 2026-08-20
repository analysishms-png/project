@extends('property.layouts.main')
@section('main-container')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.2/css/buttons.dataTables.css">
    <div class="content-body">

        <div class="container-fluid">
            @include('property.layouts.pageheader', ['hmsTitle' => 'Charge Master', 'hmsSubtitle' => 'Manage charge codes and tariffs'])

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white font-weight-bold"><i class="fas fa-file-invoice-dollar mr-2"></i>Add Charge Master</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="form" name="chargemasterstoreform" id="chargemasterstoreform"
                                action="{{ route('chargemasterstore') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="name">Charge Name</label>
                                        <input type="text" name="name" id="name" class="form-control form-control-sm" required>
                                        <div id="namelist"></div>
                                        <span id="name_error" class="text-danger small"></span>
                                        @error('name')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="nature">Nature Of Charge</label>
                                        <select id="nature" name="nature" class="form-control form-control-sm">
                                            <option value="">Select Nature</option>
                                            <option value="Room Charge">Room Charge</option>
                                            <option value="Meal Charge">Meal Charge</option>
                                            <option value="Laundry Charge">Laundry Charge</option>
                                            <option value="Telephone Charge">Telephone Charge</option>
                                            <option value="Internet Charge">Internet Charge</option>
                                            <option value="Vehicle Charge">Vehicle Charge</option>
                                        </select>
                                        @error('nature')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="hsn_code">HSN Code</label>
                                        <input type="text" name="hsn_code" id="hsn_code" class="form-control form-control-sm">
                                        @error('hsn_code')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="ac_code">Account Name</label>
                                        <select id="ac_code" name="ac_code" class="form-control form-control-sm" required>
                                            <option value="">Select Account</option>
                                            @foreach ($ledgerdata as $list)
                                                <option value="{{ $list->sub_code }}">{{ $list->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('ac_code')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="tax_stru">Tax Structure</label>
                                        <select id="tax_stru" name="tax_stru" class="form-control form-control-sm" required>
                                            <option value="">Select Tax Structure</option>
                                            <?php
                                            $uniqueOptions = [];
                                            ?>
                                            @foreach ($taxstrudata as $list)
                                                <?php
                                                if (!in_array($list->str_code, $uniqueOptions)) {
                                                    $uniqueOptions[] = $list->str_code;
                                                ?>
                                                <option value="{{ $list->str_code }}">{{ $list->name }}</option>
                                                <?php
                                                }
                                                ?>
                                            @endforeach
                                        </select>
                                        @error('tax_stru')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="seq_no">Seq No</label>
                                        <input type="text" name="seq_no" id="seq_no" class="form-control form-control-sm">
                                        @error('seq_no')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="tax_inc" class="font-weight-bold text-secondary small">Tax Inclusive</label>
                                        <select id="tax_inc" name="tax_inc" class="form-control form-control-sm">
                                            <option value="Y">Yes</option>
                                            <option value="N">No</option>
                                        </select>
                                        @error('tax_inc')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="ac_posting" class="font-weight-bold text-secondary small">Posting Type</label>
                                        <select id="ac_posting" name="ac_posting" class="form-control form-control-sm">
                                            <option value="">Select Type</option>
                                            <option value="Detailed">Detailed</option>
                                            <option value="Summarize">Summarize</option>
                                        </select>
                                        @error('ac_posting')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="sales_rate" class="font-weight-bold text-secondary small">Sale Rate</label>
                                        <input name="sales_rate" class="form-control form-control-sm decimal-input form-visible"
                                            step="0.01" min="0.00" max="9999.99" placeholder="0.00"
                                            oninput="checkNumMax(this, 7);handleDecimalInput(event);" type="text">
                                        @error('sales_rate')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="font-weight-bold text-secondary small">Type</label>
                                        <select id="type" name="type" class="form-control form-control-sm">
                                            <option value="CR">CR</option>
                                            <option value="DR">DR</option>
                                        </select>
                                        @error('type')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small d-block" for="active">Active Status</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" value="Y" name="active"
                                                id="activeyes" checked>
                                            <label class="form-check-label small" for="activeyes">Active</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" value="N" name="active"
                                                id="activeno">
                                            <label class="form-check-label small" for="activeno">Inactive</label>
                                        </div>
                                    </div>

                                </div>

                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">Submit <i
                                            class="fa-solid fa-file-export ml-1"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 font-weight-bold text-dark"><i class="fas fa-list mr-2"></i>Charge Master List</h5>
                            <div class="d-flex gap-2">
                                <button type="button"
                                    onclick="window.location.href='{{ route('chargemaster.export') }}'"
                                    class="btn btn-success btn-sm shadow-sm mr-2"><i class="fas fa-file-excel mr-1"></i> Excel</button>
                                <button type="button"
                                    onclick="window.open('{{ route('printchargemaster') }}','_blank')"
                                    class="btn btn-info btn-sm text-white shadow-sm"><i class="fas fa-print mr-1"></i> Print</button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table id="chargemaster"
                                    class="table table-hover table-striped table-bordered table-sm" style="font-size:12px; width:100%;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Sn.</th>
                                            <th>Name</th>
                                            <th>Account Name</th>
                                            <th>Tax Structure Name</th>
                                            <th>Seq No</th>
                                            <th>Defined</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sn = 1; @endphp
                                        @foreach ($data as $row)
                                            <tr>
                                                <td>{{ $sn }}</td>
                                                <td><b>{{ $row->name }}</b></td>
                                                <td>{{ $row->subname }}</td>
                                                <td>{{ $row->taxstruname }}</td>
                                                <td>{{ $row->seq_no }}</td>
                                                <td>
                                                    <span class="badge {{ $row->SysYN == 'Y' ? 'badge-primary' : 'badge-secondary' }}">
                                                        {{ $row->SysYN == 'Y' ? 'System' : 'User' }}
                                                    </span>
                                                </td>
                                                <td class="text-center ins">
                                                    <a
                                                        href="updatechargemaster?sn={{ base64_encode($row->sn) }}&ac_code={{ base64_encode($row->ac_code) }}&rev_code={{ base64_encode($row->rev_code) }}&name={{ base64_encode($row->name) }}" class="btn btn-success btn-sm py-0 px-2 mr-1">
                                                        <i class="fa-regular fa-pen-to-square"></i> Edit
                                                    </a>
                                                    <a
                                                        href="deletechargemaster?sn={{ base64_encode($row->sn) }}&ac_code={{ base64_encode($row->ac_code) }}&rev_code={{ base64_encode($row->rev_code) }}&name={{ base64_encode($row->name) }}" class="btn btn-danger btn-sm py-0 px-2">
                                                        <i class="fa-solid fa-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            @php $sn++; @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var name = document.getElementById('name');
        var namelist = document.getElementById('namelist');
        var currentLiIndex = -1;
        if (!name || !namelist) return;
        name.addEventListener('keydown', function(event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                var liElements = namelist.querySelectorAll('li');
                currentLiIndex = (currentLiIndex + 1) % liElements.length;
                if (liElements.length > 0) {
                    name.value = liElements[currentLiIndex].textContent;
                }
            }
        });
        name.addEventListener('keyup', function() {
            var cid = this.value;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/getchargeames', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    namelist.innerHTML = xhr.responseText;
                    namelist.style.display = 'block';
                }
            };
            xhr.send('cid=' + cid + '&_token=' + '{{ csrf_token() }}');

        });
        $(document).on('click', function(event) {
            if (!$(event.target).closest('li').length) {
                namelist.style.display = 'none';
            }
        });
        $(document).on('click', '#namelist li', function() {
            $('#name').val($(this).text());
            namelist.style.display = 'none';
        });
    });
</script>
