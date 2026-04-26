@extends('layouts.admin')

@section('title', 'Team Monitoring')
@section('page_title', 'Team Monitoring')

@section('content')
<div class="team-monitoring" x-data="teamMonitoring({
    initialTeams: {{ $teams->toJson() }},
    pendingTeams: {{ $pendingTeams->toJson() }},
    stats: {{ json_encode($stats) }}
})" x-cloak>
    
    <!-- Modals (Top of Scope) -->
    <div x-show="showDetailModal" class="modal-overlay" x-transition:enter="transition-fade" x-transition:leave="transition-fade" style="display: none; z-index: 9998;">
        <div class="modal-container detail-modal" @click.away="showDetailModal = false" x-transition:enter="modal-slide-in" style="max-width: 600px; text-align: left;">
            <div class="modal-header-premium">
                <div class="header-icon">
                    <i data-lucide="users"></i>
                </div>
                <div class="header-title-wrapper">
                    <h3 x-text="selectedDetailTeam?.team_name"></h3>
                    <span class="competition-badge" x-text="selectedDetailTeam?.competition_name"></span>
                </div>
                <button type="button" @click="showDetailModal = false" class="close-btn">
                    <i data-lucide="x"></i>
                </button>
            </div>
            
            <div class="modal-body-premium">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Ketua Tim</label>
                        <div class="leader-profile">
                            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(selectedDetailTeam?.leader_name || 'L')}&background=1E293B&color=fff`" alt="Leader">
                            <div class="leader-text">
                                <span class="name" x-text="selectedDetailTeam?.leader_name || 'Tidak diketahui'"></span>
                                <span class="label">Leader</span>
                            </div>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Waktu Pendaftaran</label>
                        <div class="info-value">
                            <i data-lucide="calendar" style="width: 16px;"></i>
                            <span x-text="selectedDetailTeam ? new Date(selectedDetailTeam.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''"></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Kapasitas Tim</label>
                        <div class="info-value">
                            <i data-lucide="users" style="width: 16px;"></i>
                            <span x-text="`0 dari ${selectedDetailTeam?.max_member || 2} Anggota`"></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <label>Status Saat Ini</label>
                        <div class="info-value">
                            <span class="badge-status-pending">Menunggu Persetujuan Admin</span>
                        </div>
                    </div>
                </div>

                <div class="team-bio" x-show="selectedDetailTeam?.description">
                    <label>Bio / Deskripsi Tim</label>
                    <p x-text="selectedDetailTeam?.description"></p>
                </div>
            </div>

            <div class="modal-footer-premium">
                <button type="button" class="btn-secondary" @click="showDetailModal = false">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" class="modal-overlay" x-transition:enter="transition-fade" x-transition:leave="transition-fade" style="display: none; z-index: 9999;">
        <div class="modal-container respond-modal" @click.away="showDeleteModal = false" x-transition:enter="modal-slide-in">
            <div class="respond-icon-circle bg-red-light">
                <i data-lucide="trash-2" class="text-red"></i>
            </div>
            <h3>Hapus Tim?</h3>
            <p>
                Apakah Anda yakin ingin menghapus tim <strong x-text="selectedDeleteTeam?.name"></strong>? 
            </p>
            <div class="modal-actions">
                <button type="button" @click="showDeleteModal = false" class="btn-secondary" :disabled="loading">
                    Batal
                </button>
                <button type="button" @click="confirmDelete()" class="btn-danger" :disabled="loading">
                    <template x-if="loading"><span class="loading-spinner"></span></template>
                    <span x-text="loading ? 'Menghapus...' : 'Ya, Hapus Tim'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Respond Confirmation Modal -->
    <div x-show="showRespondModal" class="modal-overlay" x-transition:enter="transition-fade" x-transition:leave="transition-fade" style="display: none; z-index: 9999;">
        <div class="modal-container respond-modal" @click.away="showRespondModal = false" x-transition:enter="modal-slide-in">
            <div :class="`respond-icon-circle ${respondAction === 'approve' ? 'bg-green-light' : 'bg-red-light'}`">
                <i :data-lucide="respondAction === 'approve' ? 'check-circle' : 'slash'"></i>
            </div>
            <h3 x-text="respondAction === 'approve' ? (respondType === 'team' ? 'Setujui Tim?' : 'Setujui Permohonan?') : (respondType === 'team' ? 'Tolak Tim?' : 'Tolak Permohonan?')"></h3>
            <p>
                Konfirmasi permohonan untuk <span x-text="respondType === 'team' ? 'tim ' : 'anggota '"></span>
                <strong x-text="selectedItem?.team_name || selectedItem?.user_name"></strong>.
            </p>
            <div class="modal-actions">
                <button type="button" @click="showRespondModal = false" class="btn-secondary" :disabled="loading">
                    <i data-lucide="x" style="width: 16px;"></i> Batal
                </button>
                <button type="button" @click="confirmRespond()" :class="respondAction === 'approve' ? 'btn-success' : 'btn-danger'" :disabled="loading">
                    <template x-if="loading"><span class="loading-spinner"></span></template>
                    <i x-show="!loading" :data-lucide="respondAction === 'approve' ? 'check' : 'slash'" style="width: 16px;"></i>
                    <span x-text="loading ? 'Memproses...' : (respondAction === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak')"></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- View 1: Main Dashboard -->
    <div x-show="viewMode === 'dashboard'" x-transition:enter="transition-fade">
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Teams</span>
            <div class="stat-value text-blue" x-text="stats.total"></div>
        </div>
        <div class="stat-card">
            <span class="stat-label">Open Teams</span>
            <div class="stat-value text-green" x-text="stats.open"></div>
            <span class="stat-sublabel text-green">Teams still accepting members &nearr;</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Team</span>
            <div class="stat-value text-orange" x-text="stats.pending"></div>
            <span class="stat-sublabel text-orange">Teams awaiting approval &searr;</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Full Teams</span>
            <div class="stat-value text-red" x-text="stats.full"></div>
            <span class="stat-sublabel text-red">Teams at full capacity &nearr;</span>
        </div>
    </div>

    <!-- Menunggu Persetujuan Section (Teams) -->
    <template x-if="pendingTeams.length > 0">
        <div class="pending-section">
            <div class="section-header">
                <h3>Menunggu Persetujuan (<span x-text="pendingTeams.length"></span>)</h3>
                <a href="#" @click.prevent="viewMode = 'requests'" class="view-all">Lihat Semua</a>
            </div>

            <div class="pending-list">
                <template x-for="pTeam in pendingTeams.slice(0, 3)" :key="pTeam.team_id">
                    <div class="pending-card">
                        <div class="pending-card-left">
                            <div class="team-large-icon">
                                <i data-lucide="users" style="width: 48px; height: 48px;"></i>
                            </div>
                        </div>

                        <div class="pending-card-mid">
                            <h4 class="team-name" x-text="pTeam.team_name"></h4>
                            <div class="competition-info">
                                <span>Kompetisi</span>
                                <span class="dot">&bull;</span>
                                <span x-text="pTeam.competition_name"></span>
                            </div>
                            <div class="submitter-info">
                                Submitted by : <span class="submitter-name" x-text="pTeam.leader_name || 'Leader'"></span>
                                <span class="submitter-time" x-text="' &bull; ' + formatTimeAgo(pTeam.created_at)"></span>
                            </div>
                        </div>

                        <div class="pending-card-right">
                            <div class="member-count-status">
                                <div class="avatar-circle">
                                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(pTeam.leader_name || 'L')}&background=1E293B&color=fff`" alt="User">
                                </div>
                                <div class="count-label">
                                    <span class="text-blue" x-text="`0/${pTeam.max_member || 2}`"></span>
                                    <span>Anggota</span>
                                </div>
                            </div>
                            <div class="pending-actions">
                                <button type="button" class="btn-action-outline" @click.stop="openDetailModal(pTeam)" style="position: relative; z-index: 10;">
                                    <i data-lucide="eye" style="width: 16px;"></i> Lihat Detail
                                </button>
                                <button type="button" class="btn-action-success" @click.stop="openRespondModal(pTeam, 'team', 'approve')" style="position: relative; z-index: 10;">
                                    <i data-lucide="check" style="width: 16px;"></i> Setujui
                                </button>
                                <button type="button" class="btn-action-danger" @click.stop="openRespondModal(pTeam, 'team', 'reject')" style="position: relative; z-index: 10;">
                                    <i data-lucide="x" style="width: 16px;"></i> Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Main Toolbar -->
    <div class="main-toolbar">
        <div class="search-wrapper">
            <i data-lucide="search"></i>
            <input type="text" x-model.debounce.300ms="search" placeholder="Cari tim atau kompetisi..">
        </div>
        
        <div class="filter-pills">
            <button class="pill-btn" :class="{ 'active': statusFilter === 'Semua' }" @click="statusFilter = 'Semua'">Semua</button>
            <button class="pill-btn" :class="{ 'active': statusFilter === 'Open' }" @click="statusFilter = 'Open'">Open</button>
            <button class="pill-btn" :class="{ 'active': statusFilter === 'Full' }" @click="statusFilter = 'Full'">Full</button>
        </div>
    </div>

    <div class="table-info" x-text="`${filteredTeams.length} dari ${stats.total} teams`"></div>

    <!-- Teams Table -->
    <div class="table-container">
        <table class="premium-table">
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
                        <div class="cell-team">
                            <div class="team-icon-sm">
                                <i data-lucide="users" style="width: 18px;"></i>
                            </div>
                            <span class="team-name-text" x-text="team.name"></span>
                        </div>
                    </td>
                    <td x-text="team.competition"></td>
                    <td>
                        <div class="cell-members">
                            <div class="avatar-group">
                                <template x-for="member in team.top_members">
                                    <img :src="`https://ui-avatars.com/api/?name=${member.encoded_name}&background=F1F5F9&color=475569`" alt="Avatar">
                                </template>
                                <template x-if="team.active_count > 3">
                                    <span class="more-badge" x-text="`+${team.active_count - 3}`"></span>
                                </template>
                            </div>
                            <div class="member-stats">
                                <span class="count-main">
                                    <strong class="text-blue" x-text="`${team.active_count}/${team.max_member}`"></strong> 
                                    Anggota
                                </span>
                                <template x-if="team.pending_count > 0">
                                    <span class="count-pending" x-text="`(${team.pending_count} pending)`"></span>
                                </template>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span :class="`badge-status badge-${team.status.toLowerCase()}`" x-text="team.status"></span>
                    </td>
                    <td>
                        <div class="cell-actions">
                            <button type="button" class="action-btn" title="View Detail" @click="openDetailModal(team)">
                                <i data-lucide="eye" style="width: 18px;"></i>
                            </button>
                            <button type="button" class="action-btn text-red" title="Delete Team" @click="openDeleteModal(team)">
                                <i data-lucide="trash-2" style="width: 18px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                </template>
                
                <tr x-show="filteredTeams.length === 0">
                    <td colspan="5" class="empty-row">Tidak ada tim ditemukan</td>
                </tr>
            </tbody>
        </table>

    </div> <!-- end table-container -->

    <!-- Pagination -->
    <div class="pagination-footer" x-show="totalPages > 1">
        <div class="pagination-info">
            Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to
            <span x-text="Math.min(currentPage * perPage, filteredTeams.length)"></span> of
            <span x-text="filteredTeams.length"></span> entries
        </div>
        <div class="pagination-btns">
            <button class="page-nav-btn" @click="prevPage()" :disabled="currentPage === 1">
                <i data-lucide="chevron-left" style="width: 16px;"></i> Prev
            </button>
            <div class="page-numbers">
                <template x-for="page in totalPages" :key="page">
                    <button class="page-num-btn" :class="{ 'active': currentPage === page }" @click="goToPage(page)" x-text="page"></button>
                </template>
            </div>
            <button class="page-nav-btn" @click="nextPage()" :disabled="currentPage === totalPages">
                Next <i data-lucide="chevron-right" style="width: 16px;"></i>
            </button>
        </div>
    </div>
    </div><!-- end viewMode === 'dashboard' -->

    <!-- View 2: All Pending Requests View -->
    <div x-show="viewMode === 'requests'" class="requests-view-wrapper">
        <div class="requests-view-header">
            <div class="header-left">
                <button @click="viewMode = 'dashboard'" class="back-btn">
                    <i data-lucide="arrow-left" style="width: 18px;"></i> Kembali
                </button>
                <div class="header-titles">
                    <h2>Permohonan Tim Baru</h2>
                    <span class="badge-count text-orange" x-text="pendingTeams.length + ' Permohonan'"></span>
                </div>
            </div>
        </div>

        <div class="requests-grid">
            <div x-show="pendingTeams.length === 0" style="padding: 2rem; text-align: center; color: #64748B;">
                Tidak ada permohonan tim saat ini.
            </div>
            <template x-for="pTeam in pendingTeams" :key="pTeam.team_id">
                <div class="pending-card">
                    <div class="pending-card-left">
                        <div class="team-large-icon">
                            <i data-lucide="users"></i>
                        </div>
                    </div>
                    <div class="pending-card-mid">
                        <h4 class="team-name" x-text="pTeam.team_name"></h4>
                        <div class="competition-info">
                            <span x-text="pTeam.competition_name"></span>
                        </div>
                        <div class="submitter-info">
                            Oleh: <span class="submitter-name" x-text="pTeam.leader_name || 'Leader'"></span>
                            <span class="submitter-time" x-text="' &bull; ' + formatTimeAgo(pTeam.created_at)"></span>
                        </div>
                    </div>
                    <div class="pending-card-right">
                        <div class="member-count-status">
                            <div class="avatar-circle">
                                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(pTeam.leader_name || 'L')}&background=1E293B&color=fff`" alt="User">
                            </div>
                            <div class="count-label">
                                <span class="text-blue" x-text="`0/${pTeam.max_member || 2}`"></span>
                                <span>Anggota</span>
                            </div>
                        </div>
                        <div class="pending-actions">
                            <button class="btn-action-outline" @click="openDetailModal(pTeam)">
                                <i data-lucide="eye" style="width: 16px;"></i> Detail
                            </button>
                            <button class="btn-action-success" @click="openRespondModal(pTeam, 'team', 'approve')">
                                <i data-lucide="check" style="width: 16px;"></i> Setujui
                            </button>
                            <button class="btn-action-danger" @click="openRespondModal(pTeam, 'team', 'reject')">
                                <i data-lucide="x" style="width: 16px;"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div><!-- end viewMode === 'requests' -->

    <!-- Toast Notifications -->
    <div class="toast-container" x-show="toast.show" x-transition:enter="toast-enter" x-transition:leave="toast-leave" style="display: none;">
        <div :class="`toast toast-${toast.type}`">
            <i :data-lucide="toast.icon"></i>
            <span x-text="toast.message"></span>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    :root {
        --primary: #3B82F6;
        --secondary: #64748B;
        --success: #10B981;
        --warning: #F59E0B;
        --danger: #EF4444;
        --orange: #F97316;
    }

    [x-cloak] { display: none !important; }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: #fff;
        padding: 1.25rem 1.5rem;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #E2E8F0;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .stat-card:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }

    .stat-label {
        font-size: 0.875rem;
        color: #64748B;
        font-weight: 600;
        display: block;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }

    .stat-sublabel {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .text-blue { color: #3B82F6; }
    .text-green { color: #10B981; }
    .text-orange { color: #F97316; }
    .text-red { color: #EF4444; }

    /* Pending Section Override */
    .team-monitoring .pending-section {
        margin: 1.5rem 0 2rem 0 !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        box-shadow: none !important;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .section-header h3 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1E293B;
    }

    .view-all {
        font-size: 0.875rem;
        font-weight: 600;
        color: #3B82F6;
        text-decoration: none;
    }

    .pending-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .pending-card {
        background: #FEF3E2;
        border: 1px solid #FFEDD5;
        border-radius: 12px;
        padding: 8px 14px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .team-large-icon {
        width: 52px;
        height: 52px;
        background: #FFEDD5;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #F97316;
        flex-shrink: 0;
    }

    .team-large-icon i {
        width: 28px !important;
        height: 28px !important;
    }

    .pending-card-mid {
        flex: 1;
    }

    .pending-card-mid .team-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 2px;
    }

    .competition-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #64748B;
        margin-bottom: 4px;
    }

    .dot { color: #CBD5E1; }

    .submitter-info {
        font-size: 0.8rem;
        color: #64748B;
    }

    .submitter-name {
        font-weight: 700;
        color: #475569;
    }

    .pending-card-right {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding-left: 1.5rem;
        border-left: 1px solid #FFEDD5;
    }

    .member-count-status {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 110px;
    }

    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;
    }

    .avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .count-label {
        display: flex;
        flex-direction: column;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
    }

    .pending-actions {
        display: flex;
        flex-direction: row;
        gap: 8px;
    }

    .pending-actions button {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .btn-action-outline { background: #fff; border-color: #E2E8F0; color: #475569; }
    .btn-action-success { background: #fff; border-color: #10B981; color: #10B981; }
    .btn-action-danger { background: #fff; border-color: #EF4444; color: #EF4444; }

    .btn-action-outline:hover { background: #F8FAFC; }
    .btn-action-success:hover { background: #F0FDF4; }
    .btn-action-danger:hover { background: #FEF2F2; }

    /* Toolbar */
    .main-toolbar {
        background: transparent;
        padding: 0;
        border-radius: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        box-shadow: none;
    }

    .search-wrapper {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-wrapper i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        width: 18px;
    }

    .search-wrapper input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .search-wrapper input:focus { border-color: #3B82F6; }

    .filter-pills {
        display: flex;
        gap: 0.5rem;
    }

    .pill-btn {
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        background: #fff;
        border: 1px solid #E2E8F0;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s;
    }

    .pill-btn.active {
        background: #3B82F6;
        color: #fff;
        border-color: #3B82F6;
    }

    .table-info {
        font-size: 0.875rem;
        color: #64748B;
        margin-bottom: 1rem;
        font-weight: 500;
    }


    .table-container {
        background: #fff !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        margin-bottom: 20px !important;
    }

    .premium-table {
        width: 100% !important;
        border-collapse: collapse !important;
        background: white !important;
    }

    .premium-table th {
        text-align: left;
        padding: 1rem 1.5rem;
        background: #F8FAFC;
        color: #475569;
        font-weight: 600;
        font-size: 0.875rem;
        border-bottom: 1px solid #E2E8F0;
    }

    .premium-table td {
        padding: 1rem 1.5rem;
        background: #fff;
        border-bottom: 1px solid #E2E8F0;
        vertical-align: middle;
    }

    .cell-team {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .team-icon-sm {
        width: 32px;
        height: 32px;
        background: #EFF6FF;
        color: #3B82F6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .team-name-text {
        font-weight: 700;
        color: #1E293B;
    }

    .cell-members {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .avatar-group {
        display: flex;
        align-items: center;
    }

    .avatar-group img, .avatar-group .more-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #fff;
        margin-left: -8px;
    }

    .avatar-group img:first-child { margin-left: 0; }

    .avatar-group .more-badge {
        background: #EFF6FF;
        color: #3B82F6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .member-stats {
        display: flex;
        flex-direction: column;
        font-size: 0.75rem;
    }

    .count-main { color: #64748B; font-weight: 500; }
    .count-pending { color: #F97316; font-weight: 600; }

    .badge-status {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-open { background: #DCFCE7; color: #166534; }
    .badge-full { background: #FEE2E2; color: #991B1B; }

    .cell-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        width: 34px;
        height: 34px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: #EFF6FF;
        border-color: #BFDBFE;
        color: #2563EB;
    }

    .action-btn.text-red:hover {
        background: #FEE2E2;
        border-color: #FECACA;
        color: #B91C1C;
    }

    .empty-row {
        text-align: center;
        padding: 3rem !important;
        color: #94A3B8;
        font-weight: 500;
    }

    /* Pagination */
    .pagination-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #E2E8F0;
    }

    .pagination-info {
        font-size: 0.875rem;
        color: #64748B;
        font-weight: 500;
    }

    .pagination-btns {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .page-nav-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #E2E8F0;
        color: #475569;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .page-nav-btn:hover:not(:disabled) {
        background: #F8FAFC;
        border-color: #CBD5E1;
    }

    .page-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .page-numbers {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .page-num-btn {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #E2E8F0;
        color: #475569;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .page-num-btn:hover:not(.active) {
        background: #F8FAFC;
        border-color: #CBD5E1;
    }

    .page-num-btn.active {
        background: #3B82F6;
        border-color: #3B82F6;
        color: #fff;
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-container {
        background: #fff;
        border-radius: 20px;
        width: 100%;
        max-width: 450px;
        padding: 2rem;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        text-align: center;
    }

    .respond-icon-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
    }

    .bg-green-light { background: #DCFCE7; color: #10B981; }
    .bg-red-light { background: #FEE2E2; color: #EF4444; }

    .respond-modal h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 1rem;
    }

    .respond-modal p {
        color: #64748B;
        font-size: 0.875rem;
        line-height: 1.5;
        margin-bottom: 2rem;
    }

    .modal-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .modal-actions button {
        padding: 0.75rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-secondary { 
        background: #F1F5F9; 
        color: #475569; 
        border: none;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-success { 
        background: #10B981; 
        color: #fff; 
        border: none;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-danger { 
        background: #EF4444; 
        color: #fff; 
        border: none;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    /* Detail Modal Premium Styles */
    .modal-header-premium {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #F1F5F9;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .modal-header-premium .header-icon {
        width: 48px;
        height: 48px;
        background: #EFF6FF;
        color: #3B82F6;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header-title-wrapper h3 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1E293B;
        margin: 0;
    }

    .competition-badge {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
        background: #F8FAFC;
        padding: 2px 8px;
        border-radius: 4px;
        border: 1px solid #E2E8F0;
    }

    .close-btn {
        position: absolute;
        right: 0;
        top: 0;
        background: none;
        border: none;
        color: #94A3B8;
        cursor: pointer;
        padding: 4px;
        transition: color 0.2s;
    }

    .close-btn:hover { color: #64748B; }

    .modal-body-premium .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-item label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #94A3B8;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        margin-bottom: 0.5rem;
    }

    .leader-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .leader-profile img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }

    .leader-text {
        display: flex;
        flex-direction: column;
    }

    .leader-text .name {
        font-size: 0.875rem;
        font-weight: 700;
        color: #1E293B;
    }

    .leader-text .label {
        font-size: 0.75rem;
        color: #64748B;
    }

    .info-value {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
    }

    .badge-status-pending {
        background: #FFF7ED;
        color: #C2410C;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        border: 1px solid #FFEDD5;
    }

    .team-bio {
        background: #F8FAFC;
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid #F1F5F9;
    }

    .team-bio p {
        font-size: 0.875rem;
        color: #475569;
        line-height: 1.6;
        margin: 0;
    }

    .modal-footer-premium {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #F1F5F9;
    }

    .action-group {
        display: flex;
        gap: 0.75rem;
    }

    .btn-danger-outline {
        background: #fff;
        border: 1px solid #EF4444;
        color: #EF4444;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-danger-outline:hover { background: #FEF2F2; }

    /* Toast */
    .toast-container {
        position: fixed;
        top: 2rem;
        right: 2rem;
        z-index: 9999;
    }

    .toast {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        font-size: 0.875rem;
        border-left: 4px solid #E2E8F0;
    }

    .toast-success { border-left-color: #10B981; }
    .toast-error { border-left-color: #EF4444; }

    /* Transitions */
    .transition-fade { transition: all 0.3s ease; }
    .modal-slide-in { animation: slideIn 0.3s ease-out; }
    @keyframes slideIn {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .toast-enter { transform: translateY(-20px); opacity: 0; }
    .toast-leave { transform: translateX(20px); opacity: 0; }

    /* Requests View */
    .requests-view-wrapper {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .requests-view-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .back-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        color: #64748B;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .back-btn:hover { background: #F8FAFC; color: #1E293B; }

    .header-titles h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1E293B;
        margin-bottom: 0.25rem;
    }

    .badge-count {
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        background: #FEF3E2;
        color: #F97316;
        border-radius: 9999px;
    }

    .requests-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
        gap: 1.5rem;
    }

    .loading-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('teamMonitoring', (config) => ({
            allTeams: config.initialTeams || [],
            pendingTeams: config.pendingTeams || [],
            stats: config.stats || { total: 0, open: 0, full: 0, pending: 0 },
            
            search: '',
            statusFilter: 'Semua',
            currentPage: 1,
            perPage: 5,
            
            loading: false,
            showRespondModal: false,
            showDetailModal: false,
            showDeleteModal: false,
            selectedItem: null,
            selectedDetailTeam: null,
            selectedDeleteTeam: null,
            viewMode: 'dashboard', // 'dashboard' | 'requests'
            respondType: 'team', // 'team' | 'member'
            respondAction: 'approve',
            
            toast: { show: false, message: '', type: 'success', icon: 'check-circle' },

            init() {
                this.$nextTick(() => this.reinitIcons());
                
                this.$watch('search', () => {
                    this.currentPage = 1;
                    this.$nextTick(() => this.reinitIcons());
                });
                this.$watch('statusFilter', () => {
                    this.currentPage = 1;
                    this.$nextTick(() => this.reinitIcons());
                });
                this.$watch('currentPage', () => this.$nextTick(() => this.reinitIcons()));
                this.$watch('viewMode', () => this.$nextTick(() => this.reinitIcons()));
                this.$watch('showRespondModal', () => this.$nextTick(() => this.reinitIcons()));
                this.$watch('showDetailModal', () => this.$nextTick(() => this.reinitIcons()));
                this.$watch('showDeleteModal', () => this.$nextTick(() => this.reinitIcons()));
            },

            get filteredTeams() {
                let q = this.search.toLowerCase();
                let f = this.statusFilter;
                
                return this.allTeams.filter(t => {
                    let matchesSearch = q === '' || 
                        t.name.toLowerCase().includes(q) || 
                        t.competition.toLowerCase().includes(q);
                    let matchesFilter = f === 'Semua' || t.status === f;
                    return matchesSearch && matchesFilter;
                });
            },

            get totalPages() { return Math.ceil(this.filteredTeams.length / this.perPage) || 1; },
            get startIndex() { return (this.currentPage - 1) * this.perPage; },
            get endIndex() { return this.startIndex + this.perPage; },
            get paginatedTeams() { return this.filteredTeams.slice(this.startIndex, this.endIndex); },

            prevPage() { if (this.currentPage > 1) this.currentPage--; },
            nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
            goToPage(page) { this.currentPage = page; },

            reinitIcons() {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            },

            openRespondModal(item, type, action) {
                this.selectedItem = item;
                this.respondType = type;
                this.respondAction = action;
                this.showRespondModal = true;
            },

            openDetailModal(team) {
                // Normalize data for both pending and approved formats
                this.selectedDetailTeam = {
                    team_name: team.name || team.team_name,
                    competition_name: team.competition || team.competition_name,
                    leader_name: team.leader_name,
                    created_at: team.created_at,
                    description: team.description,
                    max_member: team.max_member
                };
                this.showDetailModal = true;
            },

            openDeleteModal(team) {
                this.selectedDeleteTeam = team;
                this.showDeleteModal = true;
            },

            async confirmDelete() {
                if (!this.selectedDeleteTeam || this.loading) return;
                this.loading = true;
                
                const id = this.selectedDeleteTeam.id;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                try {
                    const response = await fetch(`/admin/teams/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showToast(data.message, 'success');
                        this.showDeleteModal = false;
                        this.allTeams = this.allTeams.filter(t => t.id !== id);
                        this.stats.total = this.allTeams.length;
                    } else {
                        this.showToast(data.message || 'Gagal menghapus tim.', 'error');
                    }
                } catch (error) {
                    this.showToast('Terjadi kesalahan koneksi.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async confirmRespond() {
                if (!this.selectedItem || this.loading) return;
                this.loading = true;
                
                const id = this.respondType === 'team' ? this.selectedItem.team_id : this.selectedItem.member_id;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const url = `/admin/teams/${id}/respond`;
                
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            'action': this.respondAction,
                            'type': this.respondType,
                        }),
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showToast(data.message, 'success');
                        this.showRespondModal = false;
                        
                        if (this.respondType === 'team') {
                            // Remove from pendingTeams
                            this.pendingTeams = this.pendingTeams.filter(t => t.team_id !== id);
                            this.stats.pending = this.pendingTeams.length;
                            
                            // If approved, add to allTeams (mock for UI)
                            if (this.respondAction === 'approve') {
                                window.location.reload(); // Easier to reload to get all members etc
                            }
                        } else {
                            // Logic for member response if needed in this view
                        }
                    } else {
                        this.showToast(data.message || 'Gagal memproses permintaan.', 'error');
                    }
                } catch (error) {
                    this.showToast('Terjadi kesalahan koneksi.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.icon = type === 'success' ? 'check-circle' : 'x-circle';
                this.toast.show = true;
                this.$nextTick(() => this.reinitIcons());
                setTimeout(() => { this.toast.show = false; }, 3000);
            },

            formatTimeAgo(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                const now = new Date();
                const diffInMinutes = Math.floor((now - date) / 60000);
                
                if (diffInMinutes < 1) return 'Baru saja';
                if (diffInMinutes < 60) return `${diffInMinutes} menit yang lalu`;
                if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} jam yang lalu`;
                return `${Math.floor(diffInMinutes / 1440)} hari yang lalu`;
            }
        }));
    });
</script>
@endpush
