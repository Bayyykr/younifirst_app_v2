@extends('layouts.authLayout')

@section('title', 'Lupa Kata Sandi')

@section('image', asset('images/auth/lupa_password.png'))

@section('content')
    <a href="{{ route('login') }}" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </a>

    <div class="form-header">
        <h1>Lupa Kata Sandi?</h1>
        <p>Jangan khawatir! Masukkan email SSO Anda untuk menerima instruksi reset kata sandi.</p>
    </div>

    <form action="{{ route('password.email') }}" method="POST" onsubmit="event.preventDefault(); window.location.href='{{ route('verification.notice') }}';">
        @csrf
        <div class="form-group">
            <label for="email">Email SSO Karyawan</label>
            <input type="email" id="email" name="email" placeholder="Masukkan email SSO Anda" required>
        </div>

        <button type="submit" class="btn-submit">Kirim</button>
    </form>
@endsection
