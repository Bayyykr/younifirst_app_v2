<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Younifirst Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth.css'])
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Image Section -->
        <div class="image-section">
            <div class="image-wrapper">
                <img src="@yield('image')" alt="Auth Visual">
            </div>
        </div>

        <!-- Right Side - Form Section -->
        <div class="form-section">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" alt="Younifirst Logo">
                <span class="brand-name">Younifirst</span>
            </div>

            @yield('content')
        </div>
    </div>

    @stack('scripts')

    <!-- Global Toasts (Identical to Admin Role) -->
    @if (session('status'))
        <div class="toast-wrapper" id="successToast">
            <div class="toast-box toast-success">
                <div class="toast-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div class="toast-content">
                    <p>{{ session('status') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="toast-wrapper" id="errorToast">
            <div class="toast-box toast-error">
                <div class="toast-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <div class="toast-content">
                    <p>{{ $errors->first() }}</p>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successToast = document.getElementById('successToast');
            const errorToast = document.getElementById('errorToast');
            
            if (successToast) {
                setTimeout(() => {
                    successToast.classList.add('show');
                }, 100);
                setTimeout(() => {
                    successToast.classList.remove('show');
                }, 5000);
            }
            
            if (errorToast) {
                setTimeout(() => {
                    errorToast.classList.add('show');
                }, 100);
                setTimeout(() => {
                    errorToast.classList.remove('show');
                }, 5000);
            }
        });
    </script>
</body>
</html>
