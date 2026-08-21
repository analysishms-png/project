@extends('property.layouts.main')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-history"></i> Cash Card Transaction History</h4>
                    <a href="{{ route('cashcard.list') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('cashcard.history') }}" class="form-inline mb-3">
                        <div class="form-group mr-2">
                            <label class="mr-2">Select Card</label>
                            <select name="cardno" class="form-control" required>
                                <option value="">-- Select Card --</option>
                                @foreach($allCards as $c)
                                <option value="{{ $c->cardno }}" {{ (request('cardno') == $c->cardno) ? 'selected' : '' }}>
                                    {{ $c->cardno }} — {{ $c->guestname }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> View</button>
                    </form>

                    @if($card)
                    <div class="alert alert-info">
                        <strong>Card:</strong> {{ $card->cardno }} |
                        <strong>Guest:</strong> {{ $card->guestname }} |
                        <strong>Room:</strong> {{ $card->roomno }} |
                        <strong>Balance:</strong> ₹{{ number_format($card->balance, 2) }} |
                        <strong>Status:</strong> {{ $card->status }}
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance After</th>
                                    <th>Pay Mode</th>
                                    <th>Room</th>
                                    <th>Remark</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->vdate }}</td>
                                    <td>
                                        @if($row->vtype == 'ISSUE')
                                            <span class="badge badge-primary">ISSUE</span>
                                        @elseif($row->vtype == 'RECHARGE')
                                            <span class="badge badge-success">RECHARGE</span>
                                        @elseif($row->vtype == 'REFUND')
                                            <span class="badge badge-danger">REFUND</span>
                                        @elseif($row->vtype == 'ADJUST')
                                            <span class="badge badge-warning">ADJUST</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $row->vtype }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right" style="color: {{ $row->amount >= 0 ? 'green' : 'red' }}">
                                        ₹{{ number_format($row->amount, 2) }}
                                    </td>
                                    <td class="text-right">₹{{ number_format($row->balance, 2) }}</td>
                                    <td>{{ $row->paymode }}</td>
                                    <td>{{ $row->roomno }}</td>
                                    <td>{{ $row->remark }}</td>
                                    <td>{{ $row->u_name }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center text-muted">No transactions found</td></tr>
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
