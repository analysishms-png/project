@extends('property.layouts.main')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4><i class="fas fa-sync"></i> Recharge Cash Card</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('cashcard.recharge.store') }}" method="POST">
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
                            <label>Recharge Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" required min="1" step="0.01" id="rechargeAmount">
                        </div>
                        <div class="form-group">
                            <label>Payment Mode</label>
                            <select name="paymode" class="form-control">
                                <option value="CASH">CASH</option>
                                <option value="CARD">CARD</option>
                                <option value="UPI">UPI</option>
                                <option value="BANK">BANK TRANSFER</option>
                            </select>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('cashcard.list') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-sync"></i> Recharge</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
