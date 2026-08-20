@extends('property.layouts.main')
@section('main-container')
    @include('cdns.datatable')
    <div class="content-body">

        <div class="container-fluid">
            @include('property.layouts.pageheader', ['hmsTitle' => 'Company Master', 'hmsSubtitle' => 'Manage company profile and accounts'])

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white font-weight-bold"><i class="fas fa-building mr-2"></i>Add Company Master</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="form" name="companymaster" id="companymaster"
                                action="{{ route('comp_maststore') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="name">Account Name</label>
                                        <input type="text" name="name" id="name" class="form-control form-control-sm" required>
                                        <span id="name_error" class="text-danger small"></span>
                                        @error('name')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="group_code">Under Group</label>
                                        <select id="group_code" name="group_code" class="form-control form-control-sm" required>
                                            <option value="">Select Group</option>
                                            @foreach ($subgroupdata as $list)
                                                <option value="{{ $list->group_code }}">{{ $list->group_name }}</option>
                                            @endforeach
                                        </select>
                                        <span id="group_code_error" class="text-danger small"></span>
                                        @error('group_code')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="comp_type" class="font-weight-bold text-secondary small">Company Type</label>
                                        <select id="comp_type" name="comp_type" class="form-control form-control-sm" required>
                                            <option value="">Select Type</option>
                                            <option value="Corporate">Corporate</option>
                                            <option value="Travel Agency">Travel Agency</option>
                                            <option value="Mess">Mess</option>
                                        </select>
                                        @error('comp_type')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="allow_credit" class="font-weight-bold text-secondary small">Allow Credit</label>
                                        <select id="allow_credit" name="allow_credit" class="form-control form-control-sm">
                                            <option value="">Select Option</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        @error('allow_credit')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="logo" class="font-weight-bold text-secondary small">Logo</label>
                                        <select id="logo" name="logo" class="form-control form-control-sm">
                                            <option value="">Select Logo</option>
                                            @foreach ($travel_agents as $agent)
                                                <option value="{{ $agent->img }}">{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('logo')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="mapcode">Map Code</label>
                                        <input type="text" name="mapcode" id="mapcode" class="form-control form-control-sm">
                                        @error('mapcode')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="conperson">Contact Person</label>
                                        <input type="text" name="conperson" id="conperson" class="form-control form-control-sm">
                                        @error('conperson')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="discounttype" class="font-weight-bold text-secondary small">Discount Type</label>
                                        <select id="discounttype" name="discounttype" class="form-control form-control-sm">
                                            <option value="">Select Type</option>
                                            <option value="Fix Rate">Fix Rate</option>
                                            <option value="Discount">Discount %</option>
                                        </select>
                                        @error('discounttype')
                                            <span class="text-danger small"> {{ $message }} </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="tradename" class="font-weight-bold text-secondary small">Trade Name</label>
                                        <input type="text" class="form-control form-control-sm" name="tradename" id="tradename">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="legalname" class="font-weight-bold text-secondary small">Legal Name</label>
                                        <input type="text" class="form-control form-control-sm" name="legalname" id="legalname">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="pincode" class="font-weight-bold text-secondary small">Pin Code</label>
                                        <input type="text" class="form-control form-control-sm" name="pincode" id="pincode">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="address">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control form-control-sm" name="address" id="addressC" rows="3"></textarea>
                                        <span id="address_error" class="text-danger small"></span>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="citycode">City</label>
                                        <select name="citycode" id="citycode" class="form-control form-control-sm">
                                            <option value="">Select City</option>
                                            @foreach ($citydata as $list)
                                                <option value="{{ $list->city_code }}">{{ $list->cityname }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="mobile">Mobile</label>
                                        <input type="text" oninput="checkNum(this)" name="mobile" id="mobile" maxlength="10"
                                            class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="panno">Pan No.</label>
                                        <input type="text" name="panno" id="panno" maxlength="14" class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="gstin">GSTIN</label>
                                        <input type="text" name="gstin" id="gstin" class="form-control form-control-sm">
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

                                    <div class="col-md-6 mb-3">
                                        <label for="openingbalance" class="font-weight-bold text-secondary small">Opening Balance</label>
                                        <input type="text" class="form-control form-control-sm" name="openingbalance" id="openingbalance">
                                        <span id="balancebadge" class="font-weight-bold h4 text-center mt-1 balancebadge"></span>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        @include('property.include.subledger')
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
                            <h5 class="card-title mb-0 font-weight-bold text-dark"><i class="fas fa-list mr-2"></i>Company Master List</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table id="comp_mast"
                                    class="table table-hover table-striped table-bordered table-sm" style="font-size:12px; width:100%;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Sn.</th>
                                            <th>Name</th>
                                            <th>Nature</th>
                                            <th>Contact Person</th>
                                            <th>Address</th>
                                            <th>GSTIN</th>
                                            <th>Property Id</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sn = 1; @endphp
                                        @foreach ($comp_mastdata as $data)
                                            <tr>
                                                <td>{{ $sn }}</td>
                                                <td><b>{{ $data->name }}</b></td>
                                                <td>{{ $data->nature }}</td>
                                                <td>{{ $data->conperson }}</td>
                                                <td>{{ $data->address }}</td>
                                                <td>{{ $data->gstin }}</td>
                                                <td>{{ $data->propertyid }}</td>
                                                <td class="text-center ins">
                                                    <a
                                                        href="updatecompmaster?sn={{ base64_encode($data->sn) }}&comp_code={{ base64_encode($data->sub_code) }}" class="btn btn-success btn-sm py-0 px-2 mr-1">
                                                        <i class="fa-regular fa-pen-to-square"></i> Edit
                                                    </a>
                                                    <a
                                                        href="deletecomp_mast?sn={{ base64_encode($data->sn) }}&comp_code={{ base64_encode($data->sub_code) }}" class="btn btn-danger btn-sm py-0 px-2">
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
            let table = new DataTable('#comp_mast', {
                dom: 'Bfrtip',
                ordering: true,
                order: [],
                buttons: [
                    'excel', 'pdf', 'print'
                ]
            });
            $('#companymaster').on('submit', function(e) {
                let gstin = $('#gstin').val();
                if (gstin !== '' && gstin.length < 15) {
                    e.preventDefault();
                    pushNotify('error', 'Company Master', 'GSTIN length should be equal to 15!', 'fade', 300, '', '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                }
            });

        });
    </script>
@endsection
