@extends('layouts.admin')

@section('title', 'User Management')
@section('page_title', 'User Management')

@section('content')
    <div class="user-management" x-data="userManagement({{ $users->toJson() }})">
        <!-- Summary Cards -->
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card hover-scale">
                <div class="card-content">
                    <span class="card-label">Total Users</span>
                    <span class="card-value text-primary" x-text="totalUsers"></span>
                </div>
            </div>
            <div class="card hover-scale">
                <div class="card-content">
                    <span class="card-label">Inactive Users</span>
                    <span class="card-value" style="color: #F59E0B;" x-text="inactiveUsers"></span>
                    <span class="card-subtitle" style="color: #FBBF24;">Users with no activity > 3 days <i data-lucide="trending-up" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i></span>
                </div>
            </div>
            <div class="card hover-scale">
                <div class="card-content">
                    <span class="card-label">Suspended Users</span>
                    <span class="card-value" style="color: #F97316;" x-text="suspendedUsers"></span>
                </div>
            </div>
            <div class="card hover-scale">
                <div class="card-content">
                    <span class="card-label">Blocked Users</span>
                    <span class="card-value text-danger" x-text="blockedUsers"></span>
                </div>
            </div>
        </div>

            <div class="filter-bar">
                <!-- Left Side: Search -->
                <div class="search-wrapper">
                    <i data-lucide="search"></i>
                    <input type="text" x-model="search" placeholder="Cari nama, email, atau NIM..." id="searchInput">
                </div>

                <!-- Right Side: Actions Grouped -->
                <div class="filter-actions-group">
                    <div class="dropdown-wrapper" x-data="{ open: false }">
                        <button type="button" class="dropdown-btn" @click="open = !open">
                            <span x-text="status === 'Semua Status' ? 'Semua Status' : (status.charAt(0).toUpperCase() + status.slice(1))">Semua Status</span>
                            <i data-lucide="chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" x-show="open" @click.outside="open = false" x-cloak>
                            <div class="dropdown-item" @click="status = 'Semua Status'; open = false">
                                Semua Status
                                <i data-lucide="check" x-show="status === 'Semua Status'"></i>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-item" @click="status = 'active'; open = false">
                                Active
                                <i data-lucide="check" x-show="status === 'active'"></i>
                            </div>
                            <div class="dropdown-item" @click="status = 'suspended'; open = false">
                                Suspended
                                <i data-lucide="check" x-show="status === 'suspended'"></i>
                            </div>
                            <div class="dropdown-item" @click="status = 'blocked'; open = false">
                                Blocked
                                <i data-lucide="check" x-show="status === 'blocked'"></i>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-primary" @click="showAddModal = true">
                        <i data-lucide="plus-circle"></i>
                        <span>Tambah User</span>
                    </button>

                    <button type="button" class="btn-primary" @click="exportToCSV()">
                        <i data-lucide="download"></i>
                        <span>Export</span>
                    </button>
                </div>
            </div>

        <div class="table-info" x-text="`${totalUsers} dari ${allUsers.length} mahasiswa`">
        </div>

        <!-- Users Table -->
        <div class="table-card glass-panel">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>NIM</th>
                        <th>Program Studi</th>
                        <th>Bergabung</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="user in paginatedUsers" :key="user.user_id">
                    <tr class="table-row-hover" x-init="$nextTick(() => lucide.createIcons())">
                        <td>
                            <div class="user-info">
                                <div class="avatar-wrapper">
                                    <img :src="`https://ui-avatars.com/api/?name=${user.encoded_name}&background=E2E8F0&color=475569&bold=true`" alt="Avatar" class="user-avatar">
                                    <div class="status-indicator" :class="`bg-${user.status}`"></div>
                                </div>
                                <div class="user-details">
                                    <span class="user-name" x-text="user.name"></span>
                                    <span class="user-email text-xs" x-text="user.email"></span>
                                </div>
                            </div>
                        </td>
                        <td class="font-mono text-xs" x-text="user.nim"></td>
                        <td class="text-sm" x-text="user.prodi"></td>
                        <td class="text-sm" x-text="user.joined"></td>
                        <td>
                            <span :class="`status-badge status-${user.status}`">
                                <span class="status-dot"></span>
                                <span x-text="user.status"></span>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <!-- View Detail -->
                                <button class="action-btn" @click="viewUser(user)" title="Detail Pengguna">
                                    <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
                                </button>

                                <!-- Edit User -->
                                <button class="action-btn text-primary" @click="openEditModal(user)" title="Edit Pengguna">
                                    <i data-lucide="edit-3" style="width: 18px; height: 18px;"></i>
                                </button>

                                <!-- Suspend/Unsuspend User -->
                                <template x-if="user.status !== 'suspended'">
                                    <button class="action-btn text-warning" @click="openSuspendModal(user)" title="Suspend Pengguna">
                                        <i data-lucide="user-minus" style="width: 18px; height: 18px;"></i>
                                    </button>
                                </template>
                                <template x-if="user.status === 'suspended'">
                                    <button class="action-btn text-success" @click="unsuspendUser(user)" title="Buka Suspend">
                                        <i data-lucide="user-check" style="width: 18px; height: 18px;"></i>
                                    </button>
                                </template>

                                <!-- Block/Unblock User -->
                                <template x-if="user.status !== 'blocked'">
                                    <button class="action-btn text-danger" @click="openBlockModal(user)" title="Blokir Pengguna">
                                        <i data-lucide="ban" style="width: 18px; height: 18px;"></i>
                                    </button>
                                </template>
                                <template x-if="user.status === 'blocked'">
                                    <button class="action-btn text-success" @click="unblockUser(user)" title="Buka Blokir">
                                        <i data-lucide="unlock" style="width: 18px; height: 18px;"></i>
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                    </template>

                    <template x-if="filteredUsers.length === 0">
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-content">
                                        <i data-lucide="search-x" class="empty-icon"></i>
                                        <p>Tidak ada data ditemukan</p>
                                        <button @click="resetFilters()" class="btn-text">Reset Filter</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Toast Notifications -->
        <div class="toast-container" x-show="toast.show" x-cloak x-transition:enter="toast-enter" x-transition:leave="toast-leave">
            <div :class="`toast toast-${toast.type}`">
                <i :data-lucide="toast.icon"></i>
                <span x-text="toast.message"></span>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" x-show="totalPages > 1" x-cloak>
            <div class="pagination-info">
                Menampilkan <span class="font-bold" x-text="startIndex + 1"></span> - <span class="font-bold" x-text="Math.min(endIndex, totalUsers)"></span> dari <span class="font-bold" x-text="totalUsers"></span> data
            </div>
            <div class="pagination-buttons">
                <button class="pagination-btn" @click="prevPage()" :disabled="currentPage === 1">
                    <i data-lucide="chevron-left"></i> Prev
                </button>
                <div class="page-numbers">
                    <template x-for="page in totalPages" :key="page">
                        <button class="pagination-btn" :class="{ 'active': currentPage === page }" @click="goToPage(page)" x-text="page"></button>
                    </template>
                </div>
                <button class="pagination-btn" @click="nextPage()" :disabled="currentPage === totalPages">
                    Next <i data-lucide="chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Modals -->
        <!-- Add User Modal -->
        <div class="modal-overlay" x-show="showAddModal" x-cloak x-transition>
            <div class="modal-container glass-panel" @click.outside="showAddModal = false">
                <div class="modal-header">
                    <h3>Tambah User Baru</h3>
                    <button @click="showAddModal = false" class="close-btn"><i data-lucide="x"></i></button>
                </div>
                <form @submit.prevent="addUser()">
                    <div class="modal-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" x-model="newUser.name" required placeholder="Contoh: Rona Naa">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" x-model="newUser.email" required placeholder="email@student.polije.ac.id">
                            </div>
                            <div class="form-group">
                                <label>NIM</label>
                                <input type="text" x-model="newUser.nim" placeholder="Contoh: E41240238">
                            </div>
                            <div class="form-group">
                                <label>Program Studi</label>
                                <select x-model="newUser.prodi">
                                    <option value="">Pilih Program Studi</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Manajemen Bisnis">Manajemen Bisnis</option>
                                    <option value="Teknik Komputer">Teknik Komputer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" x-model="newUser.password" required placeholder="Minimal 8 karakter">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select x-model="newUser.role">
                                    <option value="user">Student</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showAddModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary" :disabled="loading">
                            <span x-show="!loading">Simpan User</span>
                            <span x-show="loading">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal-overlay" x-show="showEditModal" x-cloak x-transition>
            <div class="modal-container glass-panel" @click.outside="showEditModal = false">
                <div class="modal-header">
                    <h3>Edit Pengguna</h3>
                    <button @click="showEditModal = false" class="close-btn"><i data-lucide="x"></i></button>
                </div>
                <form @submit.prevent="updateUser()">
                    <template x-if="editingUser">
                        <div class="modal-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" x-model="editingUser.name" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" x-model="editingUser.email" required>
                            </div>
                            <div class="form-group">
                                <label>NIM</label>
                                <input type="text" x-model="editingUser.nim">
                            </div>
                            <div class="form-group">
                                <label>Program Studi</label>
                                <select x-model="editingUser.prodi">
                                    <option value="">Pilih Program Studi</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Manajemen Bisnis">Manajemen Bisnis</option>
                                    <option value="Teknik Komputer">Teknik Komputer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select x-model="editingUser.role">
                                    <option value="user">Student</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="modal-footer">
                        <button type="button" @click="showEditModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary" :disabled="loading">
                            <span x-show="!loading">Simpan Perubahan</span>
                            <span x-show="loading">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Suspend Confirmation Modal -->
        <div class="modal-overlay" x-show="showSuspendModal" x-cloak x-transition>
            <div class="modal-container glass-panel" style="max-width: 450px; padding: 0; overflow: hidden;" @click.outside="showSuspendModal = false">
                <div class="suspend-header" style="padding: 16px 24px;">
                    <div class="suspend-title-group">
                        <div class="suspend-icon-bg" style="width: 32px; height: 32px; border-radius: 8px;">
                            <i data-lucide="user-x" style="color: #F59E0B; width: 18px; height: 18px;"></i>
                        </div>
                        <h3 style="font-size: 15px;">Suspend Akun Pengguna</h3>
                    </div>
                    <button @click="showSuspendModal = false" class="close-btn-minimal">
                        <i data-lucide="x"></i>
                    </button>
                </div>

                <template x-if="selectedUser">
                    <div class="modal-body" style="padding: 12px 24px;">
                    <div class="suspend-intro" style="margin-bottom: 8px;">
                        <p style="font-size: 12px; margin-bottom: 0;">Tetapkan durasi dan alasan suspend. Informasi akan dikirim ke email pengguna.</p>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-size: 12px; margin-bottom: 4px; display: block;">Durasi Suspend <span style="color: #EF4444;">*</span></label>
                        <div class="duration-tabs" style="gap: 6px;">
                            <button type="button" style="padding: 8px 4px; font-size: 12px;" :class="suspendDuration === '1' ? 'active' : ''" @click="suspendDuration = '1'">1 Hari</button>
                            <button type="button" style="padding: 8px 4px; font-size: 12px;" :class="suspendDuration === '7' ? 'active' : ''" @click="suspendDuration = '7'">7 Hari</button>
                            <button type="button" style="padding: 8px 4px; font-size: 12px;" :class="suspendDuration === '30' ? 'active' : ''" @click="suspendDuration = '30'">30 Hari</button>
                            <button type="button" style="padding: 8px 4px; font-size: 12px;" :class="suspendDuration === 'custom' ? 'active' : ''" @click="suspendDuration = 'custom'">Custom</button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 10px;">
                        <textarea x-model="suspendReason" placeholder="Ketik alasan.." style="min-height: 60px; font-size: 13px; padding: 10px;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 10px;">
                        <label style="font-size: 12px; margin-bottom: 4px; display: block;">Catatan Internal <span style="color: #94A3B8;">(Opsional)</span></label>
                        <textarea x-model="internalNotes" placeholder="Catatan internal admin..." style="min-height: 50px; font-size: 13px; padding: 10px;"></textarea>
                    </div>

                    <div class="info-alert" style="padding: 8px 12px; margin-top: 4px; gap: 8px;">
                        <div class="info-icon" style="width: 24px; height: 24px;">
                            <i data-lucide="clock" style="width: 14px;"></i>
                        </div>
                        <p style="font-size: 10px; line-height: 1.3;">Akun akan otomatis aktif kembali setelah durasi berakhir. Pengguna akan menerima email.</p>
                    </div>
                </div>
                </template>
                <div class="modal-footer" style="padding: 8px 24px 20px; border-top: none; display: flex; gap: 12px;">
                    <button @click="showSuspendModal = false" class="btn-cancel-suspend" style="flex: 1; padding: 10px; font-size: 14px;">Batal</button>
                    <button @click="confirmSuspend()" class="btn-confirm-suspend" style="flex: 1; padding: 10px; font-size: 14px;" :disabled="loading">
                        <span x-show="!loading">Suspend Akun</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Block Confirmation Modal -->
        <div class="modal-overlay" x-show="showBlockModal" x-cloak x-transition>
            <div class="modal-container glass-panel" style="max-width: 400px; text-align: center;">
                <div class="modal-body" style="padding: 40px 30px;">
                    <div style="width: 64px; height: 64px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i data-lucide="ban" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="font-size: 18px; margin-bottom: 12px;">Blokir Pengguna?</h3>
                    <p style="color: #64748B; font-size: 14px; margin-bottom: 30px;">Pengguna tidak akan bisa masuk ke aplikasi. Tindakan ini dapat dibatalkan melalui buka blokir.</p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <button @click="showBlockModal = false" class="btn-secondary" style="justify-content: center;">Batal</button>
                        <button @click="confirmBlock()" class="btn-primary" style="background: #EF4444; justify-content: center;" :disabled="loading">
                            <span x-show="!loading">Ya, Blokir</span>
                            <span x-show="loading">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unsuspend Confirmation Modal -->
        <div class="modal-overlay" x-show="showUnsuspendModal" x-cloak x-transition>
            <div class="modal-container glass-panel" style="max-width: 400px; text-align: center;">
                <div class="modal-body" style="padding: 40px 30px;">
                    <div style="width: 64px; height: 64px; background: #ECFDF5; color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i data-lucide="user-check" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="font-size: 18px; margin-bottom: 12px;">Buka Suspend?</h3>
                    <p style="color: #64748B; font-size: 14px; margin-bottom: 30px;" x-text="`Apakah Anda yakin ingin membuka suspend untuk ${selectedUser?.name}?`"></p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <button @click="showUnsuspendModal = false" class="btn-secondary" style="justify-content: center;">Batal</button>
                        <button @click="confirmUnsuspend()" class="btn-primary" style="background: #10B981; justify-content: center;" :disabled="loading">
                            <span x-show="!loading">Ya, Buka</span>
                            <span x-show="loading">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unblock Confirmation Modal -->
        <div class="modal-overlay" x-show="showUnblockModal" x-cloak x-transition>
            <div class="modal-container glass-panel" style="max-width: 400px; text-align: center;">
                <div class="modal-body" style="padding: 40px 30px;">
                    <div style="width: 64px; height: 64px; background: #ECFDF5; color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i data-lucide="unlock" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="font-size: 18px; margin-bottom: 12px;">Buka Blokir?</h3>
                    <p style="color: #64748B; font-size: 14px; margin-bottom: 30px;" x-text="`Apakah Anda yakin ingin membuka blokir untuk ${selectedUser?.name}?`"></p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <button @click="showUnblockModal = false" class="btn-secondary" style="justify-content: center;">Batal</button>
                        <button @click="confirmUnblock()" class="btn-primary" style="background: #10B981; justify-content: center;" :disabled="loading">
                            <span x-show="!loading">Ya, Buka</span>
                            <span x-show="loading">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details Modal -->
        <div class="modal-overlay" x-show="showDetailModal" x-cloak x-transition>
            <div class="modal-container glass-panel" @click.outside="showDetailModal = false">
                <div class="modal-header">
                    <h3>Detail Pengguna</h3>
                    <button @click="showDetailModal = false" class="close-btn"><i data-lucide="x"></i></button>
                </div>
                <template x-if="selectedUser">
                    <div class="modal-body">
                    <div class="user-profile-header">
                        <img :src="`https://ui-avatars.com/api/?name=${selectedUser.encoded_name}&background=E2E8F0&color=475569&size=100&bold=true`" alt="Avatar" class="user-avatar" style="width: 100px; height: 100px;">
                        <div class="profile-info">
                            <h2 x-text="selectedUser.name"></h2>
                            <p x-text="selectedUser.email"></p>
                            <span :class="`status-badge status-${selectedUser.status}`" x-text="selectedUser.status"></span>
                        </div>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>NIM</label>
                            <p x-text="selectedUser.nim || '-'"></p>
                        </div>
                        <div class="detail-item">
                            <label>Program Studi</label>
                            <p x-text="selectedUser.prodi || '-'"></p>
                        </div>
                        <div class="detail-item">
                            <label>Bergabung Pada</label>
                            <p x-text="selectedUser.joined"></p>
                        </div>
                        <div class="detail-item">
                            <label>User ID</label>
                            <p class="font-mono text-xs" x-text="selectedUser.id"></p>
                        </div>
                    </div>
                </div>
                </template>
                <div class="modal-footer">
                    <button @click="showDetailModal = false" class="btn-secondary">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        [x-cloak] { display: none !important; }

        :root {
            --primary: #3B82F6;
            --secondary: #64748B;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --orange: #F97316;
            --bg-glass: rgba(255, 255, 255, 0.8);
        }

        .glass-panel {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }

        .hover-scale { transition: transform 0.2s; }
        .hover-scale:hover { transform: translateY(-4px); }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .summary-cards { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .summary-cards { grid-template-columns: 1fr; }
        }

        .card {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            border: 1px solid rgba(226, 232, 240, 0.8);
            min-height: 120px;
        }

        .card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); }

        .card-content { display: flex; flex-direction: column; text-align: left; width: 100%; }
        .card-label { font-size: 15px; color: #475569; font-weight: 600; margin-bottom: 12px; }
        .card-value { font-size: 32px; font-weight: 800; line-height: 1; }
        .card-subtitle { font-size: 12px; margin-top: 8px; font-weight: 500; display: flex; align-items: center; gap: 4px; }

        .btn-primary { 
            background: #2563EB; color: #fff; padding: 0 24px; border-radius: 99px; 
            font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; 
            display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;
            font-size: 14px; height: 46px;
        }
        .btn-primary:hover { background: #1D4ED8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }

        .btn-secondary { 
            background: #fff; color: #475569; border: 1.5px solid #E2E8F0; padding: 12px 24px; 
            border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; 
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
        }
        .btn-secondary:hover { background: #F8FAFC; border-color: #CBD5E1; }

        .btn-outline { 
            border: 1.5px solid #E2E8F0; background: transparent; color: #475569; padding: 10px 20px; 
            border-radius: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s;
        }
        .btn-outline:hover { background: #F8FAFC; border-color: #CBD5E1; }

        .avatar-wrapper { position: relative; width: 44px; height: 44px; flex-shrink: 0; }
        .user-avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: block; aspect-ratio: 1/1; }

        .status-indicator { position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; }
        .bg-active { background: var(--success); }
        .bg-suspended { background: var(--orange); }
        .bg-blocked { background: var(--danger); }
        .bg-inactive { background: var(--secondary); }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-dot { width: 6px; height: 6px; border-radius: 50%; }
        .status-active { background: #ECFDF5; color: #065F46; }
        .status-active .status-dot { background: #10B981; }
        .status-suspended { background: #FFF7ED; color: #9A3412; }
        .status-suspended .status-dot { background: #F97316; }
        .status-blocked { background: #FEF2F2; color: #991B1B; }
        .status-blocked .status-dot { background: #EF4444; }

        .action-buttons { display: flex; gap: 6px; }
        .action-btn {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 8px;
            border-radius: 8px;
            color: #64748B;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .action-btn:hover { background: #F1F5F9; color: var(--text-main); border-color: #CBD5E1; transform: translateY(-1px); }
        .action-btn i { width: 18px; height: 18px; }

        /* Modals Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(8px);
            padding: 20px;
        }

        .modal-container {
            width: 100%;
            max-width: 650px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalSlideUp 0.3s ease-out;
        }

        @keyframes modalSlideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            padding: 24px 30px;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .modal-header h3 { font-size: 18px; font-weight: 700; color: #1E293B; }

        .modal-body { padding: 30px; max-height: 75vh; overflow-y: auto; }

        .modal-footer {
            padding: 24px 30px;
            background: #F8FAFC;
            border-top: 1px solid #F1F5F9;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            color: #94A3B8;
        }
        .empty-state i { width: 48px; height: 48px; color: #CBD5E1; }
        .empty-state p { font-size: 16px; font-weight: 500; }

        .close-btn { 
            width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            border: none; background: #F8FAFC; color: #64748B; cursor: pointer; transition: all 0.2s;
        }
        .close-btn:hover { background: #FEF2F2; color: #EF4444; }

        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 600; color: #475569; }
        .form-group input, .form-group select { 
            padding: 12px 16px; border: 1.5px solid #E2E8F0; border-radius: 12px; font-size: 14px; 
            transition: all 0.2s; background: #fff;
        }
        .form-group input:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); outline: none; }

        .detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .detail-item { background: #F8FAFC; padding: 16px; border-radius: 16px; border: 1px solid #F1F5F9; }
        .detail-item label { font-size: 12px; color: #94A3B8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 4px; display: block; }
        .detail-item p { font-size: 15px; color: #1E293B; font-weight: 600; }

        .user-profile-header { display: flex; align-items: center; gap: 24px; margin-bottom: 30px; background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%); padding: 24px; border-radius: 20px; }
        .profile-info h4, .profile-info h2 { font-size: 22px; font-weight: 800; color: #1E293B; margin-bottom: 4px; }
        .profile-info p { color: #64748B; font-size: 14px; margin-bottom: 8px; }

        .btn-primary { background: #2563EB; color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; white-space: nowrap; display: flex; align-items: center; gap: 8px; }
        .btn-primary:hover { background: #1D4ED8; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }

        .btn-secondary { background: #fff; color: #475569; border: 1.5px solid #E2E8F0; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
        .btn-secondary:hover { background: #F8FAFC; border-color: #CBD5E1; }

        .user-row { transition: background-color 0.2s; border-bottom: 1px solid #F1F5F9; }
        .user-row:hover { background-color: #F8FAFC; }
        .user-name { font-weight: 700; color: #1E293B; display: block; font-size: 14px; }
        .user-email { font-size: 13px; color: #64748B; }

        .filter-bar { 
            display: flex; 
            justify-content: space-between;
            align-items: center; 
            margin-bottom: 32px;
            gap: 32px;
            width: 100%;
        }

        .filter-actions-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-wrapper {
            position: relative;
            flex: 1;
            max-width: 600px;
        }
        .search-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #94A3B8;
        }
        .search-wrapper input {
            width: 100%;
            padding: 0 16px 0 42px;
            border: 1px solid #E2E8F0;
            border-radius: 99px;
            font-size: 14px;
            background: #fff;
            transition: all 0.2s;
            height: 46px;
        }
        .search-wrapper input:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Suspend Modal Specific Styles */
        .suspend-header {
            background: #FFFBEB;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .suspend-title-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .suspend-title-group h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1E293B;
        }
        .suspend-icon-bg {
            width: 40px;
            height: 40px;
            background: #FEF3C7;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .close-btn-minimal {
            background: none;
            border: none;
            color: #64748B;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .close-btn-minimal:hover { background: #FEF3C7; color: #1E293B; }

        .suspend-intro p {
            font-size: 13px;
            line-height: 1.5;
            color: #1E293B;
            margin-bottom: 4px;
        }
        .suspend-intro .secondary-info {
            color: #64748B;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .duration-tabs {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .duration-tabs button {
            flex: 1;
            padding: 10px;
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #1E293B;
            cursor: pointer;
            transition: all 0.2s;
        }
        .duration-tabs button.active {
            background: #F59E0B;
            border-color: #F59E0B;
            color: #fff;
        }

        .info-alert {
            background: #F0F4FF;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        .info-icon {
            width: 32px;
            height: 32px;
            background: #E0E7FF;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .info-icon i { color: #3B82F6; width: 16px; }
        .info-alert p {
            font-size: 11px;
            line-height: 1.4;
            color: #475569;
            margin: 0;
        }

        .btn-cancel-suspend {
            background: #fff;
            border: 1px solid #E2E8F0;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            color: #1E293B;
            cursor: pointer;
        }
        .btn-confirm-suspend {
            background: #F59E0B;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-confirm-suspend:hover { background: #D97706; }
        .btn-confirm-suspend:disabled { opacity: 0.7; cursor: not-allowed; }

        .filter-actions { display: flex; align-items: center; gap: 10px; }

        /* Custom Dropdown */
        .dropdown-wrapper { position: relative; }
        .dropdown-btn {
            background: #fff; border: 1px solid #E2E8F0; padding: 0 20px; 
            border-radius: 99px; font-size: 14px; font-weight: 500; color: #1E293B;
            display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all 0.2s;
            min-width: 160px; height: 46px;
        }
        .dropdown-btn:hover { background: #F8FAFC; border-color: #CBD5E1; }
        .dropdown-menu {
            position: absolute; top: calc(100% + 8px); left: 0; min-width: 200px;
            background: #fff; border-radius: 16px; border: 1px solid #E2E8F0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 50; padding: 6px;
            animation: dropdownIn 0.2s ease-out;
        }
        @keyframes dropdownIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-item {
            padding: 10px 12px; border-radius: 10px; font-size: 14px; font-weight: 500; color: #475569;
            cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;
        }
        .dropdown-item:hover { background: #F1F5F9; color: var(--primary); }
        .dropdown-item i { width: 16px; height: 16px; }
        .dropdown-divider { height: 1px; background: #F1F5F9; margin: 4px 6px; }

        .admin-table thead th {
            padding: 16px 20px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748B;
            border-bottom: 2px solid #F1F5F9;
            background: #FDFDFD;
        }
        .admin-table tbody td { padding: 16px 20px; vertical-align: middle; }

        .table-row-hover { 
            transition: all 0.2s ease;
        }
        .table-row-hover:hover { 
            background-color: #F8FAFC; 
        }

        /* Toast styles */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 2000;
        }
        .toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            color: #1E293B;
            font-weight: 600;
            min-width: 300px;
            border-left: 4px solid #E2E8F0;
        }
        .toast-success { border-left-color: var(--success); }
        .toast-error { border-left-color: var(--danger); }
        .toast-warning { border-left-color: var(--warning); }

        .toast-enter { transform: translateY(-20px); opacity: 0; }
        .toast-leave { transform: translateX(20px); opacity: 0; }

        .empty-state {
            padding: 80px 0;
            text-align: center;
            width: 100%;
        }
        .empty-icon { width: 64px; height: 64px; color: #CBD5E1; margin: 0 auto 16px; }
        .btn-text { background: none; border: none; color: var(--primary); font-weight: 600; cursor: pointer; margin-top: 8px; text-decoration: underline; }

        .hidden { display: none; }
        @media (max-width: 640px) { 
            .form-grid, .detail-grid { grid-template-columns: 1fr; } 
            .user-profile-header { flex-direction: column; text-align: center; }
            .filter-bar { flex-direction: column; gap: 16px; align-items: stretch; }
        }

    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userManagement', (initialUsers) => ({
                allUsers: initialUsers,
                search: '',
                status: 'Semua Status',
                currentPage: 1,
                perPage: 8,
                loading: false,
                showAddModal: false,
                showEditModal: false,
                showSuspendModal: false,
                showBlockModal: false,
                showUnsuspendModal: false,
                showUnblockModal: false,
                showDetailModal: false,

                toast: { show: false, message: '', type: 'success', icon: 'check-circle' },

                selectedUser: null,
                editingUser: null,

                newUser: { name: '', email: '', nim: '', prodi: '', password: '', role: 'user' },

                // Suspend Modal State
                suspendDuration: '7',
                suspendReason: '',
                internalNotes: '',

                init() {
                    this.$watch('search', () => this.currentPage = 1);
                    this.$watch('status', () => this.currentPage = 1);
                    this.$watch('currentPage', () => {
                        this.$nextTick(() => lucide.createIcons());
                    });
                    lucide.createIcons();
                },

                get filteredUsers() {
                    let q = this.search.toLowerCase();
                    let s = this.status.toLowerCase();
                    return this.allUsers.filter(u => {
                        let matchesSearch = q === '' || (u.name && u.name.toLowerCase().includes(q)) || (u.email && u.email.toLowerCase().includes(q)) || (u.nim && u.nim.toLowerCase().includes(q));
                        let matchesStatus = s === 'semua status' || u.status === s;
                        return matchesSearch && matchesStatus;
                    });
                },

                get totalUsers() { return this.filteredUsers.length; },
                get inactiveUsers() { return this.allUsers.filter(u => u.status === 'inactive').length; },
                get suspendedUsers() { return this.allUsers.filter(u => u.status === 'suspended').length; },
                get blockedUsers() { return this.allUsers.filter(u => u.status === 'blocked').length; },
                get totalPages() { return Math.ceil(this.totalUsers / this.perPage) || 1; },
                get startIndex() { return (this.currentPage - 1) * this.perPage; },
                get endIndex() { return this.startIndex + this.perPage; },
                get paginatedUsers() { return this.filteredUsers.slice(this.startIndex, this.endIndex); },
                prevPage() { if (this.currentPage > 1) this.currentPage--; },
                nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
                goToPage(page) { this.currentPage = page; },
                resetFilters() { this.search = ''; this.status = 'Semua Status'; this.currentPage = 1; },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'alert-triangle');
                    this.toast.show = true;
                    this.$nextTick(() => lucide.createIcons());
                    setTimeout(() => { this.toast.show = false; }, 3000);
                },

                viewUser(user) {
                    this.selectedUser = user;
                    this.showDetailModal = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                openEditModal(user) {
                    this.editingUser = { ...user };
                    this.showEditModal = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                openSuspendModal(user) {
                    this.selectedUser = user;
                    this.showSuspendModal = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                openBlockModal(user) {
                    this.selectedUser = user;
                    this.showBlockModal = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async updateUser() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/users/${this.editingUser.user_id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                            body: JSON.stringify(this.editingUser)
                        });
                        const result = await response.json();
                        if (response.ok) {
                            const idx = this.allUsers.findIndex(u => (u.user_id || u.id) === this.editingUser.user_id);
                            if (idx !== -1) {
                                this.allUsers[idx] = { ...this.editingUser, ...result.data };
                                this.allUsers[idx].encoded_name = encodeURIComponent(this.editingUser.name);
                            }
                            this.showEditModal = false;
                            this.showToast('Data pengguna berhasil diperbarui', 'success');
                        } else { 
                            this.showToast(result.message || 'Gagal memperbarui data', 'error'); 
                        }
                    } catch (error) { console.error(error); this.showToast('Terjadi kesalahan sistem', 'error'); } 
                    finally { this.loading = false; }
                },

                async confirmSuspend() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/users/${this.selectedUser.user_id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                            body: JSON.stringify({ 
                                status: 'suspended',
                                duration: this.suspendDuration,
                                reason: this.suspendReason,
                                notes: this.internalNotes
                            })
                        });
                        if (response.ok) {
                            const idx = this.allUsers.findIndex(u => (u.user_id || u.id) === this.selectedUser.user_id);
                            if (idx !== -1) {
                                this.allUsers[idx].status = 'suspended';
                            }
                            this.showSuspendModal = false;
                            this.showToast('Akun berhasil ditangguhkan', 'success');

                            // Clear fields
                            this.suspendReason = '';
                            this.internalNotes = '';
                            this.suspendDuration = '7';
                        } else { this.showToast('Gagal menangguhkan akun', 'error'); }
                    } catch (error) { console.error(error); this.showToast('Terjadi kesalahan sistem', 'error'); } 
                    finally { this.loading = false; }
                },

                async unsuspendUser(user) {
                    this.selectedUser = user;
                    this.showUnsuspendModal = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async confirmUnsuspend() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/users/${this.selectedUser.user_id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                            body: JSON.stringify({ status: 'active' })
                        });
                        if (response.ok) {
                            const idx = this.allUsers.findIndex(u => (u.user_id || u.id) === this.selectedUser.user_id);
                            if (idx !== -1) this.allUsers[idx].status = 'active';
                            this.showUnsuspendModal = false;
                            this.showToast('Suspend berhasil dibuka', 'success');
                            this.$nextTick(() => lucide.createIcons());
                        } else { this.showToast('Gagal membuka suspend', 'error'); }
                    } catch (error) { console.error(error); this.showToast('Terjadi kesalahan sistem', 'error'); }
                    finally { this.loading = false; }
                },

                async unblockUser(user) {
                    this.selectedUser = user;
                    this.showUnblockModal = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async confirmUnblock() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/users/${this.selectedUser.user_id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                            body: JSON.stringify({ status: 'active' })
                        });
                        if (response.ok) {
                            const idx = this.allUsers.findIndex(u => (u.user_id || u.id) === this.selectedUser.user_id);
                            if (idx !== -1) this.allUsers[idx].status = 'active';
                            this.showUnblockModal = false;
                            this.showToast('Blokir berhasil dibuka', 'success');
                            this.$nextTick(() => lucide.createIcons());
                        } else { this.showToast('Gagal membuka blokir', 'error'); }
                    } catch (error) { console.error(error); this.showToast('Terjadi kesalahan sistem', 'error'); }
                    finally { this.loading = false; }
                },

                async confirmBlock() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/users/${this.selectedUser.user_id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                            body: JSON.stringify({ status: 'blocked' })
                        });
                        if (response.ok) {
                            const idx = this.allUsers.findIndex(u => (u.user_id || u.id) === this.selectedUser.user_id);
                            if (idx !== -1) {
                                this.allUsers[idx].status = 'blocked';
                            }
                            this.showBlockModal = false;
                            this.showToast('Pengguna berhasil diblokir', 'success');
                        } else { this.showToast('Gagal memblokir pengguna', 'error'); }
                    } catch (error) { console.error(error); this.showToast('Terjadi kesalahan sistem', 'error'); } 
                    finally { this.loading = false; }
                },

                async addUser() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/users/add', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                            body: JSON.stringify(this.newUser)
                        });
                        if (response.ok) { 
                            this.showToast('User berhasil ditambahkan', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else { 
                            const result = await response.json(); 
                            this.showToast('Gagal menambah user: ' + (result.message || 'Validation error'), 'error'); 
                        }
                    } catch (error) { 
                        console.error(error); 
                        this.showToast('Terjadi kesalahan sistem', 'error'); 
                    } 
                    finally { this.loading = false; }
                },

                exportToCSV() {
                    const headers = ['ID', 'Name', 'Email', 'NIM', 'Prodi', 'Status', 'Joined'];
                    const rows = this.filteredUsers.map(u => [u.id, u.name, u.email, u.nim, u.prodi, u.status, u.joined]);
                    let csvContent = "data:text/csv;charset=utf-8," + headers.join(",") + "\n" + rows.map(e => e.join(",")).join("\n");
                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", `users_export_${new Date().toISOString().slice(0,10)}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }));
        });
    </script>
@endpush


