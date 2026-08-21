@extends('property.layouts.main')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4><i class="fas fa-undo"></i> Refund Cash Card</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Refund will set card balance to ₹0 and mark status as REFUNDED.
                    </div>

                    <form action="{{ route('cashcard.refund.store') }}" method="POST" onsubmit="return confirm('Are you sure you want to refund this card? This action cannot be undone.')">
                        @csrf
                        <div class="form-group">
                            <label>Select Card <span class="text-danger">*</span></label>
                            <select name="cardno" class="form-control" required id="cardSelect">
                                <option value="">-- Select Card --</option>
                                @foreach($cards as $card)
                                <option value="{{ $card->cardno }}" data-balance="{{ $card->balance }}">
                                    {{ $card->cardno }} — {{ $card->guestname }} (Bal: ₹{{ number_format($card->balance, 2) }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reason / Remark</label>
                            <textarea name="remark" class="form-control" rows="3" placeholder="Reason for refund..."></textarea>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('cashcard.list') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-undo"></i> Process Refund</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
