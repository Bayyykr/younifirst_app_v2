<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Younifirst</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
</head>
<body class="antialiased" x-data="{ 
    darkMode: localStorage.getItem('darkMode') === 'true',
    sidebarOpen: true, 
    collapsed: false,
    showLogoutModal: false,
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
    }
}" :class="{ 'dark': darkMode }">
    <div class="admin-layout">
        
        <x-admin.sidebar />

        <div class="main-wrapper" :class="{ 'expanded': collapsed }">
            
            <x-admin.topbar />

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal-overlay" x-show="showLogoutModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">
        
        <div class="modal-content" @click.away="showLogoutModal = false"
             x-show="showLogoutModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             style="background: var(--bg-white); width: 100%; max-width: 400px; border-radius: 20px; padding: 32px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); text-align: center;">
            
            <div style="width: 64px; height: 64px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i data-lucide="log-out" style="width: 32px; height: 32px;"></i>
            </div>
            
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Konfirmasi Logout</h3>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 24px;">Apakah Anda yakin ingin keluar dari akun ini? Anda harus login kembali untuk mengakses dashboard.</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <button @click="showLogoutModal = false" 
                        style="padding: 12px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-white); color: var(--text-main); font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    Batal
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            style="width: 100%; padding: 12px; border-radius: 12px; border: none; background: #EF4444; color: white; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);">
                        Ya, Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
