@extends('property.layouts.main')
@section('main-container')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.2/css/buttons.dataTables.css">
    <div class="content-body">
        <div class="container-fluid">
            @include('property.layouts.pageheader', ['hmsTitle' => 'Plan Master', 'hmsSubtitle' => 'Manage room plans and tariff structures'])

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white font-weight-bold"><i class="fas fa-clipboard-list mr-2"></i>Add Plan Master</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="form" action="{{ route('planststore') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="planname">Plan Name</label>
                                            <input type="text" name="planname" id="planname" class="form-control form-control-sm" required>
                                            <div id="namelist"></div>
                                            @error('planname')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="tarrif">Tariff</label>
                                            <select id="tarrif" name="tarrif" class="form-control form-control-sm" required>
                                                <option value="">Select Tariff</option>
                                                <option value="A.P.">A.P.</option>
                                                <option value="C.P.">C.P.</option>
                                                <option value="E.P.">E.P.</option>
                                                <option value="M.A.P.">M.A.P.</option>
                                            </select>
                                            @error('tarrif')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="room_cat">Room Category</label>
                                            <select id="room_cat" name="room_cat" class="form-control form-control-sm" required>
                                                <option value="">Select Category</option>
                                                @foreach ($roomcat as $list)
                                                    <option value="{{ $list->cat_code }}">{{ $list->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('room_cat')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="room_tax_stru">Room Tax Structure</label>
                                            <select id="room_tax_stru" name="room_tax_stru" class="form-control form-control-sm" required>
                                                <option value="">Select Tax Structure</option>
                                                @foreach ($taxstrudata as $list)
                                                    <option value="{{ $list->str_code }}">{{ $list->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('room_tax_stru')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="adults">Adult</label>
                                            <input type="text" name="adults" id="adults"
                                                oninput="Submiton();UpdateTotal();CalcPercent2('adultprice', 'package_amount', 'plan_per', 'net_amount','childprice');checkNumMax(this, 2)"
                                                class="form-control form-control-sm" required>
                                            @error('adults')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="childs">Child</label>
                                            <input type="text" name="childs" id="childs"
                                                oninput="Submiton();UpdateTotal();CalcPercent2('adultprice', 'package_amount', 'plan_per', 'net_amount','childprice');checkNumMax(this, 2)"
                                                class="form-control form-control-sm">
                                            @error('childs')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="map_code">Map Code</label>
                                            <input type="text" name="map_code" id="map_code" class="form-control form-control-sm">
                                            @error('map_code')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="package_amount">Plan Amount</label>
                                            <input name="package_amount" id="package_amount"
                                                class="decimal-input form-control form-control-sm"
                                                oninput="Submiton();UpdateTotal();CalcPercent2('adultprice', 'package_amount', 'plan_per', 'net_amount','childprice');LoadNext2(this, 'show2', 'room_rate', 'room_per');checkNumMax(this, 8)"
                                                step="0.01" min="0.00" max="9999999.99" placeholder="0.00"
                                                type="number">
                                            @error('package_amount')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div id="show2" class="none">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-secondary small" for="room_rate">Net Room Rate</label>
                                                <input class="form-control form-control-sm" type="number" name="room_rate" id="room_rate"
                                                    oninput="Submiton();UpdateTotal();CalcPercent2('adultprice', 'package_amount', 'plan_per', 'net_amount', 'childprice');
                                                    CalcPercent(this, 'package_amount', 'room_per');
                                                    checkNumMax(this, 10);
                                                    DisplayTable('room_rate', 'gridtaxstructure');">
                                                @error('room_rate')
                                                    <span class="text-danger small"> {{ $message }} </span>
                                                @enderror
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-secondary small" for="room_per">Room Percent</label>
                                                <input type="number" name="room_per" id="room_per"
                                                    oninput="Submiton();UpdateTotal();CalcPercent2('adultprice', 'package_amount', 'plan_per', 'net_amount','childprice');
                                                    checkNumMax(this, 5); CalcAmount(this, 'package_amount', 'room_rate');
                                                    DisplayTable('room_rate', 'gridtaxstructure');"
                                                    class="form-control form-control-sm">
                                                @error('room_per')
                                                    <span class="text-danger small"> {{ $message }} </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="disc_appYN">Discount Applicable </label>
                                            <select id="disc_appYN" onchange="LoadNext(this, 'show', 'disc_appON')"
                                                name="disc_appYN" class="form-control form-control-sm" required>
                                                <option value="">Select Option</option>
                                                <option value="Y">Yes</option>
                                                <option value="N">No</option>
                                            </select>
                                        </div>

                                        <div id="show" class="none">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-secondary small" for="disc_appON">Discount Applicable On</label>
                                                <select id="disc_appON" name="disc_appON" class="form-control form-control-sm">
                                                    <option value="">Select Target</option>
                                                    <option value="Discount On Food">Discount On Food</option>
                                                    <option value="Discount On Room">Discount On Room</option>
                                                    <option value="Discount On Both">Discount On Both</option>
                                                </select>
                                                @error('disc_appON')
                                                    <span class="text-danger small"> {{ $message }} </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="rrinc_tax">Room Tax Include In Plan Amount </label>
                                            <select id="rrinc_tax" name="rrinc_tax" class="form-control form-control-sm" required>
                                                <option value="">Select Option</option>
                                                <option value="Y">Yes</option>
                                                <option value="N">No</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="activeYN">Active Status</label>
                                            <select class="form-control form-control-sm" name="activeYN" id="activeYN" required>
                                                <option value="Y">Active</option>
                                                <option value="N">Inactive</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="desc1" class="font-weight-bold text-secondary small">Desc 1</label>
                                            <input type="text" class="form-control form-control-sm" name="desc1" id="desc1">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="desc2" class="font-weight-bold text-secondary small">Desc 2</label>
                                            <input type="text" class="form-control form-control-sm" name="desc2" id="desc2">
                                        </div>

                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm table-striped none" id="gridtaxstructure">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Sn</th>
                                                        <th>Fixed Charge</th>
                                                        <th>Tax Inc.</th>
                                                        <th>Fix Rate</th>
                                                        <th>Adult</th>
                                                        <th>Child</th>
                                                        <th>Percentage</th>
                                                        <th>Net Amt</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td id="serial">1</td>
                                                        <td class="text-center">
                                                            <select id="rev_code" name="rev_code1" class="form-control form-control-sm sl"
                                                                required>
                                                                <option value="">Select Charge</option>
                                                                @foreach ($chargedata as $list)
                                                                    <option value="{{ $list->rev_code }}">{{ $list->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-control form-control-sm sl" name="tax_inc1" id="tax_inc">
                                                                <option value="">Select</option>
                                                                <option value="Y">Yes</option>
                                                                <option value="N">No</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-control form-control-sm sl"
                                                                onchange="RemoveRead(this, 'plan_per', 'adultprice', 'childprice')"
                                                                name="fix_rate1" id="fix_rate">
                                                                <option value="">Select</option>
                                                                <option value="Y">Yes</option>
                                                                <option value="N">No</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="adultprice1" id="adultprice"
                                                                oninput="CalcPercent2('adultprice', 'package_amount', 'plan_per', 'net_amount','childprice');checkNumMax(this, 10);Submiton();UpdateTotal();"
                                                                class="form-control form-control-sm sl" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="childprice1" id="childprice"
                                                                oninput="CalcPercent2('adultprice', 'package_amount', 'plan_per', 'net_amount','childprice');checkNumMax(this, 10);Submiton();UpdateTotal();"
                                                                class="form-control form-control-sm sl" readonly>
                                                        </td>
                                                        <td>
                                                            <input name="plan_per1" id="plan_per"
                                                                class="form-control form-control-sm decimal-input form-visible" onkeydown="addNewRow(event)"
                                                                step="0.01" min="0.00" max="99999.99" placeholder="0.00"
                                                                oninput="handleDecimalInput(event); CalcPercent3('plan_per', 'package_amount', 'net_amount');
                                                                Submiton();UpdateTotal();"
                                                                type="text" readonly>
                                                        </td>
                                                        <td><input oninput="Submiton();UpdateTotal();" type="text"
                                                                name="net_amount1" id="net_amount" class="form-control form-control-sm sl"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-md-6 offset-md-6 mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="font-weight-bold small text-secondary">Net Amount</span>
                                            <input type="text" class="form-control form-control-sm w-50" name="totalroomrate" id="totalroomrate" readonly>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="font-weight-bold small text-secondary">Total</span>
                                            <input type="text" class="form-control form-control-sm w-50" name="lasttotal" oninput="Submiton()" id="lasttotal" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 text-right mt-2">
                                        <button type="submit" id="plansubmit" class="btn btn-primary btn-sm px-4 shadow-sm" disabled>Submit <i
                                                class="fa-solid fa-file-export ml-1"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 font-weight-bold text-dark"><i class="fas fa-list mr-2"></i>Plan Master List</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table id="plan_mast"
                                    class="table table-hover table-striped table-bordered table-sm" style="font-size:12px; width:100%;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Sn.</th>
                                            <th>Plan Code</th>
                                            <th>Name</th>
                                            <th>Room Type</th>
                                            <th>Tarrif Type</th>
                                            <th>Plan Amount</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sn = 1; @endphp
                                        @foreach ($data as $row)
                                            <tr>
                                                <td>{{ $sn }}</td>
                                                <td><b>{{ $row->plan_code }}</b></td>
                                                <td>{{ $row->planname }}</td>
                                                <td>{{ $row->catname }}</td>
                                                <td>{{ $row->tarrif }}</td>
                                                <td>₹{{ number_format($row->package_amount, 2) }}</td>
                                                <td class="text-center ins">
                                                    <a
                                                        href="updateplanmaster?plan_code={{ base64_encode($row->plan_code) }}&tarrif={{ base64_encode($row->tarrif) }}&room_cat={{ base64_encode($row->room_cat) }}&package_amount={{ base64_encode($row->package_amount) }}" class="btn btn-success btn-sm py-0 px-2 mr-1">
                                                        <i class="fa-regular fa-pen-to-square"></i> Edit
                                                    </a>
                                                    <a
                                                        href="deleteplanmast?plan_code={{ base64_encode($row->plan_code) }}&room_cat={{ base64_encode($row->room_cat) }}" class="btn btn-danger btn-sm py-0 px-2">
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
@endsection
