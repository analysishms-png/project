@extends('property.layouts.property')

@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-gear" style="color:#667eea;margin-right:8px;"></i> Payroll Parameters</h4>

    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.save-payroll-parameter') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 style="font-weight:700;color:#64748b;">PF Configuration</h6>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">PF Limit (₹)</label>
                            <input type="number" name="pflimit" class="form-control" value="{{ $param->pflimit ?? 15000 }}" style="border-radius:8px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Employee PF %</label>
                            <input type="number" name="pfemployee" class="form-control" value="{{ $param->pfemployee ?? 12 }}" step="0.01" style="border-radius:8px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Employer PF %</label>
                            <input type="number" name="pfemployer" class="form-control" value="{{ $param->pfemployer ?? 12 }}" step="0.01" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 style="font-weight:700;color:#64748b;">ESI Configuration</h6>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">ESI Limit (₹)</label>
                            <input type="number" name="esilimit" class="form-control" value="{{ $param->esilimit ?? 21000 }}" style="border-radius:8px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Employee ESI %</label>
                            <input type="number" name="esiemployee" class="form-control" value="{{ $param->esiemployee ?? 0.75 }}" step="0.01" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 style="font-weight:700;color:#64748b;">Working Days</h6>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Working Days/Month</label>
                            <input type="number" name="gworkingdaysInamonth" class="form-control" value="{{ $param->gworkingdaysInamonth ?? 30 }}" style="border-radius:8px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Days for Salary Calculation</label>
                            <input type="number" name="gdayssalary" class="form-control" value="{{ $param->gdayssalary ?? 30 }}" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 style="font-weight:700;color:#64748b;">Account Mapping</h6>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Salary Account</label>
                            <input type="text" name="salaryac" class="form-control" value="{{ $param->salaryac ?? '' }}" style="border-radius:8px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">Loan Account</label>
                            <input type="text" name="loanac" class="form-control" value="{{ $param->loanac ?? '' }}" style="border-radius:8px;">
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary" style="border-radius:10px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;padding:10px 40px;">
                        <i class="fa-solid fa-floppy-disk"></i> Save Parameters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
