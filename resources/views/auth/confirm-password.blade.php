@extends('layouts.authLayout')

@section('title', 'Konfirmasi Kata Sandi')

@section('image', asset('images/auth/confirm.png'))

@section('content')
    <div class="form-header">
        <h1>Konfirmasi Kata Sandi</h1>
        <p>Masukkan kata sandi Anda untuk melanjutkan.</p>
    </div>
    @if ($errors->any())
        <div class="alert-error" role="alert">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" name="password" required class="input-field" autofocus />
        </div>
        <button type="submit" class="btn-submit">Konfirmasi</button>
    </form>
@endsection
