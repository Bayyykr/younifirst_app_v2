<header class="topbar" style="overflow: visible !important;">
    <div class="page-title">
        @yield('page_title', 'Dashboard')
    </div>

    <div class="topbar-right" style="display: flex !important; align-items: center !important; gap: 20px !important;">
        <button class="icon-btn">
            <i data-lucide="bell"></i>
            <span class="badge"></span>
        </button>
        <button class="icon-btn" @click="toggleDarkMode()">
            <i x-show="!darkMode" data-lucide="moon"></i>
            <i x-show="darkMode" data-lucide="sun"></i>
        </button>

        <div class="profile-wrapper" x-data="{ open: false }" @click.away="open = false" style="position: relative !important; display: flex !important; align-items: center !important; padding-left: 20px !important; border-left: 1px solid var(--border-color) !important; height: 40px !important;">
            <div class="profile-trigger" @click="open = !open" style="display: flex !important; align-items: center !important; gap: 12px !important; cursor: pointer !important; white-space: nowrap !important;">
                <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=3B82F6&color=fff' }}" alt="{{ Auth::user()->name }}" class="profile-avatar" style="width: 40px !important; height: 40px !important; border-radius: 50% !important; object-fit: cover !important; flex-shrink: 0 !important;">
                <div class="profile-info" style="display: flex !important; flex-direction: column !important; justify-content: center !important; line-height: 1.2 !important;">
                    <span class="profile-name" style="font-size: 0.875rem !important; font-weight: 600 !important; color: var(--text-main) !important;">{{ Auth::user()->name }}</span>
                    <span class="profile-role" style="font-size: 0.75rem !important; color: var(--text-muted) !important;">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
                <i data-lucide="chevron-down" :class="{ 'rotate-180': open }" style="width: 16px !important; height: 16px !important; transition: transform 0.2s !important; flex-shrink: 0 !important; color: var(--text-muted) !important;"></i>
            </div>

            <div class="dropdown-menu" x-show="open" 
                x-transition:enter="transition ease-out duration-100" 
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100" 
                x-transition:leave="transition ease-in duration-75" 
                x-transition:leave-start="opacity-100 scale-100" 
                x-transition:leave-end="opacity-0 scale-95" 
                style="display: none; position: absolute !important; top: calc(100% + 15px) !important; right: 0 !important; width: 220px !important; background: var(--bg-white) !important; border: 1px solid var(--border-color) !important; border-radius: 12px !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; z-index: 100 !important; padding: 8px !important;">
                
                <div class="dropdown-header" style="padding: 8px 12px !important;">
                    <strong style="display: block !important; font-size: 0.875rem !important; color: var(--text-main) !important;">{{ Auth::user()->name }}</strong>
                    <span style="font-size: 0.75rem !important; color: var(--text-muted) !important;">{{ Auth::user()->email }}</span>
                </div>
                
                <div class="dropdown-divider" style="height: 1px !important; background: var(--border-color) !important; margin: 8px 0 !important;"></div>
                
                <a href="{{ route('profile.edit') }}" class="dropdown-item" style="display: flex !important; align-items: center !important; gap: 10px !important; padding: 10px 12px !important; font-size: 0.875rem !important; color: var(--text-main) !important; border-radius: 8px !important; text-decoration: none !important;">
                    <i data-lucide="user" style="width: 16px !important; height: 16px !important; color: var(--primary) !important;"></i>
                    <span>Profile Settings</span>
                </a>
                
                <div class="dropdown-divider" style="height: 1px !important; background: var(--border-color) !important; margin: 8px 0 !important;"></div>
                
                <button type="button" @click="showLogoutModal = true" class="dropdown-item logout-item" style="width: 100% !important; border: none !important; background: none !important; display: flex !important; align-items: center !important; gap: 10px !important; padding: 10px 12px !important; font-size: 0.875rem !important; color: var(--danger) !important; border-radius: 8px !important; cursor: pointer !important;">
                    <i data-lucide="log-out" style="width: 16px !important; height: 16px !important;"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
</header>



