@extends('layouts.admin')

@section('page_title', 'Settings')

@push('styles')
    @vite(['resources/css/settings.css'])
@endpush

@section('content')
<div class="settings-container" x-data="{ activeTab: 'profile' }">
    <div class="settings-grid">
        <!-- Left Column: Profile Card -->
        <div class="settings-sidebar">
            <div class="profile-card">
                <div class="avatar-edit-wrapper">
                    <img src="{{ $user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=3B82F6&color=fff' }}" 
                         alt="Avatar" class="profile-avatar-large">
                    <button class="camera-btn">
                        <i data-lucide="camera"></i>
                    </button>
                </div>
                <h3 class="profile-name-large">{{ $user->name }}</h3>
                <p class="profile-role-text">{{ ucfirst($user->role) }}</p>

                <nav class="settings-menu">
                    <a href="#" @click.prevent="activeTab = 'profile'" :class="{ 'active': activeTab === 'profile' }" class="menu-item">
                        <i data-lucide="user"></i>
                        <span>Edit Profil</span>
                    </a>
                    <a href="#" @click.prevent="activeTab = 'notifications'" :class="{ 'active': activeTab === 'notifications' }" class="menu-item">
                        <i data-lucide="bell"></i>
                        <span>Pengaturan Notifikasi</span>
                    </a>
                    <a href="#" @click.prevent="activeTab = 'security'" :class="{ 'active': activeTab === 'security' }" class="menu-item">
                        <i data-lucide="shield"></i>
                        <span>Keamanan Akun</span>
                    </a>
                    <a href="#" @click.prevent="activeTab = 'help'" :class="{ 'active': activeTab === 'help' }" class="menu-item">
                        <i data-lucide="help-circle"></i>
                        <span>Pusat Bantuan</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Right Column: Tab Content -->
        <div class="settings-content">
            <!-- Edit Profile Tab -->
            <div x-show="activeTab === 'profile'" class="form-card">
                <div class="card-header-group">
                    <h2 class="form-title">Edit Profil</h2>
                    <p class="form-subtitle">Ubah dan sesuaikan data akun Anda</p>
                </div>

                <form method="post" action="{{ route('profile.update') }}" class="settings-form">
                    @csrf
                    @method('patch')

                    <div class="form-group">
                        <label for="name">Username</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                               class="form-input" placeholder="Masukkan username Anda">
                        @error('name')
                            <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email SSO Anda</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                               class="form-input" placeholder="Masukkan email SSO Anda">
                        @error('email')
                            <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (session('status') === 'profile-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                           class="text-success" style="font-size: 0.875rem;">Perubahan berhasil disimpan.</p>
                    @endif

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <!-- Notifications Tab -->
            <div x-show="activeTab === 'notifications'" class="form-card" x-cloak>
                <div class="card-header-group">
                    <h2 class="form-title">Pengaturan Notifikasi</h2>
                    <p class="form-subtitle">Atur notifikasi Anda</p>
                </div>

                <div class="switch-group">
                    <div class="switch-item">
                        <div class="switch-label-group">
                            <span class="switch-title">Email Notifikasi</span>
                            <span class="switch-description">Izinkan Younifirst kirim notifikasi via email</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="switch-item">
                        <div class="switch-label-group">
                            <span class="switch-title">Notifikasi Event Baru</span>
                            <span class="switch-description">Notif saat ada event baru menunggu approval</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="switch-item">
                        <div class="switch-label-group">
                            <span class="switch-title">Notifikasi Team Baru</span>
                            <span class="switch-description">Notif saat ada tim baru menunggu approval</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="switch-item">
                        <div class="switch-label-group">
                            <span class="switch-title">Notifikasi Lost & Found</span>
                            <span class="switch-description">Notif saat ada postingan baru</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Keamanan Akun Tab -->
            <div x-show="activeTab === 'security'" class="form-card" x-cloak>
                <div class="card-header-group">
                    <h2 class="form-title">Keamanan Akun</h2>
                    <p class="form-subtitle">Ubah dan atur kata sandi akun Anda</p>
                </div>

                <form method="post" action="{{ route('password.update') }}" class="settings-form">
                    @csrf
                    @method('put')

                    <div class="form-group" x-data="{ show: false }">
                        <label for="current_password">Kata Sandi Saat Ini</label>
                        <div style="position: relative;">
                            <input :type="show ? 'text' : 'password'" id="current_password" name="current_password" 
                                   class="form-input" placeholder="Masukkan kata sandi saat ini">
                            <button type="button" @click="show = !show" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                                <i x-show="!show" data-lucide="eye" style="width: 18px; height: 18px;"></i>
                                <i x-show="show" data-lucide="eye-off" style="width: 18px; height: 18px;" x-cloak></i>
                            </button>
                        </div>
                        @error('current_password', 'updatePassword')
                            <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <p style="font-size: 0.875rem; color: var(--text-main); margin: 8px 0; line-height: 1.5;">
                        Buat kata sandi baru. Kata sandi baru Anda harus berbeda dari kata sandi yang sebelumnya pernah digunakan.
                    </p>

                    <div class="form-group">
                        <label for="password">Kata Sandi Baru</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan kata sandi baru">
                        @error('password', 'updatePassword')
                            <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" 
                               class="form-input" placeholder="Ulangi kata sandi baru">
                        @error('password_confirmation', 'updatePassword')
                            <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (session('status') === 'password-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                           class="text-success" style="font-size: 0.875rem;">Kata sandi berhasil diperbarui.</p>
                    @endif

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" @click="activeTab = 'profile'">Batal</button>
                        <button type="submit" class="btn btn-primary">Ganti Kata Sandi</button>
                    </div>
                </form>
            </div>

            <!-- Pusat Bantuan Tab -->
            <div x-show="activeTab === 'help'" class="form-card" x-cloak>
                <div class="card-header-group">
                    <h2 class="form-title">Pusat Bantuan</h2>
                    <p class="form-subtitle">Temukan jawaban dan hubungi kami</p>
                </div>

                <div class="faq-section">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <i data-lucide="book-open" style="width: 20px; height: 20px; color: var(--text-main);"></i>
                        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main);">Pertanyaan Umum (FAQ)</h3>
                    </div>

                    <div class="faq-item">
                        <span class="faq-question">Bagaimana cara menambah event baru?</span>
                        <p class="faq-answer">Buka halaman Event Management, klik tombol 'Tambah Event', lalu isi form sesuai data yang dibutuhkan dan posting.</p>
                    </div>

                    <div class="faq-item">
                        <span class="faq-question">Bagaimana cara approve postingan event ataupun tim?</span>
                        <p class="faq-answer">Buka halaman baik Event Management dan Team Monitoring, klik "Lihat Semua" di Menunggu Persetujuan dan admin dapat melihat konten event ataupun tim di "Lihat Detail" sebelum menyetujui/approve postingan ke publik dengan menekan tombol "Setujui" atau tolak dengan menekan tombol "Tolak"</p>
                    </div>

                    <div class="faq-item">
                        <span class="faq-question">Bagaimana sistem Lost & Found bekerja?</span>
                        <p class="faq-answer">User/mahasiswa dapat memposting barang hilang atau ditemukan, dan admin dapat mengelola serta mengubah status item (Hilang, Ditemukan, Selesai). Postingan yang tidak sesuai dapat dihapus oleh admin.</p>
                    </div>

                    <div class="faq-item">
                        <span class="faq-question">Apa itu fitur Announcement di Younifirst?</span>
                        <p class="faq-answer">Fitur Announcement digunakan untuk mengumumkan informasi atau pengumuman penting bagi seluruh mahasiswa.</p>
                    </div>
                </div>
            </div>

            <!-- Hubungi Kami Card (only visible when in help tab) -->
            <div x-show="activeTab === 'help'" class="contact-card" x-cloak>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="message-circle" style="width: 20px; height: 20px; color: var(--text-main);"></i>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main);">Hubungi Kami</h3>
                </div>

                <div class="contact-grid">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i data-lucide="mail"></i>
                        </div>
                        <div class="contact-info">
                            <span class="contact-label">Email Support :</span>
                            <span class="contact-value">younifirstteamsupport@gmail.com</span>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-info">
                            <span class="contact-label">Social Media :</span>
                            <div class="social-links">
                                <a href="#" class="social-btn"><i data-lucide="instagram" style="width: 20px; height: 20px;"></i></a>
                                <a href="#" class="social-btn"><i data-lucide="message-square" style="width: 20px; height: 20px;"></i></a>
                                <a href="#" class="social-btn"><i data-lucide="youtube" style="width: 20px; height: 20px;"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
