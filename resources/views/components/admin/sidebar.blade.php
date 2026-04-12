<aside class="sidebar" :class="{ 'collapsed': collapsed }">
    <div class="sidebar-header">
        <div class="logo-section" x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
            <img src="{{ asset('images/logo.png') }}" alt="Younifirst Logo">
            <span>Younifirst</span>
        </div>
        <button class="collapse-btn" @click="collapsed = !collapsed" :title="collapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
            <i data-lucide="menu"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard"></i>
            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Dashboard</span>
        </a>
        <a href="#" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <i data-lucide="users"></i>
            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">User Management</span>
        </a>
        <a href="#" class="nav-item {{ request()->routeIs('admin.events') ? 'active' : '' }}">
            <i data-lucide="calendar"></i>
            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Event Management</span>
        </a>
        <a href="#" class="nav-item {{ request()->routeIs('admin.team') ? 'active' : '' }}">
            <i data-lucide="monitor"></i>
            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Team Monitoring</span>
        </a>
        <a href="#" class="nav-item {{ request()->routeIs('admin.lostfound') ? 'active' : '' }}">
            <i data-lucide="search"></i>
            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Lost and Found</span>
        </a>

        <div class="nav-divider"></div>

        <a href="#" class="nav-item {{ request()->routeIs('admin.announcement') ? 'active' : '' }}">
            <i data-lucide="megaphone"></i>
            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Announcement</span>
        </a>
        <a href="#" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <i data-lucide="settings"></i>
            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Settings</span>
        </a>

        <div style="margin-top: auto;">
            <a href="#" class="nav-item logout">
                <i data-lucide="log-out"></i>
                <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Logout</span>
            </a>
        </div>
    </nav>
</aside>
