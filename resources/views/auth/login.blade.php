@extends('layouts.authLayout')

@section('title', 'Masuk')

@section('image', asset('images/auth/login.png'))

@section('content')
    <div class="form-header">
        <h1>Masuk ke Admin</h1>
        <p>Kelola sistem Younifirst</p>
    </div>

    <form action="{{ route('login') }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="error-summary" style="color: #dc2626; background: #fee2e2; padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="form-group">
            <label for="email">Email SSO Karyawan</label>
            <div class="input-wrapper">
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email SSO Anda" required autocomplete="username" autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <div class="input-wrapper">
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi Anda" required autocomplete="current-password">
                <div class="password-toggle" id="togglePassword">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
            </div>
        </div>

        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember">
                <span>Ingat Saya</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-password">Lupa Kata Sandi?</a>
            @endif
        </div>

        <button type="submit" class="btn-submit">Masuk</button>
    </form>
@endsection

@push('scripts')
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        if (type === 'text') {
            this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-off-icon"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
        } else {
            this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        }
    });
</script>
@endpush
