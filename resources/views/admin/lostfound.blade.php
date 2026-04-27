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
            <!-- Status Dropdown -->
            <select x-model="statusFilter" class="form-select status-filter">
                <option value="all">Semua Status</option>
                <option value="lost">Hilang</option>
                <option value="found">Ditemukan</option>
                <option value="claimed">Diklaim</option>
            </select>
            <button class="btn btn-primary" @click="openAddModal()">
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
                                    <span class="item-desc" x-text="item.description.length > 30 ? item.description.substring(0, 30) + '...' : item.description"></span>
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
                                <button title="View" class="action-btn text-neutral" @click="openDetailModal(item)">
                                    <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
                                </button>
                                <button x-show="item.status !== 'claimed'" title="Mark as Resolved" class="action-btn text-neutral" @click="openResolveModal(item)">
                                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                                </button>
                                <button title="Delete" class="action-btn text-danger" @click="openDeleteModal(item)">
                                    <i data-lucide="trash-2" style="width: 18px; height: 18px;"></i>
                                </button>
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

    <!-- Modals Section -->
    
    <!-- Add Item Modal -->
    <div class="modal-overlay" x-show="showAddModal" x-cloak x-transition>
        <div class="modal-container glass-panel" style="max-width: 850px;" @click.outside="showAddModal = false">
            <div class="modal-header">
                <h3>Posting Barang Baru</h3>
                <button @click="showAddModal = false" class="close-btn"><i data-lucide="x"></i></button>
            </div>
            <form @submit.prevent="addItem()">
                <div class="modal-body">
                    <div class="horizontal-modal-layout" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px;">
                        <!-- Left Side: Image Upload & Preview -->
                        <div class="modal-upload-section">
                            <label style="font-size: 14px; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Foto Barang</label>
                            
                            <!-- Clickable Preview Area -->
                            <div class="image-preview-placeholder" 
                                 @click="$refs.fileInput.click()"
                                 style="width: 100%; aspect-ratio: 1/1; background: #F8FAFC; border: 2px dashed #E2E8F0; border-radius: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 16px; position: relative; cursor: pointer; transition: all 0.2s;"
                                 onmouseover="this.style.borderColor='#4F46E5'; this.style.background='#F1F5F9'"
                                 onmouseout="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC'">
                                
                                <template x-if="!newItem.photo">
                                    <div style="text-align: center; color: #94A3B8;">
                                        <i data-lucide="image-plus" style="width: 48px; height: 48px; margin-bottom: 8px;"></i>
                                        <p style="font-size: 14px; font-weight: 500;">Klik untuk Pilih Foto</p>
                                    </div>
                                </template>
                                <template x-if="newItem.photo">
                                    <div style="width: 100%; height: 100%; position: relative;">
                                        <img :src="URL.createObjectURL(newItem.photo)" style="width: 100%; height: 100%; object-fit: cover;">
                                        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                            <span style="color: white; background: rgba(0,0,0,0.5); padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;">Ganti Foto</span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Hidden Actual Input -->
                            <input type="file" x-ref="fileInput" @change="handleFileUpload($event)" accept="image/*" style="display: none;">
                            
                            <!-- Custom Styled Button -->
                            <button type="button" @click="$refs.fileInput.click()" class="btn btn-secondary" style="width: 100%; justify-content: center; border-style: solid; border-width: 1.5px;">
                                <i data-lucide="upload-cloud" class="icon-xs"></i>
                                <span x-text="newItem.photo ? 'Ganti File' : 'Pilih File'"></span>
                            </button>
                            
                            <p class="text-xs text-neutral-500 mt-3" style="text-align: center; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <i data-lucide="info" style="width: 12px; height: 12px;"></i>
                                Format: JPG, PNG. Max 5MB.
                            </p>
                        </div>
                        
                        <!-- Right Side: Form Fields -->
                        <div class="modal-form-section">
                            <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group col-span-2">
                                    <label>Nama Barang</label>
                                    <input type="text" x-model="newItem.item_name" required placeholder="Contoh: Kunci Motor Vario">
                                </div>
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" x-model="newItem.location" required placeholder="Contoh: Kantin Pusat">
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select x-model="newItem.status" required>
                                        <option value="lost">Hilang</option>
                                        <option value="found">Ditemukan</option>
                                    </select>
                                </div>
                                <div class="form-group col-span-2">
                                    <label>Deskripsi</label>
                                    <textarea x-model="newItem.description" required placeholder="Ciri-ciri barang, dsb..." rows="4" style="resize: none;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #F8FAFC; padding: 20px 30px; border-top: 1px solid #F1F5F9; display: flex; justify-content: flex-end; gap: 12px; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px;">
                    <button type="button" @click="showAddModal = false" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span x-show="!loading">Posting Sekarang</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail Item Modal -->
    <div class="modal-overlay" x-show="showDetailModal" x-cloak x-transition>
        <div class="modal-container glass-panel" style="max-width: 850px;" @click.outside="showDetailModal = false">
            <div class="modal-header">
                <h3>Detail Barang</h3>
                <button @click="showDetailModal = false" class="close-btn"><i data-lucide="x"></i></button>
            </div>
            <template x-if="selectedItem">
                <div class="modal-body">
                    <div class="item-detail-layout" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 24px;">
                        <div class="item-image-large">
                            <template x-if="selectedItem.photo">
                                <img :src="selectedItem.photo" :alt="selectedItem.name" style="width: 100%; border-radius: 16px; object-fit: cover; aspect-ratio: 1/1;">
                            </template>
                            <template x-if="!selectedItem.photo">
                                <div style="width: 100%; aspect-ratio: 1/1; background: #F3F4F6; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #9CA3AF;">
                                    <i data-lucide="package" style="width: 48px; height: 48px;"></i>
                                </div>
                            </template>
                        </div>
                        <div class="item-details">
                            <div style="margin-bottom: 20px;">
                                <span :class="`status-badge ${selectedItem.status_class}`" x-text="selectedItem.status_label" style="margin-bottom: 8px; display: inline-block;"></span>
                                <h2 x-text="selectedItem.name" style="font-size: 24px; font-weight: 700; color: #111827;"></h2>
                                <p style="color: #6B7280; font-size: 14px;"><i data-lucide="calendar" style="width: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i> <span x-text="selectedItem.date"></span></p>
                            </div>
                            
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase;">Lokasi</label>
                                <p x-text="selectedItem.location" style="font-weight: 600; color: #374151;"></p>
                            </div>
                            
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase;">Pelapor</label>
                                <p style="font-weight: 600; color: #374151;"><span x-text="selectedItem.reporter_name"></span> (<span x-text="selectedItem.reporter_nim"></span>)</p>
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase;">Deskripsi</label>
                                <p x-text="selectedItem.description" style="color: #4B5563; line-height: 1.6;"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <div class="modal-footer">
                <button @click="showDetailModal = false" class="btn btn-secondary">Tutup</button>
                <template x-if="selectedItem && selectedItem.status !== 'claimed'">
                    <button @click="openResolveModal(selectedItem)" class="btn btn-primary">Tandai Selesai</button>
                </template>
            </div>
        </div>
    </div>

    <!-- Resolve Confirmation Modal -->
    <div class="modal-overlay" x-show="showResolveModal" x-cloak x-transition>
        <div class="modal-container glass-panel" style="max-width: 400px; text-align: center;" @click.outside="showResolveModal = false">
            <div class="modal-body" style="padding: 40px 30px;">
                <div style="width: 64px; height: 64px; background: #ECFDF5; color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i data-lucide="check-circle" style="width: 32px; height: 32px;"></i>
                </div>
                <h3 style="font-size: 18px; margin-bottom: 12px;">Selesaikan Postingan?</h3>
                <p style="color: #64748B; font-size: 14px; margin-bottom: 30px;">Apakah barang ini sudah ditemukan/dikembalikan? Status akan berubah menjadi "Diklaim".</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <button @click="showResolveModal = false" class="btn btn-secondary" style="justify-content: center;">Batal</button>
                    <button @click="confirmResolve()" class="btn btn-primary" style="background: #10B981; justify-content: center; border-color: #10B981;" :disabled="loading">
                        <span x-show="!loading">Ya, Selesai</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" x-show="showDeleteModal" x-cloak x-transition>
        <div class="modal-container glass-panel" style="max-width: 400px; text-align: center;" @click.outside="showDeleteModal = false">
            <div class="modal-body" style="padding: 40px 30px;">
                <div style="width: 64px; height: 64px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i data-lucide="trash-2" style="width: 32px; height: 32px;"></i>
                </div>
                <h3 style="font-size: 18px; margin-bottom: 12px;">Hapus Data Barang?</h3>
                <p style="color: #64748B; font-size: 14px; margin-bottom: 30px;">Tindakan ini tidak dapat dibatalkan. Data akan dihapus secara permanen dari sistem.</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <button @click="showDeleteModal = false" class="btn btn-secondary" style="justify-content: center;">Batal</button>
                    <button @click="confirmDelete()" class="btn btn-primary" style="background: #EF4444; justify-content: center; border-color: #EF4444;" :disabled="loading">
                        <span x-show="!loading">Ya, Hapus</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-wrapper" x-show="toast.show" x-cloak x-transition:enter="toast-enter" x-transition:leave="toast-leave">
        <div :class="`toast-box toast-${toast.type}`">
            <div class="toast-icon">
                <i :data-lucide="toast.type === 'success' ? 'check-circle' : 'alert-circle'"></i>
            </div>
            <div class="toast-content">
                <p x-text="toast.message"></p>
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
    justify-content: center;
    gap: 0.5rem;
    border: 1px solid #4F46E5;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: #4338CA;
    transform: translateY(-1px);
}

.btn-secondary {
    background: white;
    color: #4B5563;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border: 1px solid #E5E7EB;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: #F9FAFB;
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

.action-btn {
    width: 36px;
    height: 36px;
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
    background: #F1F5F9;
    border-color: #CBD5E1;
    transform: translateY(-1px);
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

/* Modals */
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
    display: flex;
    flex-direction: column;
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
    flex-shrink: 0;
}

.modal-header h3 { font-size: 18px; font-weight: 700; color: #1E293B; }
.modal-body { padding: 30px; max-height: 60vh; overflow-y: auto; flex-grow: 1; }
.modal-footer {
    padding: 24px 30px;
    background: #F8FAFC;
    border-top: 1px solid #F1F5F9;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    flex-shrink: 0;
}

.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
.col-span-2 { grid-column: span 2; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 14px; font-weight: 600; color: #475569; }
.form-group input, .form-group select, .form-group textarea { 
    padding: 12px 16px; border: 1.5px solid #E2E8F0; border-radius: 12px; font-size: 14px; 
    transition: all 0.2s; background: #fff; color: #1E293B;
}
.form-group input::placeholder, .form-group textarea::placeholder { color: #94A3B8; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { 
    border-color: #4F46E5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); outline: none; 
}

.close-btn { 
    width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    border: none; background: #F8FAFC; color: #64748B; cursor: pointer; transition: all 0.2s;
}
.close-btn:hover { background: #FEF2F2; color: #EF4444; }

/* Helpers */
.text-success { color: #10B981; }
.text-danger { color: #EF4444; }
.text-warning { color: #F59E0B; }
.text-neutral { color: #64748B; }
.icon-sm { width: 18px; height: 18px; }
.icon-xs { width: 16px; height: 16px; }
[x-cloak] { display: none !important; }

/* Toast Notifications */
.toast-wrapper {
    position: fixed;
    top: 30px;
    right: 30px;
    z-index: 10000;
    pointer-events: none;
}

.toast-box {
    display: flex;
    align-items: center;
    gap: 16px;
    background: white;
    padding: 16px 24px;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    min-width: 320px;
    border-left: 6px solid #10B981;
    pointer-events: auto;
    animation: toastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-success { border-color: #10B981; }
.toast-error { border-color: #EF4444; }

.toast-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
}

.toast-success .toast-icon { background: #ECFDF5; color: #10B981; }
.toast-error .toast-icon { background: #FEF2F2; color: #EF4444; }

.toast-content p {
    font-size: 14px;
    font-weight: 600;
    color: #1E293B;
    margin: 0;
}

@keyframes toastSlideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.toast-enter { animation: toastSlideIn 0.4s ease-out; }
.toast-leave { animation: toastSlideIn 0.4s ease-in reverse; }
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
            loading: false,
            
            // Modal States
            showAddModal: false,
            showDetailModal: false,
            showResolveModal: false,
            showDeleteModal: false,
            
            // Current Selection
            selectedItem: null,
            newItem: {
                item_name: '',
                description: '',
                location: '',
                status: 'lost',
                photo: null
            },
            
            // Toast
            toast: {
                show: false,
                message: '',
                type: 'success'
            },
            
            init() {
                this.$watch('search', () => this.currentPage = 1);
                this.$watch('statusFilter', () => this.currentPage = 1);
                
                this.$watch('paginatedItems', () => {
                    this.$nextTick(() => lucide.createIcons());
                });

                this.$nextTick(() => lucide.createIcons());
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
            goToPage(p) { this.currentPage = p; },
            
            // Modal Handlers
            openAddModal() {
                this.newItem = { item_name: '', description: '', location: '', status: 'lost', photo: null };
                this.showAddModal = true;
            },
            
            openDetailModal(item) {
                this.selectedItem = item;
                this.showDetailModal = true;
            },
            
            openResolveModal(item) {
                this.selectedItem = item;
                this.showResolveModal = true;
            },
            
            openDeleteModal(item) {
                this.selectedItem = item;
                this.showDeleteModal = true;
            },
            
            handleFileUpload(event) {
                this.newItem.photo = event.target.files[0];
            },
            
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => {
                    this.toast.show = false;
                    this.$nextTick(() => lucide.createIcons());
                }, 3000);
            },
            
            async addItem() {
                this.loading = true;
                const formData = new FormData();
                formData.append('item_name', this.newItem.item_name);
                formData.append('description', this.newItem.description);
                formData.append('location', this.newItem.location);
                formData.append('status', this.newItem.status);
                if (this.newItem.photo) {
                    formData.append('photo', this.newItem.photo);
                }
                
                try {
                    const response = await fetch('{{ route('admin.lostfound.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });
                    
                    const result = await response.json();
                    if (response.ok) {
                        this.allItems.unshift(result.data);
                        this.showAddModal = false;
                        this.showToast(result.message);
                    } else {
                        this.showToast(result.message || 'Gagal memposting barang', 'error');
                    }
                } catch (error) {
                    this.showToast('Terjadi kesalahan sistem', 'error');
                } finally {
                    this.loading = false;
                }
            },
            
            async confirmResolve() {
                this.loading = true;
                try {
                    const response = await fetch(`{{ url('/admin/lostfound') }}/${this.selectedItem.id}/resolve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    const result = await response.json();
                    if (response.ok) {
                        const index = this.allItems.findIndex(i => i.id === this.selectedItem.id);
                        if (index !== -1) {
                            this.allItems[index].status = 'claimed';
                            this.allItems[index].status_label = 'Diklaim';
                            this.allItems[index].status_class = 'status-warning';
                        }
                        this.showResolveModal = false;
                        this.showDetailModal = false;
                        this.showToast(result.message);
                    }
                } catch (error) {
                    this.showToast('Gagal memperbarui status', 'error');
                } finally {
                    this.loading = false;
                }
            },
            
            async confirmDelete() {
                this.loading = true;
                try {
                    const response = await fetch(`{{ url('/admin/lostfound') }}/${this.selectedItem.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const result = await response.json();
                    if (response.ok) {
                        this.allItems = this.allItems.filter(i => i.id !== this.selectedItem.id);
                        this.showDeleteModal = false;
                        this.showToast(result.message);
                    }
                } catch (error) {
                    this.showToast('Gagal menghapus data', 'error');
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection
