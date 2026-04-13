@extends('layouts.admin')

@section('title', 'User Management')
@section('page_title', 'User Management')

@section('content')
<div class="user-management" x-data="userManagement({{ $users->toJson() }})">
    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <span class="card-label">Total Users</span>
            <span class="card-value text-primary" x-text="totalUsers"></span>
        </div>
        <div class="card">
            <span class="card-label">Inactive Users</span>
            <span class="card-value text-warning" x-text="inactiveUsers"></span>
            <span class="card-subtitle">Users with no activity > 3 days</span>
        </div>
        <div class="card">
            <span class="card-label">Suspended Users</span>
            <span class="card-value text-orange" x-text="suspendedUsers"></span>
        </div>
        <div class="card">
            <span class="card-label">Blocked Users</span>
            <span class="card-value text-danger" x-text="blockedUsers"></span>
        </div>
    </div>

    <!-- Filter Bar -->
    <!-- Filter Bar -->
    <div class="filter-container">
        <div class="filter-bar">
            <div class="search-wrapper">
                <i data-lucide="search"></i>
                <input type="text" x-model="search" placeholder="Cari nama, email, atau NIM..." id="searchInput">
            </div>
            
            <div class="filter-actions">
                <!-- Status Dropdown -->
                <div class="dropdown-wrapper" x-data="{ open: false }">
                    <button type="button" class="dropdown-btn" @click="open = !open">
                        <span x-text="status">Semua Status</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" x-show="open" @click.outside="open = false" x-cloak>
                        <div class="dropdown-item" @click="status = 'Semua Status'; open = false">
                            Semua Status
                            <i data-lucide="check" x-show="status === 'Semua Status'"></i>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-item" @click="status = 'Active'; open = false">
                            Active
                            <i data-lucide="check" x-show="status === 'Active'"></i>
                        </div>
                        <div class="dropdown-item" @click="status = 'Suspended'; open = false">
                            Suspended
                            <i data-lucide="check" x-show="status === 'Suspended'"></i>
                        </div>
                        <div class="dropdown-item" @click="status = 'Blocked'; open = false">
                            Blocked
                            <i data-lucide="check" x-show="status === 'Blocked'"></i>
                        </div>
                    </div>
                </div>

                <a href="#" @click.prevent="resetFilters()" class="export-btn" style="background: #64748B; text-decoration: none;" title="Reset Filters">
                    <i data-lucide="rotate-ccw"></i>
                    Reset
                </a>

                <button type="button" class="export-btn">
                    <i data-lucide="download"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <div class="table-info" x-text="`${totalUsers} dari ${allUsers.length} mahasiswa`">
    </div>

    <!-- Users Table -->
    <div class="table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>NIM</th>
                    <th>Program Studi</th>
                    <th>Bergabung</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="user in paginatedUsers" :key="user.id">
                <tr>
                    <td>
                        <div class="user-info">
                            <img :src="`https://ui-avatars.com/api/?name=${user.encoded_name}&background=E2E8F0&color=475569`" alt="Avatar">
                            <div class="user-details">
                                <span class="user-name" x-text="user.name"></span>
                                <span class="user-email" x-text="user.email"></span>
                            </div>
                        </div>
                    </td>
                    <td x-text="user.nim"></td>
                    <td x-text="user.prodi"></td>
                    <td x-text="user.joined"></td>
                    <td>
                        <span :class="`status-badge status-${user.status}`" x-text="user.status"></span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <!-- View Detail -->
                            <button class="action-btn" title="View Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            
                            <!-- Suspended Actions -->
                            <template x-if="user.status === 'suspended'">
                                <div style="display: contents;">
                                    <button class="action-btn" title="History Log">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                                    </button>
                                    <button class="action-btn text-success" title="Activate User">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                                    </button>
                                </div>
                            </template>
                            
                            <!-- Other Statuses (Active, Blocked, Inactive) -->
                            <template x-if="user.status !== 'suspended'">
                                <div style="display: contents;">
                                    <button class="action-btn" title="History Log">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                                    </button>
                                    
                                    <template x-if="user.status === 'blocked'">
                                        <button class="action-btn text-success" title="Unlock User">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-unlock"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                                        </button>
                                    </template>
                                    
                                    <template x-if="user.status !== 'blocked'">
                                        <button class="action-btn text-danger" title="Block User">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-off"><path d="m2 2 20 20"/><path d="M5 5a1 1 0 0 0-1 1v7c0 5 3.5 7.5 7.67 8.94a1 1 0 0 0 .67 0c1.07-.37 2.14-.85 3.14-1.46"/><path d="M19.78 14A10.87 10.87 0 0 0 20 13c0-5 0-7-9-9-2 1.33-4 2-5 2"/></svg>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </td>
                </tr>
                </template>
                
                <tr x-show="filteredUsers.length === 0" x-cloak>
                    <td colspan="6" style="text-align: center; padding: 32px; color: #64748B;">Tidak ada data ditemukan</td>
                </tr>
            </tbody>
        </table>
    <!-- Pagination Controls -->
    <div class="pagination-container" x-show="totalPages > 1" x-cloak>
        <div class="pagination-info">
            Menampilkan <span x-text="startIndex + 1"></span> - <span x-text="Math.min(endIndex, totalUsers)"></span> dari <span x-text="totalUsers"></span> data
        </div>
        <div class="pagination-buttons">
            <button class="pagination-btn" @click="prevPage()" :disabled="currentPage === 1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg> Prev
            </button>
            <div class="page-numbers">
                <template x-for="page in totalPages" :key="page">
                    <button class="pagination-btn" :class="{ 'active': currentPage === page }" @click="goToPage(page)" x-text="page"></button>
                </template>
            </div>
            <button class="pagination-btn" @click="nextPage()" :disabled="currentPage === totalPages">
                Next <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        border-top: 1px solid #E2E8F0;
        background: #fff;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }
    .pagination-info {
        font-size: 14px;
        color: #64748B;
    }
    .pagination-buttons {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pagination-btn {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 8px 12px;
        border: 1px solid #E2E8F0;
        background: #fff;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pagination-btn:not(:disabled):hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
    }
    .pagination-btn.active {
        background: #3B82F6;
        color: #fff;
        border-color: #3B82F6;
    }
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .page-numbers {
        display: flex;
        gap: 4px;
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
            perPage: 5,
            
            init() {
                this.$watch('search', () => this.currentPage = 1);
                this.$watch('status', () => this.currentPage = 1);
            },
            
            get filteredUsers() {
                let q = this.search.toLowerCase();
                let s = this.status.toLowerCase();
                
                return this.allUsers.filter(u => {
                    let matchesSearch = q === '' || 
                        u.name.toLowerCase().includes(q) || 
                        u.email.toLowerCase().includes(q) || 
                        u.nim.toLowerCase().includes(q) ||
                        u.id.toLowerCase().includes(q);
                        
                    let matchesStatus = s === 'semua status' || u.status === s;
                    
                    return matchesSearch && matchesStatus;
                });
            },
            
            get totalUsers() { return this.filteredUsers.length; },
            get inactiveUsers() { return this.filteredUsers.filter(u => u.status === 'inactive').length; },
            get suspendedUsers() { return this.filteredUsers.filter(u => u.status === 'suspended').length; },
            get blockedUsers() { return this.filteredUsers.filter(u => u.status === 'blocked').length; },

            get totalPages() { return Math.ceil(this.totalUsers / this.perPage) || 1; },
            get startIndex() { return (this.currentPage - 1) * this.perPage; },
            get endIndex() { return this.startIndex + this.perPage; },
            
            get paginatedUsers() {
                return this.filteredUsers.slice(this.startIndex, this.endIndex);
            },
            
            prevPage() { if (this.currentPage > 1) this.currentPage--; },
            nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
            goToPage(page) { this.currentPage = page; },

            resetFilters() {
                this.search = '';
                this.status = 'Semua Status';
                this.currentPage = 1;
            }
        }));
    });
</script>
@endpush
