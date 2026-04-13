@extends('layouts.admin')

@section('title', 'Team Monitoring')
@section('page_title', 'Team Monitoring')

@section('content')
<div class="user-management" x-data="teamMonitoring({{ $teams->toJson() }})">
    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <span class="card-label">Total Teams</span>
            <span class="card-value text-primary" x-text="totalTeams"></span>
        </div>
        <div class="card">
            <span class="card-label">Open Teams</span>
            <span class="card-value text-success" x-text="openTeams"></span>
            <span class="card-subtitle" style="color: #10B981;">Teams still accepting members ↗</span>
        </div>
        <div class="card">
            <span class="card-label">Pending Request</span>
            <span class="card-value text-orange" x-text="pendingRequests"></span>
            <span class="card-subtitle" style="color: #F97316;">Members awaiting approval ↗</span>
        </div>
        <div class="card">
            <span class="card-label">Full Teams</span>
            <span class="card-value text-danger" x-text="fullTeams"></span>
            <span class="card-subtitle" style="color: #EF4444;">Teams at full capacity ↗</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-container">
        <div class="filter-bar">
            <div class="search-wrapper" style="flex: 1;">
                <i data-lucide="search"></i>
                <input type="text" x-model="search" placeholder="Cari tim atau kompetisi.." id="searchInput">
            </div>
            
            <div class="filter-actions">
                <div class="team-filter">
                    <button type="button" class="team-filter-btn" :class="{ 'active': status === 'Semua' }" @click="status = 'Semua'">Semua</button>
                    <button type="button" class="team-filter-btn" :class="{ 'active': status === 'Open' }" @click="status = 'Open'">Open</button>
                    <button type="button" class="team-filter-btn" :class="{ 'active': status === 'Full' }" @click="status = 'Full'">Full</button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-info" x-text="`${filteredTeams.length} dari ${totalTeams} teams`"></div>

    <!-- Teams Table -->
    <div class="table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama Tim</th>
                    <th>Kompetisi</th>
                    <th>Anggota</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="team in paginatedTeams" :key="team.id">
                <tr>
                    <td>
                        <div class="team-info">
                            <div class="team-icon-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <span class="user-name" x-text="team.name"></span>
                        </div>
                    </td>
                    <td x-text="team.competition"></td>
                    <td>
                        <div class="avatar-group">
                            <template x-for="member in team.top_members">
                                <img :src="`https://ui-avatars.com/api/?name=${member.encoded_name}&background=E2E8F0&color=475569`" alt="Avatar">
                            </template>
                            
                            <template x-if="team.active_count > 3">
                                <span class="more-badge" x-text="`+${team.active_count - 3}`"></span>
                            </template>
                            
                            <div class="anggota-text">
                                <span x-text="`${team.active_count}/${team.max_member} Anggota`"></span>
                                <template x-if="team.pending_count > 0">
                                    <span class="pending" x-text="`(${team.pending_count} pending)`"></span>
                                </template>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span :class="`status-badge status-${team.status.toLowerCase()}`" x-text="team.status"></span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <!-- View Detail -->
                            <button class="action-btn" title="View Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            
                            <!-- Delete Team -->
                            <button class="action-btn text-danger" title="Delete Team">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                </template>
                
                <tr x-show="filteredTeams.length === 0" x-cloak>
                    <td colspan="5" style="text-align: center; padding: 32px; color: #64748B;">Tidak ada tim ditemukan</td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div class="pagination-container" x-show="totalPages > 1" x-cloak>
            <div class="pagination-info">
                Menampilkan <span x-text="startIndex + 1"></span> - <span x-text="Math.min(endIndex, filteredTeams.length)"></span> dari <span x-text="filteredTeams.length"></span> data
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
</div>
@endsection

@push('styles')
<style>
    /* Pagination Styles Reused from Users */
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
    .pagination-info { font-size: 14px; color: #64748B; }
    .pagination-buttons { display: flex; align-items: center; gap: 8px; }
    .pagination-btn { display: flex; align-items: center; gap: 4px; padding: 8px 12px; border: 1px solid #E2E8F0; background: #fff; border-radius: 6px; font-size: 14px; font-weight: 500; color: #475569; cursor: pointer; transition: all 0.2s; }
    .pagination-btn:not(:disabled):hover { background: #F8FAFC; border-color: #CBD5E1; }
    .pagination-btn.active { background: #3B82F6; color: #fff; border-color: #3B82F6; }
    .pagination-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .page-numbers { display: flex; gap: 4px; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('teamMonitoring', (initialTeams) => ({
            allTeams: initialTeams,
            search: '',
            status: 'Semua', // Semua, Open, Full
            
            currentPage: 1,
            perPage: 12,
            
            init() {
                this.$watch('search', () => this.currentPage = 1);
                this.$watch('status', () => this.currentPage = 1);
            },
            
            get filteredTeams() {
                let q = this.search.toLowerCase();
                let s = this.status;
                
                return this.allTeams.filter(t => {
                    let matchesSearch = q === '' || 
                        t.name.toLowerCase().includes(q) || 
                        t.competition.toLowerCase().includes(q);
                        
                    let matchesStatus = s === 'Semua' || t.status === s;
                    
                    return matchesSearch && matchesStatus;
                });
            },
            
            get totalTeams() { return this.allTeams.length; },
            get openTeams() { return this.allTeams.filter(t => t.status === 'Open').length; },
            get fullTeams() { return this.allTeams.filter(t => t.status === 'Full').length; },
            get pendingRequests() { 
                return this.allTeams.reduce((sum, t) => sum + t.pending_count, 0); 
            },

            get totalPages() { return Math.ceil(this.filteredTeams.length / this.perPage) || 1; },
            get startIndex() { return (this.currentPage - 1) * this.perPage; },
            get endIndex() { return this.startIndex + this.perPage; },
            
            get paginatedTeams() {
                return this.filteredTeams.slice(this.startIndex, this.endIndex);
            },
            
            prevPage() { if (this.currentPage > 1) this.currentPage--; },
            nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
            goToPage(page) { this.currentPage = page; }
        }));
    });
</script>
@endpush
