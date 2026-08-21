@extends('property.layouts.main')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4><i class="fas fa-credit-card"></i> Register New Cash Card</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('cashcard.store') }}" method="POST" id="cashCardForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Card Number <span class="text-danger">*</span></label>
                                    <input type="text" name="cardno" class="form-control" required maxlength="20" placeholder="e.g. CC001" autofocus>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Guest Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guestname" class="form-control" required maxlength="100" placeholder="Guest name" style="text-transform:uppercase">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Room No</label>
                                    <select name="roomno" class="form-control">
                                        <option value="">-- Select Room --</option>
                                        @foreach($rooms as $room)
                                        <option value="{{ $room }}">{{ $room }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Initial Balance <span class="text-danger">*</span></label>
                                    <input type="number" name="balance" class="form-control" required min="0" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Security Deposit</label>
                                    <input type="number" name="security" class="form-control" min="0" step="0.01" value="0">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('cashcard.list') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Register Card</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
