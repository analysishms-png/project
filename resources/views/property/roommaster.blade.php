@extends('property.layouts.main')
@section('main-container')
    <div class="content-body">
        <div class="container-fluid">
            @include('property.layouts.pageheader', ['hmsTitle' => 'Room Master', 'hmsSubtitle' => 'Manage hotel rooms, categories and rates'])

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white font-weight-bold"><i class="fas fa-bed mr-2"></i>Add New Room</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="form" action="{{ route('roommaststore') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="rcode">Room No.</label>
                                            <input type="text" name="rcode" id="rcode" class="form-control form-control-sm" required>
                                            @error('rcode')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="roomname">Room Name</label>
                                            <input type="text" name="roomname" id="roomname" class="form-control form-control-sm" required>
                                            <div id="namelist"></div>
                                            @error('roomname')
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
                                            <label class="font-weight-bold text-secondary small" for="pic_path">Photo <span class="text-danger">*</span></label>
                                            <input type="file" onchange="checkFile(this, '1mb', ['jpg', 'png', 'jpeg', 'webp'])"
                                                name="pic_path" accept=".jpg,.png,.jpeg,.webp" id="pic_path"
                                                class="form-control-file">
                                            @error('pic_path')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="multiper">Multiple Person</label>
                                            <input type="text" oninput="checkNumMax(this, 5)" name="multiper" id="multiper"
                                                class="form-control form-control-sm" required>
                                            @error('multiper')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="maid_station">House Keeping</label>
                                            <select id="maid_station" name="maid_station" class="form-control form-control-sm">
                                                <option value="">Select</option>
                                                <option value="House Keeping">House Keeping</option>
                                            </select>
                                            @error('maid_station')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="floor">Floor</label>
                                            <select id="floor" name="floor" class="form-control form-control-sm">
                                                <option value="">Select Floor</option>
                                                @foreach ($floors as $fl)
                                                    <option value="{{ $fl->code }}">{{ $fl->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('floor')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small d-block">Include Room Count</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" value="Y" name="inclcount"
                                                    id="activeyes" checked>
                                                <label class="form-check-label small" for="activeyes">Active</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" value="N" name="inclcount"
                                                    id="activeno">
                                                <label class="form-check-label small" for="activeno">Inactive</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <h6 class="font-weight-bold text-primary mb-2">Rate Structure</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm table-striped" id="gridtaxstructure">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Occ Type</th>
                                                        <th>{{ $envirodata->rate1 }}</th>
                                                        <th>{{ $envirodata->rate2 }}</th>
                                                        <th>{{ $envirodata->rate3 }}</th>
                                                        <th>{{ $envirodata->rate4 }}</th>
                                                        <th>{{ $envirodata->rate5 }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $icons = ['singleuser.svg', 'multiuser.svg', 'extrauser.svg', 'weekend.svg'];
                                                        $names = ['singleuser', 'multiuser', 'extrauser', 'weekend'];
                                                        for ($i = 0; $i < count($icons); $i++) {
                                                            $iconName = $icons[$i];
                                                            $namelabel = $names[$i];
                                                        ?>
                                                    <tr>
                                                        <td class="text-center" id="serial">
                                                            <input type="hidden" name="<?php echo $namelabel; ?>"
                                                                value="<?php echo $iconName; ?>">
                                                            <img src="admin/icons/custom/<?php echo $iconName; ?>" width="25"
                                                                height="25">
                                                        </td>
                                                        <td>
                                                            <input name="<?php echo $namelabel; ?>_highrate"
                                                                class="form-control form-control-sm decimal-input form-visible" step="0.01" min="0.00"
                                                                max="99999.99" placeholder="0.00"
                                                                oninput="checkNumMax(this, 8);handleDecimalInput(event);"
                                                                type="text">
                                                        </td>
                                                        <td>
                                                            <input name="<?php echo $namelabel; ?>_rackrate"
                                                                class="form-control form-control-sm decimal-input form-visible" step="0.01" min="0.00"
                                                                max="99999.99" placeholder="0.00"
                                                                oninput="checkNumMax(this, 8);handleDecimalInput(event);"
                                                                type="text">
                                                        </td>
                                                        <td>
                                                            <input name="<?php echo $namelabel; ?>_diskrate1"
                                                                class="form-control form-control-sm decimal-input form-visible" step="0.01" min="0.00"
                                                                max="99999.99" placeholder="0.00"
                                                                oninput="checkNumMax(this, 8);handleDecimalInput(event);"
                                                                type="text">
                                                        </td>
                                                        <td>
                                                            <input name="<?php echo $namelabel; ?>_diskrate2"
                                                                class="form-control form-control-sm decimal-input form-visible" step="0.01" min="0.00"
                                                                max="99999.99" placeholder="0.00"
                                                                oninput="checkNumMax(this, 8);handleDecimalInput(event);"
                                                                type="text">
                                                        </td>
                                                        <td>
                                                            <input name="<?php echo $namelabel; ?>_diskrate3"
                                                                class="form-control form-control-sm decimal-input form-visible" step="0.01" min="0.00"
                                                                max="99999.99" placeholder="0.00"
                                                                oninput="checkNumMax(this, 8);handleDecimalInput(event);"
                                                                type="text">
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3 text-right">
                                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">Submit <i
                                                class="fa-solid fa-file-export ml-1"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 font-weight-bold text-dark"><i class="fas fa-list mr-2"></i>Room List</h5>
                            <div class="d-flex gap-2">
                                <button type="button" onclick="window.location.href='{{ route('roommaster.export') }}'" class="btn btn-success btn-sm shadow-sm mr-2"><i class="fas fa-file-excel mr-1"></i> Excel</button>
                                <button type="button" onclick="window.open('{{ route('printroommaster') }}','_blank')" class="btn btn-info btn-sm text-white shadow-sm"><i class="fas fa-print mr-1"></i> Print</button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table id="room_mast"
                                    class="table table-hover table-striped table-bordered table-sm" style="font-size:12px; width:100%;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Sn.</th>
                                            <th>Room No.</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sn = 1; @endphp
                                        @foreach ($data as $row)
                                            <tr>
                                                <td>{{ $sn }}</td>
                                                <td><b>{{ $row->rcode }}</b></td>
                                                <td>{{ $row->rcode }}</td>
                                                <td>{{ $row->catname }}</td>
                                                <td class="text-center ins">
                                                    <a
                                                        href="updateroommast?sno={{ base64_encode($row->sno) }}&roomno={{ base64_encode($row->rcode) }}&cat_code={{ base64_encode($row->room_cat) }}" class="btn btn-success btn-sm py-0 px-2 mr-1">
                                                        <i class="fa-regular fa-pen-to-square"></i> Edit
                                                    </a>
                                                    <a
                                                        href="deleteroommaster?sno={{ base64_encode($row->sno) }}&roomno={{ base64_encode($row->rcode) }}&cat_code={{ base64_encode($row->room_cat) }}" class="btn btn-danger btn-sm py-0 px-2 mr-1">
                                                        <i class="fa-solid fa-trash"></i> Delete
                                                    </a>
                                                    <a data-dcode="{{ $row->rest_code }}" data-rcode="{{ $row->rcode }}" class="btn btn-info btn-sm py-0 px-2 qrcodebtn text-white" href="javascript:void(0)"><i class="fa-solid fa-qrcode"></i> QR Code</a>
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
        if (!$.fn.DataTable.isDataTable('#room_mast')) {
            new DataTable('#room_mast', { ordering: true, order: [], pageLength: 25 });
        }
    </script>
@endsection
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        $(document).on('click', '.qrcodebtn', function() {
            let dcode = $(this).data('dcode');
            let rcode = $(this).data('rcode');

            $.ajax({
                method: "POST",
                url: "{{ url('roomqrgenerator') }}",
                data: {
                    dcode: dcode,
                    rcode: rcode
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let link = document.createElement('a');
                        link.href = response.file_data;
                        link.download = response.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                    } else {
                        console.error("Error: " + response.message);
                        alert("Failed to generate QR Code");
                    }
                },
                error: function(xhr) {
                    console.error("Error generating QR", xhr);
                    alert("Error generating QR Code. Please try again.");
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var name = document.getElementById('name');
        var namelist = document.getElementById('namelist');
        var currentLiIndex = -1;
        if (!name || !namelist) {
            return;
        }
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
            xhr.open('POST', '/getroomnames', true);
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
