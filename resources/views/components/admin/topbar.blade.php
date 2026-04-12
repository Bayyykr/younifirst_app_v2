<header class="topbar">
    <div class="page-title">
        @yield('page_title', 'Dashboard')
    </div>

    <div class="topbar-right">
        <button class="icon-btn">
            <i data-lucide="bell"></i>
            <span class="badge"></span>
        </button>
        <button class="icon-btn">
            <i data-lucide="moon"></i>
        </button>

        <div class="profile-dropdown">
            <img src="https://ui-avatars.com/api/?name=Rafayel+Qi&background=3B82F6&color=fff" alt="User Avatar" class="profile-avatar">
            <div class="profile-info">
                <span class="profile-name">Rafayel Qi</span>
                <span class="profile-role">Admin</span>
            </div>
            <i data-lucide="chevron-down" style="width: 16px; height: 16px; margin-left: 8px;"></i>
        </div>
    </div>
</header>
