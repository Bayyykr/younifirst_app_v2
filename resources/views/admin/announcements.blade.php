@extends('layouts.admin')

@section('title', 'Announcement')
@section('page_title', 'Announcement')

@section('content')
<div class="user-management" x-data="announcementApp({{ $announcements->toJson() }})">
    <!-- Filter Bar -->
    <div class="filter-container">
        <div class="filter-bar">
            <div class="search-wrapper" style="flex: 1;">
                <i data-lucide="search"></i>
                <input type="text" x-model="search" placeholder="Cari pengumuman..." id="searchInput">
            </div>
            
            <div class="filter-actions">
                <button type="button" class="btn-primary" style="display: flex; align-items: center; gap: 8px; background: #3B82F6; color: white; padding: 10px 20px; border-radius: 8px; border: none; font-weight: 500; cursor: pointer; transition: background 0.2s;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus-circle"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg> 
                    Buat Pengumuman
                </button>
            </div>
        </div>
    </div>

    <div class="table-info" x-text="`${filteredAnnouncements.length} dari ${allAnnouncements.length} pengumuman`" style="margin-bottom: 16px;"></div>

    <!-- Announcement Table -->
    <div class="table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Konten</th>
                    <th>Dibuat oleh</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="ann in paginatedAnnouncements" :key="ann.id">
                <tr>
                    <td>
                        <div style="font-weight: 600; width: 200px; color: #1E293B;" x-text="ann.title"></div>
                    </td>
                    <td>
                        <div class="text-truncate-2" style="max-width: 400px; color: #475569; font-size: 0.875rem;" x-text="ann.content"></div>
                    </td>
                    <td>
                        <span style="font-weight: 600; color: #1E293B;" x-text="ann.creator_name"></span>
                    </td>
                    <td>
                        <span style="color: #475569; font-size: 0.875rem;" x-text="ann.date"></span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <!-- Edit Announcement -->
                            <button class="action-btn" title="Edit Pengumuman">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            
                            <!-- Delete Announcement -->
                            <button class="action-btn text-danger" title="Hapus Pengumuman">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                </template>
                
                <tr x-show="filteredAnnouncements.length === 0" x-cloak>
                    <td colspan="5" style="text-align: center; padding: 32px; color: #64748B;">Tidak ada pengumuman ditemukan</td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div class="pagination-container" x-show="totalPages > 1" x-cloak>
            <div class="pagination-info">
                Menampilkan <span x-text="startIndex + 1"></span> - <span x-text="Math.min(endIndex, filteredAnnouncements.length)"></span> dari <span x-text="filteredAnnouncements.length"></span> pengumuman
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
    /* Inline styles for specific overrides */
    .btn-primary:hover {
        background-color: #2563EB !important;
    }
    
    /* Pagination Styles Reused */
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
        Alpine.data('announcementApp', (initialData) => ({
            allAnnouncements: initialData,
            search: '',
            
            currentPage: 1,
            perPage: 5, // Tampil 5 baris aja per page
            
            init() {
                this.$watch('search', () => this.currentPage = 1);
            },
            
            get filteredAnnouncements() {
                let q = this.search.toLowerCase();
                return this.allAnnouncements.filter(a => {
                    return q === '' || 
                        a.title.toLowerCase().includes(q) || 
                        a.content.toLowerCase().includes(q) ||
                        a.creator_name.toLowerCase().includes(q);
                });
            },

            get totalPages() { return Math.ceil(this.filteredAnnouncements.length / this.perPage) || 1; },
            get startIndex() { return (this.currentPage - 1) * this.perPage; },
            get endIndex() { return this.startIndex + this.perPage; },
            
            get paginatedAnnouncements() {
                return this.filteredAnnouncements.slice(this.startIndex, this.endIndex);
            },
            
            prevPage() { if (this.currentPage > 1) this.currentPage--; },
            nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
            goToPage(page) { this.currentPage = page; }
        }));
    });
</script>
@endpush
