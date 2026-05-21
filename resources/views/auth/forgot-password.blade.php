@extends('layouts.authLayout')

@section('title', 'Lupa Kata Sandi')

@section('image', asset('images/auth/lupa_password.png'))

@section('content')
    <a href="{{ route('login') }}" class="back-button" style="min-width: 48px !important; min-height: 48px !important; width: 48px !important; height: 48px !important; border-radius: 50% !important; flex-shrink: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; align-self: flex-start !important;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>
    </a>

    <div class="form-header">
        <h1>Lupa Kata Sandi?</h1>
        <p>Jangan khawatir! Masukkan email SSO Anda untuk menerima instruksi reset kata sandi.</p>
    </div>

    <form method="POST" action="{{ route('otp.forgot') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email SSO Karyawan</label>
            <div class="input-wrapper">
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email SSO Anda" required autofocus>
            </div>
        </div>

        <button type="submit" class="btn-submit">KIRIM</button>
    </form>
@endsection
