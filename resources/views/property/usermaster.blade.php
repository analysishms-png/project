@extends('property.layouts.main')
@section('main-container')
    <div class="content-body">

        <div class="container-fluid">
            @include('property.layouts.pageheader', ['hmsTitle' => 'User Master', 'hmsSubtitle' => 'Manage users, roles and permissions'])

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Create New User</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="form" name="usemasterform" id="usemasterform"
                                action="{{ route('usermasterstore') }}" method="POST" autocomplete="off">
                                @csrf

                                <!-- Hidden fake inputs to block browser autofill -->
                                <input type="text" name="fakeuser" id="fakeuser" style="display:none">
                                <input type="password" name="fakepass" id="fakepass" style="display:none">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="fullname">Name</label>
                                        <input type="text" name="fullname" id="fullname" maxlength="25" class="form-control form-control-sm"
                                            required autocomplete="new-fullname" placeholder="Enter full name">
                                        <span id="fullname_error" class="text-danger small"></span>
                                        @error('fullname')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="designation">Super Wiser</label>
                                        <select name="designation" id="designation" class="form-control form-control-sm" required>
                                            <option value="1">Yes</option>
                                            <option value="0" selected>No</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="department">Department</label>
                                        <select name="department" id="department" class="form-control form-control-sm">
                                            <option value="">Select Department</option>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept->dcode }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="user_designation">Designation</label>
                                        <select name="user_designation" id="user_designation" class="form-control form-control-sm">
                                            <option value="">Select Designation</option>
                                            @foreach ($designations as $desig)
                                                <option value="{{ $desig->code }}">{{ $desig->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="password">
                                            Password <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" name="password" id="password" minlength="4" maxlength="12"
                                            class="form-control form-control-sm" placeholder="Enter Password" autocomplete="new-password">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="system_name">
                                            System Name
                                        </label>
                                        <input type="text" name="system_name" id="system_name" maxlength="25"
                                            class="form-control form-control-sm" placeholder="Enter System Name">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold text-secondary small" for="backdate">Back Date Entry</label>
                                        <select name="backdate" id="backdate" class="form-control form-control-sm" required>
                                            <option value="1">Yes</option>
                                            <option value="0" selected>No</option>
                                        </select>
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
                            <h5 class="card-title mb-0 font-weight-bold text-dark"><i class="fas fa-users mr-2"></i>User List</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table id="usermastertable" class="table table-hover table-striped table-bordered table-sm" style="font-size:12px; width:100%;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Sn.</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>Super Wiser</th>
                                            <th>Back Date</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $sn = 1;
                                            $loginUser = Auth::user();
                                        @endphp

                                        @foreach ($userdata as $user)
                                            @if (
                                                    (
                                                        ($loginUser->superwiser == 1 )
                                                        && $user->u_name != 'sa'
                                                    )
                                                    || $loginUser->u_name == 'sa'
                                                    || (
                                                        $loginUser->superwiser != 1 
                                                        && $loginUser->u_name != 'sa' 
                                                        && $loginUser->id == $user->id
                                                    )
                                                )

                                                <tr>
                                                    <td>{{ $sn }}</td>
                                                    <td><b>{{ $user->name }}</b></td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @php
                                                            $deptName = $departments->firstWhere('dcode', $user->department);
                                                        @endphp
                                                        {{ $deptName ? $deptName->name : '-' }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $desigName = $designations->firstWhere('code', $user->designation);
                                                        @endphp
                                                        {{ $desigName ? $desigName->name : '-' }}
                                                    </td>
                                                    <td>{{ $user->superwiser == '1' ? 'Yes' : 'No' }}</td>
                                                    <td>{{ $user->backdate == '1' ? 'Yes' : 'No' }}</td>
                                                    <td class="text-center ins">
                                                        <input type="hidden" id="user_name_{{ $user->id }}" value="{{ $loginUser->u_name }}">
                                                        <input type="hidden" id="user_url_{{ $user->id }}"
                                                            value="updateusermaster?u_name={{ base64_encode($user->u_name) }}">

                                                        @if (strtolower($loginUser->u_name) == 'sa')
                                                            <a href="{{ url('updateusermaster?u_name=' . base64_encode($user->u_name)) }}" class="btn btn-success btn-sm py-0 px-2 mr-1">
                                                                Update <i class="fa-regular fa-pen-to-square"></i>
                                                            </a>
                                                        @else
                                                            <button class="btn btn-success btn-sm py-0 px-2 mr-1"
                                                                onclick="authModelOpenClosed({{ $user->id }})">
                                                                Update <i class="fa-regular fa-pen-to-square" id="get_user_{{ $user->id }}"></i>
                                                            </button>
                                                        @endif

                                                        @php $encodedId = base64_encode($user->id); @endphp

                                                        @if ($user->status == 1)
                                                            <a href="#" onclick="return confirmBanUserMaster('{{ $encodedId }}')">
                                                                <button class="btn btn-danger btn-sm py-0 px-2 text-white">
                                                                    InActive <i class="fa-solid fa-ban"></i>
                                                                </button>
                                                            </a>
                                                        @else
                                                            <a href="#" onclick="return confirmUnbanUserMaster('{{ $encodedId }}')">
                                                                <button class="btn btn-info btn-sm py-0 px-2 text-white">
                                                                    Active <i class="fa-solid fa-user-check"></i>
                                                                </button>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @php $sn++; @endphp
                                            @endif
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

    <!-- Auth Modal -->
    <div class="modal fade" id="checkAuthModal" tabindex="-1" aria-labelledby="checkAuthModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white font-weight-bold" id="updateModalLabel">Check Auth</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">

                    <form action="javascript.void();" id="checkAuthfrom">
                        @csrf
                        <input type="hidden" name="username" id="checkname" value="">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <label class="font-weight-bold text-secondary small" for="current_password">Current Password</label>
                                <input type="password" name="password" id="password" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4 pt-3">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm px-4 shadow-sm">Submit +</button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let url = '';

        $(document).ready(function() {
            $('#myloader').removeClass('none');
            setTimeout(() => {
                $('#myloader').addClass('none');
            }, 500);
        });

        function authModelOpenClosed(id) {
            var name = $('#user_name_' + id).val();
            url = $('#user_url_' + id).val();

            $('#checkname').val(name);
            $('#checkAuthModal').modal('show');
        }

        $('#checkAuthfrom').on('submit', function(e) {
            e.preventDefault();

            $('span.error-text').text('');

            $.ajax({
                url: "{{ route('checkAuth') }}", 
                method: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#submitBtn').prop('disabled', true).text('Submitting...');
                },
                success: function(response) {
                    $('#submitBtn').prop('disabled', false).text('Submit +');
                    if (response.status == 1) {
                        pushNotify('success', response.message);
                        $('#checkAuthfrom')[0].reset();
                        $('#checkAuthModal').modal('hide');
                        setTimeout(function() {
                            window.location.href = url;
                        }, 3000);
                    } else {
                        pushNotify('error', response.message);
                    }
                },
                error: function(xhr) {
                    $('#submitBtn').prop('disabled', false).text('Submit +');

                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            $('span.' + key + '_error').text(value[0]);
                            pushNotify('error', value[0]);
                        });
                    } else {
                        pushNotify('error', 'Something went wrong! Please try again.');
                    }
                }
            });
        });
    </script>
@endsection
