@extends('layouts.authLayout')

@section('title', 'Atur Ulang Kata Sandi')

@section('image', asset('images/auth/atur_ulang_sandi.png'))

@section('content')
    <a href="{{ route('otp.verify') }}" class="back-button" style="min-width: 48px !important; min-height: 48px !important; width: 48px !important; height: 48px !important; border-radius: 50% !important; flex-shrink: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; align-self: flex-start !important; position: relative; top: 35px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>
    </a>

    <div class="form-header">
        <h1>Atur Ulang Kata Sandi</h1>
        <p>Buat kata sandi baru. Kata sandi baru Anda harus berbeda dari kata sandi yang sebelumnya pernah digunakan.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ request()->email }}">

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <div class="input-wrapper">
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi baru Anda" required autocomplete="new-password">
                <div class="password-toggle" id="togglePassword">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
            </div>
            <div style="color: #dc2626; font-size: 0.75rem; margin-top: 0.5rem;">Harus terdiri dari minimal 8 karakter, termasuk huruf, angka, dan karakter khusus.</div>
        </div>

    <div class="form-group" style="margin-top: 1.5rem;">
            <label for="password_confirmation">Konfirmasi Kata Sandi</label>
            <div class="input-wrapper">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi kata sandi Anda" required autocomplete="new-password">
                <div class="password-toggle" id="togglePasswordConfirm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
            </div>
            <div style="color: #dc2626; font-size: 0.75rem; margin-top: 0.5rem;">Kata sandi baru dan konfirmasi kata sandi harus sama.</div>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 2rem; margin-bottom: 3rem;">RESET KATA SANDI</button>
    </form>
@endsection

@push('scripts')
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        if (type === 'text') {
            this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-off-icon"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
        } else {
            this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        }
    });

    const togglePasswordConfirm = document.querySelector('#togglePasswordConfirm');
    const passwordConfirm = document.querySelector('#password_confirmation');

    togglePasswordConfirm.addEventListener('click', function () {
        const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirm.setAttribute('type', type);
        
        if (type === 'text') {
            this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-off-icon"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
        } else {
            this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        }
    });
</script>
@endpush
