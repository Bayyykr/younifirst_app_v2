@extends('layouts.authLayout')

@section('title', 'Periksa Email')

@section('image', asset('images/auth/periksa_email.png'))

@section('content')
    <a href="{{ route('password.request') }}" class="back-button" style="min-width: 48px !important; min-height: 48px !important; width: 48px !important; height: 48px !important; border-radius: 50% !important; flex-shrink: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; align-self: flex-start !important;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><polyline points="12 19 5 12 12 5"></polyline></svg>
    </a>

    <div class="form-header">
        <h1>Periksa Email</h1>
        <p>Masukkan kode verifikasi yang dikirim ke <b>{{ $email }}</b></p>
    </div>

    <form method="POST" action="{{ route('otp.verify.post') }}" id="otpForm">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}" />
        <input type="hidden" name="otp" id="otpHidden" value="" />

        <div class="otp-container">
            <input type="text" class="otp-input" placeholder="-" maxlength="1" data-index="0" inputmode="numeric" autofocus />
            <input type="text" class="otp-input" placeholder="-" maxlength="1" data-index="1" inputmode="numeric" />
            <input type="text" class="otp-input" placeholder="-" maxlength="1" data-index="2" inputmode="numeric" />
            <input type="text" class="otp-input" placeholder="-" maxlength="1" data-index="3" inputmode="numeric" />
        </div>

        <button type="submit" class="btn-submit" style="display:none;">Verifikasi</button>
    </form>

    <div class="resend-section">
        <span class="timer-text" id="timer">Kirim Ulang Kode ( <span id="countdown">25</span>s)</span>
        <a href="#" class="resend-link" id="resendLink" style="pointer-events: none; opacity: 0.5;">Tidak menerima kode?</a>
    </div>
@endsection

@push('scripts')
<script>
    const otpInputs = document.querySelectorAll('.otp-input');
    const otpHidden = document.getElementById('otpHidden');
    const otpForm = document.getElementById('otpForm');

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            // Only allow digits
            input.value = input.value.replace(/\D/g, '');

            if (input.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }

            // Auto-submit when all filled
            const otp = Array.from(otpInputs).map(i => i.value).join('');
            if (otp.length === 4) {
                otpHidden.value = otp;
                otpForm.submit();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        // Handle paste
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 4);
            pasteData.split('').forEach((char, i) => {
                if (otpInputs[index + i]) {
                    otpInputs[index + i].value = char;
                }
            });
            const otp = Array.from(otpInputs).map(i => i.value).join('');
            if (otp.length === 4) {
                otpHidden.value = otp;
                otpForm.submit();
            }
        });
    });

    // Countdown timer
    let remaining = 25;
    const countdownEl = document.getElementById('countdown');
    const resendLink = document.getElementById('resendLink');
    const timerEl = document.getElementById('timer');

    const interval = setInterval(() => {
        remaining--;
        countdownEl.textContent = remaining;
        if (remaining <= 0) {
            clearInterval(interval);
            timerEl.style.display = 'none';
            resendLink.style.pointerEvents = 'auto';
            resendLink.style.opacity = '1';
        }
    }, 1000);

    resendLink.addEventListener('click', (e) => {
        e.preventDefault();
        // Create a form and submit to resend OTP
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("otp.forgot") }}';
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        const emailInput = document.createElement('input');
        emailInput.type = 'hidden';
        emailInput.name = 'email';
        emailInput.value = '{{ $email }}';
        form.appendChild(emailInput);
        document.body.appendChild(form);
        form.submit();
    });
</script>
@endpush
