@extends('property.layouts.main')
@section('main-container')
    @include('cdns.datatable')
    <div class="content-body">

        <div class="container-fluid">
            @include('property.layouts.pageheader', ['hmsTitle' => 'Tax Master', 'hmsSubtitle' => 'Manage tax rates and accounts'])

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white font-weight-bold"><i class="fas fa-percent mr-2"></i>Add Tax Master</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="form" name="taxform" id="taxform" action="{{ route('taxstore') }}"
                                method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="taxname">Tax Name</label>
                                        <input type="text" name="taxname" id="taxname" class="form-control form-control-sm" required>
                                        <div id="taxlist"></div>
                                        <span id="taxname_error" class="text-danger small"></span>
                                        @error('taxname')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="sundryname">Sundry Name</label>
                                        <select name="sundryname" id="sundryname" class="form-control form-control-sm">
                                            <option value="">Select Sundry</option>
                                            @foreach ($sundrymast as $item)
                                                <option value="{{ $item->sundry_code }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('sundryname')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="ledgeraccount">Ledger Accounts</label>
                                        <select name="ledgeraccount" id="ledgeraccount" class="form-control form-control-sm">
                                            <option value="">Select Ledger Account</option>
                                            @foreach ($ledgerdata as $item)
                                                <option value="{{ $item->sub_code }}"> {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('ledgeraccount')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="payableaccount">Payable Accounts</label>
                                        <select name="payableaccount" id="payableaccount" class="form-control form-control-sm">
                                            <option value="">Select Payable Account</option>
                                            @foreach ($ledgerdata as $item)
                                                <option value="{{ $item->sub_code }}"> {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('payableaccount')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="unregaccount">Unregistered Accounts</label>
                                        <select name="unregaccount" id="unregaccount" class="form-control form-control-sm">
                                            <option value="">Select Account</option>
                                            @foreach ($ledgerdata as $item)
                                                <option value="{{ $item->sub_code }}"> {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('unregaccount')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small d-block" for="activeyn">Active Status</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" value="Y" name="activeyn"
                                                id="activeyes" checked>
                                            <label class="form-check-label small" for="activeyes">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" value="N" name="activeyn"
                                                id="activeno">
                                            <label class="form-check-label small" for="activeno">Inactive</label>
                                        </div>
                                    </div>

                                </div>

                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">Submit <i class="fas fa-check ml-1"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 font-weight-bold text-dark"><i class="fas fa-list mr-2"></i>Tax Master List</h5>
                            <div class="d-flex gap-2">
                                <button type="button" id="excelBtn" class="btn btn-success btn-sm shadow-sm mr-2"><i class="fas fa-file-excel mr-1"></i> Excel</button>
                                <button type="button" id="printBtn" class="btn btn-info btn-sm text-white shadow-sm"><i class="fas fa-print mr-1"></i> Print</button>
                                <span id="printUrl" style="display:none;">{{ route('printtaxmaster') }}</span>
                                <span id="exportUrl" style="display:none;">{{ route('taxmaster.export') }}</span>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table id="taxmaster"
                                    class="table table-hover table-striped table-bordered table-sm" style="font-size:12px; width:100%;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Sn.</th>
                                            <th>Name</th>
                                            <th>Account Name</th>
                                            <th>Sundry</th>
                                            <th>Defined</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sn = 1; @endphp
                                        @foreach ($taxdata as $data)
                                            <tr>
                                                <td>{{ $sn }}</td>
                                                <td><b>{{ $data->taxname }}</b></td>
                                                <td>{{ $data->subname ?: $data->ac_code }}</td>
                                                <td>{{ $data->sundryname ?: $data->sundry }}</td>
                                                <td>
                                                    <span class="badge {{ $data->SysYN == 'Y' ? 'badge-primary' : 'badge-secondary' }}">
                                                        {{ $data->SysYN == 'Y' ? 'System' : 'User' }}
                                                    </span>
                                                </td>
                                                <td class="text-center ins">
                                                    <a
                                                        href="updatetax?sn={{ base64_encode($data->sn) }}" class="btn btn-success btn-sm py-0 px-2 mr-1">
                                                        <i class="fa-regular fa-pen-to-square"></i> Edit
                                                    </a>
                                                    <a
                                                        href="deletetax?rev_code={{ base64_encode($data->rev_code) }}&sn={{ base64_encode($data->sn) }}&ac_code={{ base64_encode($data->ac_code) }}" class="btn btn-danger btn-sm py-0 px-2">
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

    <script>
        $(document).ready(function() {
            new DataTable('#taxmaster', {
                ordering: true,
                order: [],
                pageLength: 25,
            });

            $('#excelBtn').on('click', function () {
                window.location.href = $('#exportUrl').text().trim();
            });

            $('#printBtn').on('click', function () {
                window.open($('#printUrl').text().trim(), '_blank');
            });
        });
    </script>
@endsection
