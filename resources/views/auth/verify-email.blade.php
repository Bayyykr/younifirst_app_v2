@extends('layouts.authLayout')

@section('title', 'Periksa Email')

@section('image', asset('images/auth/periksa_email.png'))

@section('content')
    <a href="{{ route('password.request') }}" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </a>

    <div class="form-header">
        <h1>Periksa Email</h1>
        <p>Masukkan kode verifikasi yang dikirim ke <br><b>e41240238@student.polije.ac.id</b></p>
    </div>

    <form action="{{ route('verification.verify') }}" method="POST" id="otp-form">
        @csrf
        <div class="otp-container">
            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
            <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric">
            <input type="hidden" name="code" id="verification-code">
        </div>

        <div class="resend-section">
            <p class="timer-text">Kirim Ulang Kode ( 25s )</p>
            <a href="#" class="resend-link">Tidak menerima kode?</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    const inputs = document.querySelectorAll('.otp-input');
    const hiddenInput = document.getElementById('verification-code');

    inputs.forEach((input, index) => {
        input.addEventListener('keyup', (e) => {
            if (e.key >= 0 && e.key <= 9) {
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            } else if (e.key === 'Backspace') {
                if (index > 0) {
                    inputs[index - 1].focus();
                }
            }

            // Update hidden input
            let code = "";
            inputs.forEach(input => code += input.value);
            hiddenInput.value = code;

            // Auto submit if 4 digits entered
            if (code.length === 4) {
                window.location.href = "{{ route('password.reset') }}";
            }
        });

        // Prevent non-numeric input
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    });
</script>
@endpush
