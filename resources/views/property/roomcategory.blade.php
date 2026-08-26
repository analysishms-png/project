@extends('property.layouts.main')
@section('main-container')
    <div class="content-body">
        <div class="container-fluid">
            @include('property.layouts.pageheader', ['hmsTitle' => 'Room Category', 'hmsSubtitle' => 'Manage room categories and rates'])

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-white font-weight-bold"><i class="fas fa-tags mr-2"></i>Add Room Category</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="form" name="roomcatstoreform" id="roomcatstoreform"
                                action="{{ route('roomcatstore') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="type">Room Type</label>
                                            <input type="text" name="type" id="type" class="form-control form-control-sm" required>
                                            <div id="namelist"></div>
                                            @error('type')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="shortname">Short Name</label>
                                            <input type="text" name="shortname" id="shortname" class="form-control form-control-sm" required>
                                            @error('shortname')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="rev_code">Revenue Charge</label>
                                            <select id="rev_code" name="rev_code" class="form-control form-control-sm" required>
                                                <option value="">Select Revenue Charge</option>
                                                @foreach ($revmastdata as $list)
                                                    <option value="{{ $list->rev_code }}">{{ $list->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('rev_code')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="ammenties">Amenities</label>
                                            <input type="text" name="ammenties" id="ammenties" class="form-control form-control-sm">
                                            @error('ammenties')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="image_path">Images</label>
                                            <input type="file" name="image_path[]" id="image_path" class="form-control-file"
                                                accept="image/*" multiple>
                                            @error('image_path')
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
                                            <label class="font-weight-bold text-secondary small" for="norooms">Rooms Available</label>
                                            <input type="text" oninput="checkNumMax(this, 5)" name="norooms" id="norooms"
                                                class="form-control form-control-sm" required>
                                            @error('norooms')
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

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-secondary small" for="map_code">Map Code</label>
                                            <input type="text" name="map_code" id="map_code" class="form-control form-control-sm">
                                            @error('map_code')
                                                <span class="text-danger small"> {{ $message }} </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <h6 class="font-weight-bold text-primary mb-2">Rate Structure</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm table-striped" id="gridtaxstructure">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Occ Type</th>
                                                        @if (empty($envirodata->rate1))
                                                            <th>High Rate</th>
                                                        @else
                                                            <th>{{ $envirodata->rate1 }}</th>
                                                        @endif
                                                        @if (empty($envirodata->rate2))
                                                            <th>Rack Rate</th>
                                                        @else
                                                            <th>{{ $envirodata->rate2 }}</th>
                                                        @endif
                                                        @if (empty($envirodata->rate3))
                                                            <th>Disk 1 Rate</th>
                                                        @else
                                                            <th>{{ $envirodata->rate3 }}</th>
                                                        @endif
                                                        @if (empty($envirodata->rate4))
                                                            <th>Disk 2 Rate</th>
                                                        @else
                                                            <th>{{ $envirodata->rate4 }}</th>
                                                        @endif
                                                        @if (empty($envirodata->rate5))
                                                            <th>Disk 3 Rate</th>
                                                        @else
                                                            <th>{{ $envirodata->rate5 }}</th>
                                                        @endif
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
                            <h5 class="card-title mb-0 font-weight-bold text-dark"><i class="fas fa-list mr-2"></i>Room Category List</h5>
                            <div class="d-flex gap-2">
                                <button type="button" onclick="window.location.href='{{ route('roomcategory.export') }}'"
                                    class="btn btn-success btn-sm shadow-sm mr-2"><i class="fas fa-file-excel mr-1"></i> Excel</button>
                                <button type="button" onclick="window.open('{{ route('printroomcategory') }}','_blank')"
                                    class="btn btn-info btn-sm text-white shadow-sm"><i class="fas fa-print mr-1"></i> Print</button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table id="room_cat" class="table table-hover table-striped table-bordered table-sm table-radio" style="font-size:12px; width:100%;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Sn.</th>
                                            <th>Cat Code</th>
                                            <th>Map Code</th>
                                            <th>Name</th>
                                            <th>Revenue Name</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sn = 1; @endphp
                                        @foreach ($data as $row)
                                            <tr>
                                                <td>{{ $sn }}</td>
                                                <td><b>{{ $row->cat_code }}</b></td>
                                                <td>{{ $row->map_code }}</td>
                                                <td>{{ $row->name }}</td>
                                                <td>{{ $row->taxname }}</td>
                                                <td class="text-center ins">
                                                    <a
                                                        href="updateroomcategory?sn={{ base64_encode($row->sn) }}&cat_code={{ base64_encode($row->cat_code) }}&rev_code={{ base64_encode($row->rev_code) }}" class="btn btn-success btn-sm py-0 px-2 mr-1">
                                                        <i class="fa-regular fa-pen-to-square"></i> Edit
                                                    </a>
                                                    <a
                                                        href="deleteroomcat?sn={{ base64_encode($row->sn) }}&cat_code={{ base64_encode($row->cat_code) }}" class="btn btn-danger btn-sm py-0 px-2">
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
            if ($.fn.DataTable.isDataTable('#room_cat')) {
                $('#room_cat').DataTable().destroy();
            }
            new DataTable('#room_cat', {
                dom: 'frtip',
                ordering: true,
                order: []
            });
        });
    </script>
@endsection
<script>
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
