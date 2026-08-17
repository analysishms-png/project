@extends('frontend.layouts.main')

@section('body-class', 'login-page')

@section('content')
<style>
    /* ===== Analysis HMS Login — blue brand (UI-only redesign) ===== */
    .login-page {
        min-height: 100vh;
        background: linear-gradient(160deg, #0d6efd 0%, #084298 100%);
        position: relative;
        overflow-x: hidden;
    }

    /* subtle blue glow accents */
    .login-page::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -20%;
        width: 70vw;
        height: 70vw;
        background: radial-gradient(circle, rgba(61, 139, 253, .25) 0%, transparent 60%);
        pointer-events: none;
    }

    .login-page::after {
        content: '';
        position: absolute;
        bottom: -35%;
        left: -20%;
        width: 60vw;
        height: 60vw;
        background: radial-gradient(circle, rgba(61, 139, 253, .16) 0%, transparent 60%);
        pointer-events: none;
    }

    /* hide marketing chrome on the login gateway only */
    .login-page #topbar,
    .login-page #header,
    .login-page #footer {
        display: none !important;
    }

    .login-shell {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 460px;
        margin: 0 auto;
        padding: 3.5rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 100vh;
    }

    .login-card {
        border: 0 !important;
        border-radius: 1rem !important;
        box-shadow: 0 24px 64px rgba(4, 14, 32, .45) !important;
        overflow: hidden;
    }

    .login-brand {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 60%, #6ea8fe 160%);
        padding: 2rem 2rem 1.75rem;
        text-align: center;
    }

    .login-logo {
        width: 76px;
        height: 76px;
        margin: 0 auto 1rem;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(4, 14, 32, .35);
    }

    .login-logo img {
        max-height: 52px;
        max-width: 52px;
        border-radius: 50%;
    }

    .login-title {
        margin: 0;
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .login-tagline {
        margin: .35rem 0 0;
        color: rgba(248, 250, 252, .75);
        font-size: .85rem;
    }

    .login-card .card-body {
        padding: 2rem 2.25rem 1.5rem;
    }

    .login-page .form-label {
        font-weight: 600;
        color: #334155;
        font-size: .85rem;
    }

    .login-page .form-control {
        border-radius: .5rem;
        padding: .65rem .9rem;
        border-color: #cbd5e1;
        font-size: .9rem;
    }

    .login-page .form-control:focus {
        border-color: #3d8bfd;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .18);
    }

    .login-page .input-group-text {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #64748b;
        border-radius: .5rem 0 0 .5rem;
    }

    .login-page .input-group .form-control {
        border-radius: 0 .5rem .5rem 0;
    }

    .login-page .btn-primary {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        border-radius: .5rem;
        padding: .7rem 1rem;
        font-weight: 600;
        font-size: .95rem;
        letter-spacing: .02em;
        transition: all .2s ease;
    }

    .login-page .btn-primary:hover {
        background-color: #0a58ca !important;
        border-color: #0a58ca !important;
        box-shadow: 0 6px 16px rgba(13, 110, 253, .3);
    }

    .login-page .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .login-page .form-check-label {
        color: #475569;
        font-size: .875rem;
    }

    .login-page .btn-forgot {
        color: #64748b;
        font-size: .85rem;
    }

    .login-page .btn-forgot:hover {
        color: #0d6efd;
    }

    .login-demo {
        border-top: 1px solid #eef2f7;
        padding: 1.25rem 2.25rem 1.5rem;
        text-align: center;
    }

    .login-demo .btn-outline-demo {
        color: #0d6efd;
        border: 1.5px solid #0d6efd;
        border-radius: .5rem;
        font-weight: 600;
        font-size: .875rem;
        width: 100%;
        padding: .6rem 1rem;
        background: #ffffff;
        transition: all .2s ease;
    }

    .login-demo .btn-outline-demo:hover {
        background: #0d6efd;
        color: #ffffff;
    }

    .login-copyright {
        margin-top: 1.5rem;
        text-align: center;
        color: rgba(248, 250, 252, .5);
        font-size: .78rem;
    }

    @media (max-width: 575.98px) {
        .login-shell {
            padding: 2rem 1rem;
        }

        .login-card .card-body {
            padding: 1.5rem 1.25rem 1.25rem;
        }

        .login-demo {
            padding: 1rem 1.25rem 1.25rem;
        }

        .login-brand {
            padding: 1.5rem 1rem 1.25rem;
        }
    }
</style>

<div class="login-shell">
    <div class="login-card card">
        <div class="login-brand">
            <div class="login-logo">
                <img src="{{ asset('assets/img/logo.gif') }}" alt="Analysis HMS">
            </div>
            <h1 class="login-title">Analysis HMS</h1>
            <p class="login-tagline">Hotel Management System</p>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Username') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input id="u_name" type="text"
                            class="form-control @error('u_name') is-invalid @enderror" name="u_name"
                            value="{{ old('email') }}" required autocomplete="u_name" autofocus
                            placeholder="Enter username or email">
                    </div>
                    @error('u_name')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            required autocomplete="current-password" placeholder="Enter your password">
                    </div>
                    @error('password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="propertyid" class="form-label">{{ __('Property ID') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <input id="propertyid" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" type="text"
                            class="form-control @error('propertyid') is-invalid @enderror" name="propertyid"
                            value="{{ old('email') }}" required autocomplete="propertyid" autofocus
                            placeholder="Enter property ID">
                    </div>
                    @error('propertyid')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{
                            old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                    <a class="btn-forgot" href="{{ route('password.request') }}">
                        {{ __('Forgot Password?') }}
                    </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('Login') }}
                </button>
            </form>
        </div>

        <div class="login-demo">
            <a class="btn-outline-demo" href="{{ url('/') }}#demo-request">Don't Have Login ID & Password ? Request Demo Now..</a>
        </div>
    </div>

    <p class="login-copyright">© {{ date('Y') }} Analysis HMS. All rights reserved.</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const savedPropertyId = localStorage.getItem('propertyid');
        if (savedPropertyId) {
            document.getElementById('propertyid').value = savedPropertyId;
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const propertyId = document.getElementById('propertyid').value;
        if (propertyId) {
            localStorage.setItem('propertyid', propertyId);
        }
    });
</script>
@endsection
