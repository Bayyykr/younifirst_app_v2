@extends('layouts.admin')

@section('title', 'Lost and Found')
@section('page_title', 'Lost and Found')

@section('content')
<div class="lostfound-component" x-data="lostFoundManagement({{ $items->toJson() }})">
    <!-- Stats Section -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-label">Postingan Ditemukan</span>
                <div class="stat-value text-success">{{ $stats['found'] }}</div>
                <span class="stat-sublabel text-success">User finding items</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-label">Postingan Hilang</span>
                <div class="stat-value text-danger">{{ $stats['lost'] }}</div>
                <span class="stat-sublabel text-danger">User losing items</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-label">Sudah Diklaim</span>
                <div class="stat-value text-warning">{{ $stats['claimed'] }}</div>
                <span class="stat-sublabel">Total items claimed</span>
            </div>
        </div>
    </div>

    <!-- Toolbar Section -->
    <div class="toolbar-section">
        <div class="search-box">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" x-model.debounce.300ms="search" placeholder="Cari nama, email, atau NIM..." class="form-input search-input">
        </div>
        <div class="filter-actions">
            <!-- Status Dropdown (Custom to match your User style if wanted, or plain select) -->
            <select x-model="statusFilter" class="form-select status-filter">
                <option value="all">Semua Status</option>
                <option value="lost">Hilang</option>
                <option value="found">Ditemukan</option>
                <option value="claimed">Diklaim</option>
            </select>
            <button class="btn btn-primary">
                <i data-lucide="plus" class="icon-sm"></i>
                Posting
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Pelapor</th>
                    <th>Lokasi</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="item in paginatedItems" :key="item.id">
                    <tr>
                        <td>
                            <div class="item-cell">
                                <div class="item-img-container">
                                    <template x-if="item.photo">
                                        <img :src="item.photo" :alt="item.name" class="item-thumb">
                                    </template>
                                    <template x-if="!item.photo">
                                        <div class="item-thumb-placeholder">
                                            <i data-lucide="package" class="icon-xs"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="item-info">
                                    <span class="item-name" x-text="item.name"></span>
                                    <span class="item-desc" x-text="item.description.substring(0, 30) + '...'"></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="reporter-info">
                                <span class="reporter-name" x-text="item.reporter_name"></span>
                                <span class="reporter-nim" x-text="item.reporter_nim"></span>
                            </div>
                        </td>
                        <td>
                            <span class="location-text" x-text="item.location"></span>
                        </td>
                        <td>
                            <span class="date-text" x-text="item.date"></span>
                        </td>
                        <td>
                            <span :class="`status-badge ${item.status_class}`" x-text="item.status_label"></span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button title="View" class="btn-icon text-neutral"><i data-lucide="eye" class="icon-xs"></i></button>
                                <button title="Edit" class="btn-icon text-success"><i data-lucide="edit-3" class="icon-xs"></i></button>
                                <button title="Delete" class="btn-icon text-danger"><i data-lucide="trash-2" class="icon-xs"></i></button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredItems.length === 0" x-cloak>
                    <td colspan="6" class="text-center py-12">
                        <div class="empty-state">
                            <i data-lucide="inbox" class="icon-lg opacity-20" style="width: 48px; height: 48px; margin: 0 auto;"></i>
                            <p class="text-neutral-500 mt-2">Tidak ada data ditemukan</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Client-side Pagination Controls -->
        <div class="pagination-container" x-show="totalPages > 1" x-cloak>
            <div class="pagination-info">
                Menampilkan <span x-text="startIndex + 1"></span> - <span x-text="Math.min(endIndex, totalItemsCount)"></span> dari <span x-text="totalItemsCount"></span> data
            </div>
            <div class="pagination-buttons">
                <button class="pagination-btn" @click="prevPage" :disabled="currentPage === 1">
                    <i data-lucide="chevron-left" class="icon-xs"></i> Prev
                </button>
                <div class="page-numbers">
                    <template x-for="p in totalPages" :key="p">
                        <button class="pagination-btn" :class="{ 'active': currentPage === p }" @click="goToPage(p)" x-text="p"></button>
                    </template>
                </div>
                <button class="pagination-btn" @click="nextPage" :disabled="currentPage === totalPages">
                    Next <i data-lucide="chevron-right" class="icon-xs"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Styling */
.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    border: 1px solid #F3F4F6;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6B7280;
    display: block;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-sublabel {
    font-size: 0.75rem;
    color: #9CA3AF;
}

/* Toolbar */
.toolbar-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.search-box {
    position: relative;
    flex: 1;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9CA3AF;
    width: 16px;
    height: 16px;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border-radius: 0.75rem;
    background: white;
    border: 1px solid #E5E7EB;
}

.filter-actions {
    display: flex;
    gap: 0.75rem;
}

.status-filter {
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    background: white;
    font-size: 0.875rem;
}

.btn-primary {
    background: #4F46E5;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    cursor: pointer;
}

/* Table Styling */
.table-card {
    background: white;
    border-radius: 1rem;
    border: 1px solid #F3F4F6;
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 1rem 1.5rem;
    background: #F9FAFB;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6B7280;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #F3F4F6;
}

.data-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #F3F4F6;
    vertical-align: middle;
}

.item-cell {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.item-img-container {
    width: 48px;
    height: 48px;
    border-radius: 0.75rem;
    background: #F3F4F6;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-thumb-placeholder {
    color: #9CA3AF;
}

.item-info {
    display: flex;
    flex-direction: column;
}

.item-name {
    font-weight: 600;
    color: #111827;
    font-size: 0.9375rem;
}

.item-desc {
    font-size: 0.75rem;
    color: #6B7280;
}

.reporter-info {
    display: flex;
    flex-direction: column;
}

.reporter-name {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.reporter-nim {
    font-size: 0.75rem;
    color: #9CA3AF;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-success { background: #ECFDF5; color: #059669; }
.status-danger { background: #FEF2F2; color: #DC2626; }
.status-warning { background: #FFFBEB; color: #D97706; }
.status-neutral { background: #F9FAFB; color: #6B7280; }

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    padding: 0.5rem;
    border-radius: 0.5rem;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-icon:hover {
    background: #F3F4F6;
}

/* Custom Pagination styles matching layout */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: white;
}
.pagination-info { font-size: 0.875rem; color: #6B7280; }
.pagination-buttons { display: flex; align-items: center; gap: 0.5rem; }
.pagination-btn {
    padding: 0.5rem 0.75rem;
    border: 1px solid #E5E7EB;
    background: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.pagination-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination-btn.active { background: #4F46E5; color: white; border-color: #4F46E5; }
.page-numbers { display: flex; gap: 0.25rem; }

/* Helpers */
.text-success { color: #10B981; }
.text-danger { color: #EF4444; }
.text-warning { color: #F59E0B; }
.icon-sm { width: 18px; height: 18px; }
.icon-xs { width: 16px; height: 16px; }
[x-cloak] { display: none !important; }
</style>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('lostFoundManagement', (initialItems) => ({
            allItems: initialItems,
            search: '',
            statusFilter: 'all',
            currentPage: 1,
            perPage: 5,
            
            init() {
                this.$watch('search', () => this.currentPage = 1);
                this.$watch('statusFilter', () => this.currentPage = 1);
                // Re-init lucide icons after Alpine updates DOM
                this.$watch('currentPage', () => {
                    this.$nextTick(() => lucide.createIcons());
                });
            },
            
            get filteredItems() {
                let q = this.search.toLowerCase();
                let s = this.statusFilter;
                
                return this.allItems.filter(item => {
                    let matchesSearch = q === '' || 
                        item.name.toLowerCase().includes(q) || 
                        item.description.toLowerCase().includes(q) || 
                        item.reporter_name.toLowerCase().includes(q) || 
                        item.location.toLowerCase().includes(q);
                        
                    let matchesStatus = s === 'all' || item.status === s;
                    
                    return matchesSearch && matchesStatus;
                });
            },
            
            get totalItemsCount() { return this.filteredItems.length; },
            get totalPages() { return Math.ceil(this.totalItemsCount / this.perPage) || 1; },
            get startIndex() { return (this.currentPage - 1) * this.perPage; },
            get endIndex() { return this.startIndex + this.perPage; },
            
            get paginatedItems() {
                return this.filteredItems.slice(this.startIndex, this.endIndex);
            },
            
            prevPage() { if (this.currentPage > 1) this.currentPage--; },
            nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
            goToPage(p) { this.currentPage = p; }
        }));
    });
</script>
@endpush
@endsection
