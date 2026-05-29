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

    <!-- Scripts & Theme -->
    <script>
        // Early theme application to prevent FOUC
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Register Alpine Store
        document.addEventListener('alpine:init', () => {
            Alpine.store('darkMode', {
                on: localStorage.getItem('darkMode') === 'true',
                toggle() {
                    this.on = !this.on;
                    localStorage.setItem('darkMode', this.on);
                    this.updateClass();
                },
                updateClass() {
                    if (this.on) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css'])

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --bg-main: #F8FAFC;
            --bg-white: #FFFFFF;
            --text-main: #1E293B;
            --text-muted: #64748B;
            --border-color: #F1F5F9;
            --bg-hover: #F1F5F9;
            --bg-glass: rgba(255, 255, 255, 0.8);
        }

        html.dark {
            --bg-main: #0F172A !important;
            --bg-white: #1E293B !important;
            --text-main: #F1F5F9 !important;
            --text-muted: #94A3B8 !important;
            --border-color: #334155 !important;
            --bg-hover: #2D3748 !important;
            --bg-glass: rgba(30, 41, 59, 0.8) !important;
            --primary-light: rgba(59, 130, 246, 0.1) !important;
        }

        /* Sidebar Hover Fix */
        html.dark .nav-item:hover {
            background-color: var(--bg-hover) !important;
            color: #fff !important;
        }

        html.dark .nav-item.active {
            background-color: rgba(59, 130, 246, 0.2) !important;
            color: var(--primary) !important;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
        }
    </style>

    @stack('styles')
</head>

<body class="antialiased" x-data="{
    sidebarOpen: window.innerWidth > 768,
    collapsed: false,
    showLogoutModal: false,
    isMobile() {
        return window.innerWidth <= 768;
    },
    closeSidebarOnMobile() {
        if (this.isMobile()) this.sidebarOpen = false;
    }
}" @resize.window="sidebarOpen = window.innerWidth > 768 ? true : sidebarOpen; if (window.innerWidth <= 768) collapsed = false;">
    <div class="admin-layout">

        <x-admin.sidebar />

        <div class="sidebar-backdrop" x-show="sidebarOpen && isMobile()" x-cloak @click="sidebarOpen = false"></div>

        <div class="main-wrapper" :class="{ 'expanded': collapsed }">

            <x-admin.topbar />

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal-overlay" x-show="showLogoutModal" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">

        <div class="modal-content" @click.away="showLogoutModal = false" x-show="showLogoutModal"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            style="background: var(--bg-white); width: 100%; max-width: 400px; border-radius: 20px; padding: 32px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); text-align: center;">

            <div
                style="width: 64px; height: 64px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-log-out">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" x2="9" y1="12" y2="12" />
                </svg>
            </div>

            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Konfirmasi
                Logout</h3>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 24px;">Apakah Anda yakin ingin
                keluar dari akun ini? Anda harus login kembali untuk mengakses dashboard.</p>

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
