@extends('layouts.authLayout')

@section('title', 'Atur Ulang Kata Sandi')

@section('image', asset('images/auth/atur_ulang_sandi.png'))

@section('content')
    <a href="{{ route('verification.notice') }}" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </a>

    <div class="form-header">
        <h1>Atur Ulang Kata Sandi</h1>
        <p>Buat kata sandi baru agar masuk nanti lebih mudah harus berbeda dari kata sandi yang sebelumnya pernah digunakan.</p>
    </div>

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <div class="input-wrapper">
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi baru" required>
                <div class="password-toggle" onclick="togglePwd('password', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Kata Sandi</label>
            <div class="input-wrapper">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Tulis balik kata sandi baru" required>
                <div class="password-toggle" onclick="togglePwd('password_confirmation', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-submit">Atur ulang kata sandi</button>
    </form>
@endsection

@push('scripts')
<script>
    function togglePwd(id, el) {
        const input = document.getElementById(id);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        
        if (type === 'text') {
            el.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-off-icon"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
        } else {
            el.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        }
    }
</script>
@endpush
