@extends('layouts.admin')

@section('title', 'Announcement')
@section('page_title', 'Announcement')

@section('content')
<div class="user-management" x-data="announcementApp({{ $announcements->toJson() }})" x-init="lucide.createIcons()" x-cloak>
    <!-- Filter Bar -->
    <div class="filter-container">
        <div class="filter-bar">
            <div class="search-wrapper" style="flex: 1;">
                <i data-lucide="search"></i>
                <input type="text" x-model="search" placeholder="Cari pengumuman..." id="searchInput">
            </div>
            
            <div class="filter-actions-group">
                <button type="button" @click="openAddModal()" class="btn-primary">
                    <i data-lucide="plus-circle"></i>
                    <span>Buat Pengumuman</span>
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
                <tr x-init="$nextTick(() => lucide.createIcons())">
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
                            <!-- View Detail -->
                            <button class="action-btn" title="Lihat Detail" @click="openDetailModal(ann)">
                                <i data-lucide="eye"></i>
                            </button>

                            <!-- Edit Announcement -->
                            <button class="action-btn text-primary" title="Edit Pengumuman" @click="openEditModal(ann)">
                                <i data-lucide="edit-3"></i>
                            </button>
                            
                            <!-- Delete Announcement -->
                            <button class="action-btn text-danger" title="Hapus Pengumuman" @click="openDeleteModal(ann)">
                                <i data-lucide="trash-2"></i>
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

    <!-- Modal Form (Add/Edit) -->
    <div x-show="showFormModal" class="modal-overlay" style="display: none;" x-transition>
        <div class="modal-container" @click.away="showFormModal = false" style="max-width: 600px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #E2E8F0;">
                <h3 style="font-weight: 700; color: #1E293B;" x-text="isEdit ? 'Edit Pengumuman' : 'Buat Pengumuman Baru'"></h3>
                <button @click="showFormModal = false" style="background: none; border: none; cursor: pointer; color: #64748B;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            
            <form :action="isEdit ? `/admin/announcement/${selectedId}` : '{{ route('admin.announcement.store') }}'" method="POST" enctype="multipart/form-data" style="padding: 20px;">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #475569; margin-bottom: 8px;">Judul Pengumuman</label>
                    <input type="text" name="title" x-model="formData.title" required placeholder="Contoh: Maintenance Sistem" style="width: 100%; padding: 10px 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 14px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #475569; margin-bottom: 8px;">Isi Konten</label>
                    <textarea name="content" x-model="formData.content" required rows="5" placeholder="Tuliskan detail pengumuman di sini..." style="width: 100%; padding: 10px 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #475569; margin-bottom: 8px;">File Lampiran (Opsional)</label>
                    
                    <div class="file-upload-wrapper">
                        <input type="file" name="file" x-ref="fileInput" @change="handleFileSelect($event)" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                        
                        <div class="file-upload-box" @click="$refs.fileInput.click()">
                            <div class="file-upload-icon">
                                <i data-lucide="upload-cloud"></i>
                            </div>
                            <div class="file-upload-text">
                                <p class="main-text" x-text="selectedFileName || 'Klik untuk pilih file'"></p>
                                <p class="sub-text">PDF, JPG, PNG (Max 5MB)</p>
                            </div>
                            <button type="button" class="file-browse-btn">Browse</button>
                        </div>

                        <template x-if="isEdit && formData.file_url && !selectedFileName">
                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #EFF6FF; border-radius: 8px;">
                                <i data-lucide="file-check" style="width: 14px; color: #3B82F6;"></i>
                                <span style="font-size: 12px; color: #3B82F6;">File saat ini tersimpan: </span>
                                <a :href="formData.file_url" target="_blank" style="font-size: 12px; color: #1D4ED8; font-weight: 600; text-decoration: underline;">Lihat File</a>
                            </div>
                        </template>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 12px; border-top: 1px solid #E2E8F0;">
                    <button type="button" @click="showFormModal = false" style="padding: 10px 20px; background: #F1F5F9; color: #475569; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Batal</button>
                    <button type="submit" style="padding: 10px 20px; background: #3B82F6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" x-text="isEdit ? 'Perbarui' : 'Simpan'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div x-show="showDetailModal" class="modal-overlay" style="display: none;" x-transition>
        <div class="modal-container" @click.away="showDetailModal = false" style="max-width: 600px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #E2E8F0;">
                <h3 style="font-weight: 700; color: #1E293B;">Detail Pengumuman</h3>
                <button @click="showDetailModal = false" style="background: none; border: none; cursor: pointer; color: #64748B;">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div style="padding: 24px;">
                <div style="margin-bottom: 20px;">
                    <span style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em;">Judul</span>
                    <h2 style="font-size: 20px; font-weight: 700; color: #1E293B; margin-top: 4px;" x-text="formData.title"></h2>
                </div>
                <div style="margin-bottom: 20px;">
                    <span style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em;">Konten</span>
                    <p style="font-size: 15px; color: #475569; line-height: 1.6; margin-top: 4px; white-space: pre-line;" x-text="formData.content"></p>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em;">Dibuat Oleh</span>
                        <p style="font-weight: 600; color: #1E293B; margin-top: 4px;" x-text="formData.creator_name"></p>
                    </div>
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em;">Tanggal</span>
                        <p style="font-weight: 600; color: #1E293B; margin-top: 4px;" x-text="formData.date"></p>
                    </div>
                </div>
                <template x-if="formData.file_url">
                    <div style="padding: 16px; background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: #EFF6FF; color: #3B82F6; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="file-text"></i>
                            </div>
                            <div>
                                <p style="font-size: 14px; font-weight: 600; color: #1E293B;">Lampiran Tersedia</p>
                                <p style="font-size: 12px; color: #64748B;">Klik untuk melihat atau mengunduh</p>
                            </div>
                        </div>
                        <a :href="formData.file_url" target="_blank" class="btn-primary" style="height: 36px; padding: 0 16px; font-size: 13px;">Lihat File</a>
                    </div>
                </template>
            </div>
            <div class="modal-footer" style="padding: 20px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end;">
                <button @click="showDetailModal = false" style="padding: 10px 24px; background: #fff; color: #475569; border: 1px solid #E2E8F0; border-radius: 8px; font-weight: 600; cursor: pointer;">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" class="modal-overlay" style="display: none;" x-transition>
        <div class="modal-container" @click.away="showDeleteModal = false" style="max-width: 400px; text-align: center; padding: 32px;">
            <div style="width: 64px; height: 64px; background: #FEE2E2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i data-lucide="trash-2" style="width: 32px; height: 32px;"></i>
            </div>
            <h3 style="font-weight: 700; color: #1E293B; margin-bottom: 8px;">Hapus Pengumuman?</h3>
            <p style="color: #64748B; font-size: 14px; margin-bottom: 24px;">Tindakan ini tidak dapat dibatalkan. Pengumuman <strong x-text="selectedTitle"></strong> akan dihapus permanen.</p>
            
            <form :action="`/admin/announcement/${selectedId}`" method="POST">
                @csrf
                @method('DELETE')
                <div style="display: flex; gap: 12px;">
                    <button type="button" @click="showDeleteModal = false" style="flex: 1; padding: 10px; background: #F1F5F9; color: #475569; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Batal</button>
                    <button type="submit" style="flex: 1; padding: 10px; background: #EF4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    @if(session('success'))
    <div class="toast-wrapper" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000); $nextTick(() => lucide.createIcons())" x-cloak x-transition:enter="toast-enter" x-transition:leave="toast-leave">
        <div class="toast-box toast-success">
            <div class="toast-icon">
                <i data-lucide="check-circle"></i>
            </div>
            <div class="toast-content">
                <p>{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
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
    }

    .toast-success { border-left-color: #10B981; }
    .toast-error { border-left-color: #EF4444; }

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

    .toast-enter { animation: toastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .toast-leave { animation: toastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) reverse; }

    /* Existing Styles */
    :root {
        --primary: #3B82F6;
        --secondary: #64748B;
        --success: #10B981;
        --warning: #F59E0B;
        --danger: #EF4444;
    }

    .btn-primary { 
        background: #3B82F6; color: #fff; padding: 0 24px; border-radius: 99px; 
        font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; 
        display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;
        font-size: 14px; height: 46px;
    }
    .btn-primary:hover { background: #2563EB; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); }

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

    .action-buttons { display: flex; gap: 8px; }
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
        transform: translateY(-1px);
    }
    .action-btn.text-danger:hover {
        background: #FEE2E2;
        border-color: #FECACA;
        color: #B91C1C;
        transform: translateY(-1px);
    }
    .action-btn i { width: 18px; height: 18px; }
    .text-primary { color: #64748B; } /* Base color matches reference image */
    .text-danger { color: #EF4444; }

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
    
    [x-cloak] { display: none !important; }

    /* Custom File Upload Styles */
    .file-upload-wrapper {
        width: 100%;
    }
    .file-upload-box {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px 16px;
        background: #F8FAFC;
        border: 2px dashed #E2E8F0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .file-upload-box:hover {
        background: #F1F5F9;
        border-color: #3B82F6;
    }
    .file-upload-icon {
        width: 40px;
        height: 40px;
        background: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3B82F6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .file-upload-text {
        flex: 1;
    }
    .file-upload-text .main-text {
        font-size: 14px;
        font-weight: 600;
        color: #1E293B;
        margin: 0;
    }
    .file-upload-text .sub-text {
        font-size: 12px;
        color: #64748B;
        margin: 0;
    }
    .file-browse-btn {
        padding: 6px 14px;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('announcementApp', (initialData) => ({
            allAnnouncements: initialData,
            search: '',
            
            // Modal States
            showFormModal: false,
            showDeleteModal: false,
            showDetailModal: false,
            isEdit: false,
            selectedId: null,
            selectedTitle: '',
            selectedFileName: '',
            
            formData: {
                title: '',
                content: '',
                file_url: null,
                creator_name: '',
                date: ''
            },
            
            currentPage: 1,
            perPage: 5,
            
            init() {
                this.$watch('search', () => this.currentPage = 1);
            },
            
            openAddModal() {
                this.isEdit = false;
                this.selectedId = null;
                this.formData = { title: '', content: '', file_url: null, creator_name: '', date: '' };
                this.selectedFileName = '';
                this.showFormModal = true;
            },

            openDetailModal(ann) {
                this.formData = {
                    title: ann.title,
                    content: ann.content,
                    file_url: ann.file_url,
                    creator_name: ann.creator_name,
                    date: ann.date
                };
                this.showDetailModal = true;
                this.$nextTick(() => lucide.createIcons());
            },
            
            openEditModal(ann) {
                this.isEdit = true;
                this.selectedId = ann.id;
                this.formData = {
                    title: ann.title,
                    content: ann.content,
                    file_url: ann.file_url
                };
                this.selectedFileName = '';
                this.showFormModal = true;
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.selectedFileName = file.name;
                }
            },
            
            openDeleteModal(ann) {
                this.selectedId = ann.id;
                this.selectedTitle = ann.title;
                this.showDeleteModal = true;
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
