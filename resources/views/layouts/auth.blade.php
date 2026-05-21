<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Younifirst' }}</title>
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body class="auth-bg">
    <div class="auth-container">
        {{ $slot }}
    </div>
</body>
</html>
