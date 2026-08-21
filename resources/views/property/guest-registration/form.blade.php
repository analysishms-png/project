<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Guest Registration — {{ $reservationNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', system-ui, sans-serif; }
        .reg-card { max-width: 480px; margin: 20px auto; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden; }
        .reg-header { background: linear-gradient(135deg, #1e3a5f, #2d5a87); color: white; padding: 24px; text-align: center; }
        .reg-header h2 { font-size: 20px; margin: 0; font-weight: 600; }
        .reg-header .subtitle { font-size: 13px; opacity: 0.8; margin-top: 4px; }
        .reg-header .res-badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 16px; border-radius: 20px; font-size: 12px; margin-top: 10px; }
        .booking-summary { background: #f8fafc; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; }
        .booking-summary .bs-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px; }
        .booking-summary .bs-label { color: #64748b; }
        .booking-summary .bs-value { font-weight: 600; color: #1e293b; }
        .section-title { font-size: 14px; font-weight: 600; color: #1e293b; padding: 16px 20px 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-section { padding: 0 20px 16px; }
        .form-section .form-label { font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .form-section .form-control { border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px 12px; font-size: 14px; }
        .form-section .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .btn-submit { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; border-radius: 10px; padding: 14px; font-size: 16px; font-weight: 600; width: 100%; }
        .btn-submit:hover { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .powered-by { text-align: center; padding: 12px; font-size: 11px; color: #94a3b8; }
        .divider { border-top: 1px solid #e2e8f0; margin: 8px 20px; }
        .alert { border-radius: 10px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="reg-card">
        <div class="reg-header">
            <h2><i class="ri-building-2-line me-1"></i>Guest Registration</h2>
            <div class="subtitle">Complete your registration before check-in</div>
            <div class="res-badge"><i class="ri-file-list-3-line me-1"></i>Reservation: {{ $reservationNo }}</div>
        </div>

        <!-- Booking Summary -->
        <div class="booking-summary">
            <div class="bs-row">
                <span class="bs-label">Guest Name</span>
                <span class="bs-value">{{ $booking->GuestName ?? '—' }}</span>
            </div>
            <div class="bs-row">
                <span class="bs-label">Room Type</span>
                <span class="bs-value">{{ $booking->RoomType ?? '—' }}</span>
            </div>
            <div class="bs-row">
                <span class="bs-label">Arrival</span>
                <span class="bs-value">{{ $booking->ArrDate ?? '—' }}</span>
            </div>
            <div class="bs-row">
                <span class="bs-label">Departure</span>
                <span class="bs-value">{{ $booking->DepDate ?? '—' }}</span>
            </div>
            @if($booking->Advance && $booking->Advance > 0)
            <div class="bs-row">
                <span class="bs-label">Advance Paid</span>
                <span class="bs-value text-success">₹{{ number_format($booking->Advance) }}</span>
            </div>
            @endif
        </div>

        <form id="registrationForm" action="{{ url('guest-registration/' . $reservationNo) }}" method="POST">
            @csrf
            @method('POST')

            @if($errors->any())
            <div class="m-3">
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-1"></i>Please correct the errors below.
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="m-3">
                <div class="alert alert-danger">{{ session('error') }}</div>
            </div>
            @endif

            <!-- Personal Details -->
            <div class="section-title"><i class="ri-user-3-line me-1"></i>Personal Details</div>
            <div class="form-section">
                <div class="mb-3">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name', $existingGuest->name ?? $booking->GuestName ?? '') }}" required
                           placeholder="Enter full name as per ID proof">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Mobile <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('mobile') is-invalid @enderror"
                               name="mobile" value="{{ old('mobile', $existingGuest->mobile_no ?? '') }}" required
                               placeholder="10-digit mobile" maxlength="15">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email', $existingGuest->email_id ?? '') }}"
                               placeholder="email@example.com">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender">
                            <option value="">Select</option>
                            <option value="Male" {{ (old('gender', $existingGuest->gender ?? '') == 'Male') ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ (old('gender', $existingGuest->gender ?? '') == 'Female') ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ (old('gender', $existingGuest->gender ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="dob" value="{{ old('dob') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nationality</label>
                    <input type="text" class="form-control" name="nationality"
                           value="{{ old('nationality', $existingGuest->nationality ?? 'Indian') }}"
                           placeholder="e.g. Indian">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Address -->
            <div class="section-title"><i class="ri-map-pin-line me-1"></i>Address</div>
            <div class="form-section">
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="address"
                           value="{{ old('address', $existingGuest->add1 ?? '') }}"
                           placeholder="Street address">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city"
                               value="{{ old('city', $existingGuest->city ?? '') }}" placeholder="City">
                    </div>
                    <div class="col-6">
                        <label class="form-label">State</label>
                        <input type="text" class="form-control" name="state"
                               value="{{ old('state', $existingGuest->state_name ?? '') }}" placeholder="State">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" name="country"
                               value="{{ old('country', $existingGuest->country_name ?? 'India') }}" placeholder="Country">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Pincode</label>
                        <input type="text" class="form-control" name="pincode"
                               value="{{ old('pincode', $existingGuest->zip_code ?? '') }}" placeholder="Pincode" maxlength="10">
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- ID Proof -->
            <div class="section-title"><i class="ri-card-line me-1"></i>ID Proof</div>
            <div class="form-section">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">ID Type</label>
                        <select class="form-select" name="id_type">
                            <option value="">Select</option>
                            <option value="Aadhaar Card" {{ (old('id_type', $existingGuest->idproof_type ?? '') == 'Aadhaar Card') ? 'selected' : '' }}>Aadhaar Card</option>
                            <option value="Passport" {{ (old('id_type', $existingGuest->idproof_type ?? '') == 'Passport') ? 'selected' : '' }}>Passport</option>
                            <option value="PAN Card" {{ (old('id_type', $existingGuest->idproof_type ?? '') == 'PAN Card') ? 'selected' : '' }}>PAN Card</option>
                            <option value="Driving License" {{ (old('id_type', $existingGuest->idproof_type ?? '') == 'Driving License') ? 'selected' : '' }}>Driving License</option>
                            <option value="Voter ID" {{ (old('id_type', $existingGuest->idproof_type ?? '') == 'Voter ID') ? 'selected' : '' }}>Voter ID</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">ID Number</label>
                        <input type="text" class="form-control" name="id_number"
                               value="{{ old('id_number', $existingGuest->idproof_no ?? '') }}"
                               placeholder="ID number">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">ID Expiry Date</label>
                    <input type="date" class="form-control" name="id_expiry"
                           value="{{ old('id_expiry', $existingGuest->expiryDate ?? '') }}">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Additional Info -->
            <div class="section-title"><i class="ri-information-line me-1"></i>Additional Information</div>
            <div class="form-section">
                <div class="mb-3">
                    <label class="form-label">Company Name (if corporate)</label>
                    <input type="text" class="form-control" name="company_name"
                           value="{{ old('company_name') }}" placeholder="Company name">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Expected Arrival Time</label>
                        <input type="time" class="form-control" name="arrival_time"
                               value="{{ old('arrival_time') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Purpose of Visit</label>
                        <select class="form-select" name="purpose_of_visit">
                            <option value="">Select</option>
                            <option value="Business" {{ old('purpose_of_visit') == 'Business' ? 'selected' : '' }}>Business</option>
                            <option value="Leisure" {{ old('purpose_of_visit') == 'Leisure' ? 'selected' : '' }}>Leisure</option>
                            <option value="Conference" {{ old('purpose_of_visit') == 'Conference' ? 'selected' : '' }}>Conference</option>
                            <option value="Wedding" {{ old('purpose_of_visit') == 'Wedding' ? 'selected' : '' }}>Wedding</option>
                            <option value="Transit" {{ old('purpose_of_visit') == 'Transit' ? 'selected' : '' }}>Transit</option>
                            <option value="Other" {{ old('purpose_of_visit') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Special Requests</label>
                    <textarea class="form-control" name="special_requests" rows="3"
                              placeholder="e.g. Extra pillows, late checkout, non-smoking room...">{{ old('special_requests', $existingGuest->spl_instr ?? '') }}</textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="form-section pb-4">
                <button type="submit" class="btn btn-submit">
                    <i class="ri-check-double-line me-1"></i>Complete Registration
                </button>
            </div>
        </form>

        <div class="powered-by">
            Powered by <strong>Analysis HMS</strong> | Digital Registration
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
