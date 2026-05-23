@php
    use App\Models\Event;
    use App\Models\LostfoundItem;
    use App\Models\Team;
    use App\Models\TeamMember;
    use Illuminate\Support\Str;

    $currentUser = Auth::user();
    $canSeeAdminNotifications = $currentUser?->role === 'admin';
    $canSeeLostfoundNotifications = in_array($currentUser?->role, ['admin', 'satpam'], true);
    $notifications = collect();

    if ($canSeeAdminNotifications && ($currentUser->notify_event ?? true)) {
        Event::with('creator')
            ->where('status', 'pending')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->each(function ($event) use ($notifications) {
                $notifications->push([
                                    'id' => 'event-' . $event->event_id,
                                    'type' => 'Event',
                    'title' => $event->title,
                    'message' => 'Event baru menunggu persetujuan' . ($event->creator ? ' dari ' . $event->creator->name : ''),
                    'time' => optional($event->created_at)->diffForHumans(),
                    'url' => route('admin.events'),
                    'icon' => 'calendar',
                    'created_at' => $event->created_at,
                ]);
            });
    }

    if ($canSeeAdminNotifications && ($currentUser->notify_team ?? true)) {
        Team::where('status', 'pending')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->each(function ($team) use ($notifications) {
                $notifications->push([
                                    'id' => 'team-' . $team->team_id,
                                    'type' => 'Tim',
                    'title' => $team->team_name,
                    'message' => 'Pengajuan tim baru menunggu persetujuan',
                    'time' => optional($team->created_at)->diffForHumans(),
                    'url' => route('admin.teams'),
                    'icon' => 'users',
                    'created_at' => $team->created_at,
                ]);
            });

        TeamMember::with(['team', 'user'])
            ->where('status', 'pending')
            ->where('role', 'member')
            ->orderBy('member_id', 'desc')
            ->take(5)
            ->get()
            ->each(function ($member) use ($notifications) {
                $notifications->push([
                                    'id' => 'member-' . $member->member_id,
                                    'type' => 'Member',
                    'title' => $member->user->name ?? 'Anggota baru',
                    'message' => 'Permohonan bergabung ke tim ' . ($member->team->team_name ?? 'tim'),
                    'time' => 'Menunggu review',
                    'url' => route('admin.teams'),
                    'icon' => 'user-plus',
                    'created_at' => null,
                ]);
            });
    }

    if ($canSeeLostfoundNotifications && ($currentUser->notify_lostfound ?? true)) {
        LostfoundItem::with('user')
            ->whereIn('status', ['lost', 'found'])
            ->latest('created_at')
            ->take(5)
            ->get()
            ->each(function ($item) use ($notifications) {
                $notifications->push([
                                    'id' => 'lostfound-' . $item->lostfound_id,
                                    'type' => 'Lost & Found',
                    'title' => $item->item_name,
                    'message' => ($item->status === 'lost' ? 'Laporan barang hilang' : 'Laporan barang ditemukan') . ($item->user ? ' dari ' . $item->user->name : ''),
                    'time' => optional($item->created_at)->diffForHumans(),
                    'url' => route('admin.lostfound'),
                    'icon' => 'package-search',
                    'created_at' => $item->created_at,
                ]);
            });
    }

    $notifications = $notifications
        ->sortByDesc(fn ($notification) => optional($notification['created_at'])->timestamp ?? 0)
        ->take(10)
        ->values();
    $notificationCount = $notifications->count();
        $notificationIds = $notifications->pluck('id')->values();
        $notificationStorageKey = 'admin_notifications_read_' . ($currentUser?->user_id ?? 'guest');
@endphp

<header class="topbar" style="overflow: visible !important;">
    <div class="page-title">
        @yield('page_title', 'Dashboard')
    </div>

    <div class="topbar-right" style="display: flex !important; align-items: center !important; gap: 20px !important;">
        <div class="notification-wrapper"
                    x-data="{
                        open: false,
                        notificationIds: @js($notificationIds),
                        readIds: [],
                        storageKey: @js($notificationStorageKey),
                        init() {
                            try {
                                this.readIds = JSON.parse(localStorage.getItem(this.storageKey) || '[]');
                            } catch (e) {
                                this.readIds = [];
                            }
                        },
                        get unreadCount() {
                            return this.notificationIds.filter((id) => !this.readIds.includes(id)).length;
                        },
                        markAllRead() {
                            this.readIds = Array.from(new Set([...this.readIds, ...this.notificationIds]));
                            localStorage.setItem(this.storageKey, JSON.stringify(this.readIds));
                        },
                        toggle() {
                            this.open = !this.open;
                            if (this.open) this.markAllRead();
                        }
                    }"
                    @click.away="open = false" style="position: relative !important; display: flex !important; align-items: center !important;">
            <button type="button" class="icon-btn" @click="toggle()" aria-label="Buka notifikasi" style="position: relative !important;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                @if ($notificationCount > 0)
                                    <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" class="badge" style="display: flex !important; align-items: center !important; justify-content: center !important; min-width: 18px !important; height: 18px !important; padding: 0 5px !important; border-radius: 999px !important; background: #EF4444 !important; color: #FFFFFF !important; font-size: 0.65rem !important; font-weight: 700 !important; position: absolute !important; top: -6px !important; right: -6px !important; border: 2px solid var(--bg-white) !important;"></span>
                                @endif
            </button>

            <div x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                style="display: none; position: absolute !important; top: calc(100% + 15px) !important; right: 0 !important; width: min(360px, calc(100vw - 32px)) !important; max-height: 460px !important; overflow: hidden !important; background: var(--bg-white) !important; border: 1px solid var(--border-color) !important; border-radius: 16px !important; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.12) !important; z-index: 120 !important;">
                <div style="display: flex !important; align-items: center !important; justify-content: space-between !important; padding: 16px !important; border-bottom: 1px solid var(--border-color) !important;">
                    <div>
                        <strong style="display: block !important; color: var(--text-main) !important; font-size: 0.95rem !important;">Notifikasi</strong>
                        <span style="display: block !important; color: var(--text-muted) !important; font-size: 0.75rem !important; margin-top: 2px !important;">
                                                    <span x-text="unreadCount"></span> belum dibaca dari {{ $notificationCount }} notifikasi
                                                </span>
                    </div>
                    @if ($notificationCount > 0)
                                            <span x-show="unreadCount > 0" x-text="unreadCount" style="display: inline-block !important; background: rgba(239, 68, 68, 0.12) !important; color: #EF4444 !important; padding: 4px 8px !important; border-radius: 999px !important; font-size: 0.75rem !important; font-weight: 700 !important;"></span>
                                        @endif
                </div>

                <div style="max-height: 360px !important; overflow-y: auto !important; padding: 8px !important;">
                    @forelse ($notifications as $notification)
                        <a href="{{ $notification['url'] }}" style="display: flex !important; gap: 12px !important; padding: 12px !important; border-radius: 12px !important; text-decoration: none !important; color: var(--text-main) !important; transition: background-color 0.2s !important;" @click="open = false" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                            <span style="width: 38px !important; height: 38px !important; border-radius: 12px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; background: rgba(59, 130, 246, 0.10) !important; color: #3B82F6 !important;">
                                <i data-lucide="{{ $notification['icon'] }}" style="width: 18px !important; height: 18px !important;"></i>
                            </span>
                            <span style="display: block !important; min-width: 0 !important; flex: 1 !important;">
                                <span style="display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 8px !important; margin-bottom: 2px !important;">
                                    <strong style="font-size: 0.85rem !important; color: var(--text-main) !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important;">{{ $notification['title'] }}</strong>
                                    <small style="font-size: 0.7rem !important; color: var(--text-muted) !important; white-space: nowrap !important;">{{ $notification['time'] }}</small>
                                </span>
                                <span style="display: block !important; color: var(--text-muted) !important; font-size: 0.78rem !important; line-height: 1.35 !important;">{{ Str::limit($notification['message'], 90) }}</span>
                                <span style="display: inline-block !important; margin-top: 6px !important; font-size: 0.68rem !important; font-weight: 700 !important; color: #3B82F6 !important; text-transform: uppercase !important; letter-spacing: 0.04em !important;">{{ $notification['type'] }}</span>
                            </span>
                        </a>
                    @empty
                        <div style="padding: 28px 16px !important; text-align: center !important;">
                            <div style="width: 48px !important; height: 48px !important; margin: 0 auto 12px !important; border-radius: 999px !important; background: var(--bg-main) !important; color: var(--text-muted) !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                                <i data-lucide="bell-off" style="width: 22px !important; height: 22px !important;"></i>
                            </div>
                            <strong style="display: block !important; color: var(--text-main) !important; font-size: 0.9rem !important; margin-bottom: 4px !important;">Tidak ada notifikasi</strong>
                            <span style="display: block !important; color: var(--text-muted) !important; font-size: 0.78rem !important;">Semua aktivitas terbaru sudah tertangani.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <button class="icon-btn" @click="$store.darkMode.toggle()">
            <svg x-show="!$store.darkMode.on" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            <svg x-show="$store.darkMode.on" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M22 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
        </button>

        <div class="profile-wrapper" x-data="{ open: false }" @click.away="open = false" style="position: relative !important; display: flex !important; align-items: center !important; padding-left: 20px !important; border-left: 1px solid var(--border-color) !important; height: 40px !important;">
            <div class="profile-trigger" @click="open = !open" style="display: flex !important; align-items: center !important; gap: 12px !important; cursor: pointer !important; white-space: nowrap !important;">
                <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=3B82F6&color=fff' }}" alt="{{ Auth::user()->name }}" class="profile-avatar" style="width: 40px !important; height: 40px !important; border-radius: 50% !important; object-fit: cover !important; flex-shrink: 0 !important;">
                <div class="profile-info" style="display: flex !important; flex-direction: column !important; justify-content: center !important; line-height: 1.2 !important;">
                    <span class="profile-name" style="font-size: 0.875rem !important; font-weight: 600 !important; color: var(--text-main) !important;">{{ Auth::user()->name }}</span>
                    <span class="profile-role" style="font-size: 0.75rem !important; color: var(--text-muted) !important;">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down" :class="{ 'rotate-180': open }" style="transition: transform 0.2s !important; flex-shrink: 0 !important; color: var(--text-muted) !important;"><path d="m6 9 6 6 6-6"/></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user" style="color: var(--primary) !important;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Profile Settings</span>
                </a>

                <div class="dropdown-divider" style="height: 1px !important; background: var(--border-color) !important; margin: 8px 0 !important;"></div>

                <button type="button" @click="showLogoutModal = true" class="dropdown-item logout-item" style="width: 100% !important; border: none !important; background: none !important; display: flex !important; align-items: center !important; gap: 10px !important; padding: 10px 12px !important; font-size: 0.875rem !important; color: var(--danger) !important; border-radius: 8px !important; cursor: pointer !important;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
</header>
