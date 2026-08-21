@extends('property.layouts.main')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-list-ol"></i> Call Code Master</h4>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                        <i class="fas fa-plus"></i> Add Call Code
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="callCodeTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="80">#</th>
                                    <th>STD Code</th>
                                    <th>Call Type</th>
                                    <th>Description</th>
                                    <th>Pulse (Sec)</th>
                                    <th width="100">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $row->stdcode }}</strong></td>
                                    <td><span class="badge badge-info">{{ $row->calltype ?? 'N/A' }}</span></td>
                                    <td>{{ $row->description }}</td>
                                    <td class="text-center">{{ $row->pulseinsec }}</td>
                                    <td>
                                        <form action="{{ route('callcode.delete') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this call code?')">
                                            @csrf
                                            <input type="hidden" name="stdcode" value="{{ $row->stdcode }}">
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">No call codes found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('callcode.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-list-ol"></i> Add Call Code</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>STD Code <span class="text-danger">*</span></label>
                        <input type="text" name="stdcode" class="form-control" required maxlength="20" placeholder="e.g. 0512, 011, 91">
                    </div>
                    <div class="form-group">
                        <label>Call Type <span class="text-danger">*</span></label>
                        <select name="calltypecode" class="form-control" required>
                            <option value="">-- Select --</option>
                            @foreach($calltypes as $ct)
                            <option value="{{ $ct->code }}">{{ $ct->calltype }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" maxlength="100" placeholder="e.g. KANPUR LOCAL">
                    </div>
                    <div class="form-group">
                        <label>Pulse (Seconds)</label>
                        <input type="number" name="pulseinsec" class="form-control" value="60" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
