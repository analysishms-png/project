@extends('property.layouts.main')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-credit-card"></i> Cash Card Master</h4>
                    <div>
                        <a href="{{ route('cashcard.register') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Register New</a>
                        <a href="{{ route('cashcard.recharge') }}" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> Recharge</a>
                        <a href="{{ route('cashcard.refund') }}" class="btn btn-danger btn-sm"><i class="fas fa-undo"></i> Refund</a>
                        <a href="{{ route('cashcard.history') }}" class="btn btn-info btn-sm"><i class="fas fa-history"></i> History</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="cashCardTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Card No</th>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Issue Date</th>
                                    <th>Expiry</th>
                                    <th class="text-right">Balance</th>
                                    <th class="text-right">Security</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $row->cardno }}</strong></td>
                                    <td>{{ $row->guestname }}</td>
                                    <td>{{ $row->roomno }}</td>
                                    <td>{{ $row->issuedate }}</td>
                                    <td>{{ $row->expirydate }}</td>
                                    <td class="text-right">₹{{ number_format($row->balance, 2) }}</td>
                                    <td class="text-right">₹{{ number_format($row->security, 2) }}</td>
                                    <td>
                                        @if($row->status == 'ACTIVE')
                                            <span class="badge badge-success">ACTIVE</span>
                                        @elseif($row->status == 'REFUNDED')
                                            <span class="badge badge-warning">REFUNDED</span>
                                        @elseif($row->status == 'BLOCKED')
                                            <span class="badge badge-dark">BLOCKED</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $row->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center text-muted">No cash cards found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
