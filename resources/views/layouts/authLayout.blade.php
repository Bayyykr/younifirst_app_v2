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
</body>
</html>
