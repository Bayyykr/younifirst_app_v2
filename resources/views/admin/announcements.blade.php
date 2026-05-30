@extends('layouts.admin')

@section('title', 'Announcement')
@section('page_title', 'Announcement')

@section('content')
    <div class="user-management" x-data="announcementApp({{ $announcements->toJson() }})" x-init="lucide.createIcons()"
        x-cloak>
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

        <div class="table-info" x-text="`${filteredAnnouncements.length} dari ${allAnnouncements.length} pengumuman`"
            style="margin-bottom: 16px;"></div>

        <!-- Announcement Table -->
        <div class="table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Konten</th>
                        <th>Status</th>
                        <th>Dibuat oleh</th>
                        <th>Tanggal</th>
                        <th>Jadwal Publish</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="ann in paginatedAnnouncements" :key="ann.id">
                        <tr x-init="$nextTick(() => lucide.createIcons())">
                            <td>
                                <div style="font-weight: 600; width: 200px; color: var(--text-main);" x-text="ann.title">
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate-2"
                                    style="max-width: 400px; color: var(--text-muted); font-size: 0.875rem;"
                                    x-text="ann.content"></div>
                            </td>
                            <td>
                                <template x-if="ann.status === 'publish' && !isScheduled(ann)">
                                    <span
                                        style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; background: #ECFDF5; color: #10B981; border-radius: 99px; font-size: 12px; font-weight: 600;">
                                        <span
                                            style="width: 6px; height: 6px; background: #10B981; border-radius: 50%;"></span>
                                        Published
                                    </span>
                                </template>
                                <template x-if="ann.status === 'publish' && isScheduled(ann)">
                                    <span
                                        style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; background: #FEF3C7; color: #D97706; border-radius: 99px; font-size: 12px; font-weight: 600;">
                                        <span
                                            style="width: 6px; height: 6px; background: #D97706; border-radius: 50%;"></span>
                                        Scheduled
                                    </span>
                                </template>
                                <template x-if="ann.status === 'draft'">
                                    <span
                                        style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; background: #F1F5F9; color: #64748B; border-radius: 99px; font-size: 12px; font-weight: 600;">
                                        <span
                                            style="width: 6px; height: 6px; background: #64748B; border-radius: 50%;"></span>
                                        Draft
                                    </span>
                                </template>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--text-main);" x-text="ann.creator_name"></span>
                            </td>
                            <td>
                                <span style="color: var(--text-muted); font-size: 0.875rem;" x-text="ann.date"></span>
                            </td>
                            <td>
                                <span style="color: var(--text-muted); font-size: 0.875rem;" x-text="ann.publish_at_text"></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <!-- View Detail -->
                                    <button class="action-btn" title="Lihat Detail" @click="openDetailModal(ann)">
                                        <i data-lucide="eye"></i>
                                    </button>

                                    <!-- Edit Announcement -->
                                    <button class="action-btn text-primary" title="Edit Pengumuman"
                                        @click="openEditModal(ann)">
                                        <i data-lucide="edit-3"></i>
                                    </button>

                                    <!-- Delete Announcement -->
                                    <button class="action-btn text-danger" title="Hapus Pengumuman"
                                        @click="openDeleteModal(ann)">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredAnnouncements.length === 0" x-cloak>
                        <td colspan="7" style="text-align: center; padding: 32px; color: #64748B;">Tidak ada pengumuman
                            ditemukan</td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination Controls -->
            <div class="pagination-container" x-show="totalPages > 1" x-cloak>
                <div class="pagination-info">
                    Menampilkan <span x-text="startIndex + 1"></span> - <span
                        x-text="Math.min(endIndex, filteredAnnouncements.length)"></span> dari <span
                        x-text="filteredAnnouncements.length"></span> pengumuman
                </div>
                <div class="pagination-buttons">
                    <button class="pagination-btn" @click="prevPage()" :disabled="currentPage === 1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6" />
                        </svg> Prev
                    </button>
                    <div class="page-numbers">
                        <template x-for="page in totalPages" :key="page">
                            <button class="pagination-btn" :class="{ 'active': currentPage === page }"
                                @click="goToPage(page)" x-text="page"></button>
                        </template>
                    </div>
                    <button class="pagination-btn" @click="nextPage()" :disabled="currentPage === totalPages">
                        Next <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Form (Add/Edit) -->
        <div x-show="showFormModal" class="modal-overlay" style="display: none;" x-transition>
            <div class="modal-container announcement-form-modal" @click.away="showFormModal = false">
                <div class="modal-header announcement-modal-header">
                    <h3 x-text="isEdit ? 'Edit Pengumuman' : 'Buat Pengumuman Baru'"></h3>
                    <button type="button" @click="showFormModal = false" class="modal-close-btn" aria-label="Tutup modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>

                <form :action="isEdit ? `/admin/announcement/${selectedId}` : '{{ route('admin.announcement.store') }}'"
                    method="POST" enctype="multipart/form-data" class="announcement-form" @submit="validateFileBeforeSubmit($event)">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="announcement-form-group form-title-group">
                        <label>Judul Pengumuman</label>
                        <input type="text" name="title" x-model="formData.title" required
                            placeholder="Contoh: Maintenance Sistem">
                    </div>

                    <div class="announcement-form-group form-content-group">
                        <label>Isi Konten</label>
                        <textarea name="content" x-model="formData.content" required rows="9"
                            placeholder="Tuliskan detail pengumuman di sini..."></textarea>
                    </div>

                    <div class="announcement-form-group form-status-group">
                        <label>Status</label>
                        <select name="status" x-model="formData.status" required>
                            <option value="publish">Publish</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <div class="announcement-form-group form-schedule-group">
                        <label>Jadwal Publish (Opsional)</label>
                        <input type="datetime-local" name="publish_at" x-model="formData.publish_at">
                        <small style="display: block; margin-top: 6px; color: var(--text-muted); font-size: 12px; line-height: 1.5;">
                            Kosongkan untuk publish sekarang. Jika diisi waktu mendatang, pengumuman akan tampil dan push notif dikirim saat jadwal tersebut.
                        </small>
                    </div>

                    <div class="announcement-form-group file-group form-file-group">
                        <label>File Lampiran (Opsional)</label>

                        <div class="file-upload-wrapper">
                            <input type="file" name="file" x-ref="fileInput" @change="handleFileSelect($event)"
                                accept=".pdf,.jpg,.jpeg,.png" style="display: none;">

                            <div class="file-upload-box" :class="{ 'has-error': fileError }" @click="$refs.fileInput.click()">
                                <div class="file-upload-icon">
                                    <i data-lucide="upload-cloud"></i>
                                </div>
                                <div class="file-upload-text">
                                    <p class="main-text" x-text="selectedFileName || 'Klik untuk pilih file'"></p>
                                    <p class="sub-text">PDF, JPG, PNG (Max 5MB)</p>
                                </div>
                                <button type="button" class="file-browse-btn">Browse</button>
                            </div>

                            <template x-if="fileError">
                                <p class="file-error-message" x-text="fileError"></p>
                            </template>

                            @error('file')
                                <p class="file-error-message">{{ $message }}</p>
                            @enderror

                            <template x-if="isEdit && formData.file_url && !selectedFileName">
                                <div class="current-file-info">
                                    <i data-lucide="file-check"></i>
                                    <span>File saat ini tersimpan: </span>
                                    <a :href="formData.file_url" target="_blank">Lihat File</a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="announcement-form-actions">
                        <button type="button" @click="showFormModal = false" class="btn-modal-secondary">Batal</button>
                        <button type="submit" class="btn-modal-primary" :disabled="!!fileError"
                            x-text="isEdit ? 'Perbarui' : 'Simpan'"></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Detail Modal -->
        <div x-show="showDetailModal" class="modal-overlay" style="display: none;" x-transition>
            <div class="modal-container" @click.away="showDetailModal = false" style="max-width: 600px;">
                <div class="modal-header"
                    style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-white);">
                    <h3 style="font-weight: 700; color: var(--text-main);">Detail Pengumuman</h3>
                    <button @click="showDetailModal = false"
                        style="background: none; border: none; cursor: pointer; color: var(--text-muted);">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div style="padding: 24px;">
                    <div style="margin-bottom: 20px;">
                        <span
                            style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Judul</span>
                        <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin-top: 4px;"
                            x-text="formData.title"></h2>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <span
                            style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Konten</span>
                        <p style="font-size: 15px; color: var(--text-main); line-height: 1.6; margin-top: 4px; white-space: pre-line;"
                            x-text="formData.content"></p>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <span
                            style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em;">Status</span>
                        <div style="margin-top: 4px;">
                            <template x-if="formData.status === 'publish' && !formData.is_scheduled">
                                <span
                                    style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; background: #ECFDF5; color: #10B981; border-radius: 99px; font-size: 12px; font-weight: 600;">
                                    <span style="width: 6px; height: 6px; background: #10B981; border-radius: 50%;"></span>
                                    Published
                                </span>
                            </template>
                            <template x-if="formData.status === 'publish' && formData.is_scheduled">
                                <span
                                    style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; background: #FEF3C7; color: #D97706; border-radius: 99px; font-size: 12px; font-weight: 600;">
                                    <span style="width: 6px; height: 6px; background: #D97706; border-radius: 50%;"></span>
                                    Scheduled
                                </span>
                            </template>
                            <template x-if="formData.status === 'draft'">
                                <span
                                    style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; background: #F1F5F9; color: #64748B; border-radius: 99px; font-size: 12px; font-weight: 600;">
                                    <span style="width: 6px; height: 6px; background: #64748B; border-radius: 50%;"></span>
                                    Draft
                                </span>
                            </template>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                        <div>
                            <span
                                style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Dibuat
                                Oleh</span>
                            <p style="font-weight: 600; color: var(--text-main); margin-top: 4px;"
                                x-text="formData.creator_name"></p>
                        </div>
                        <div>
                            <span
                                style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Tanggal</span>
                            <p style="font-weight: 600; color: var(--text-main); margin-top: 4px;" x-text="formData.date">
                            </p>
                        </div>
                        <div>
                            <span
                                style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Jadwal Publish</span>
                            <p style="font-weight: 600; color: var(--text-main); margin-top: 4px;" x-text="formData.publish_at_text"></p>
                        </div>
                    </div>
                    <template x-if="formData.file_url">
                        <div
                            style="padding: 16px; background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div
                                    style="width: 40px; height: 40px; background: #EFF6FF; color: #3B82F6; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="file-text"></i>
                                </div>
                                <div>
                                    <p style="font-size: 14px; font-weight: 600; color: #1E293B;">Lampiran Tersedia</p>
                                    <p style="font-size: 12px; color: #64748B;">Klik untuk melihat atau mengunduh</p>
                                </div>
                            </div>
                            <a :href="formData.file_url" target="_blank" class="btn-primary"
                                style="height: 36px; padding: 0 16px; font-size: 13px;">Lihat File</a>
                        </div>
                    </template>
                </div>
                <div class="modal-footer"
                    style="padding: 20px; background: var(--bg-main); border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
                    <button @click="showDetailModal = false"
                        style="padding: 10px 24px; background: var(--bg-white); color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 8px; font-weight: 600; cursor: pointer;">Tutup</button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" class="modal-overlay" style="display: none;" x-transition>
            <div class="modal-container" @click.away="showDeleteModal = false"
                style="max-width: 400px; text-align: center; padding: 32px;">
                <div
                    style="width: 64px; height: 64px; background: #FEE2E2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i data-lucide="trash-2" style="width: 32px; height: 32px;"></i>
                </div>
                <h3 style="font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Hapus Pengumuman?</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Tindakan ini tidak dapat
                    dibatalkan. Pengumuman <strong x-text="selectedTitle"></strong> akan dihapus permanen.</p>

                <form :action="`/admin/announcement/${selectedId}`" method="POST">
                    @csrf
                    @method('DELETE')
                    <div style="display: flex; gap: 12px;">
                        <button type="button" @click="showDeleteModal = false"
                            style="flex: 1; padding: 10px; background: #F1F5F9; color: #475569; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Batal</button>
                        <button type="submit"
                            style="flex: 1; padding: 10px; background: #EF4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Ya,
                            Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast Notification -->
        @if($errors->any())
            <div class="toast-wrapper" x-data="{ show: true }" x-show="show"
                x-init="setTimeout(() => show = false, 5000); $nextTick(() => lucide.createIcons())" x-cloak
                x-transition:enter="toast-enter" x-transition:leave="toast-leave">
                <div class="toast-box toast-error">
                    <div class="toast-icon">
                        <i data-lucide="alert-circle"></i>
                    </div>
                    <div class="toast-content">
                        <p>{{ $errors->first() }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="toast-wrapper" x-data="{ show: true }" x-show="show"
                x-init="setTimeout(() => show = false, 3000); $nextTick(() => lucide.createIcons())" x-cloak
                x-transition:enter="toast-enter" x-transition:leave="toast-leave">
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
            background: var(--bg-white);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .announcement-form-modal {
            max-width: 920px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .announcement-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-white);
        }

        .announcement-modal-header h3 {
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }

        .modal-close-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            background: var(--bg-main);
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close-btn:hover {
            background: var(--bg-hover);
            color: var(--danger);
        }

        .modal-close-btn svg {
            width: 18px;
            height: 18px;
        }

        .announcement-form {
            padding: 20px;
            background: var(--bg-white);
            overflow-x: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 20px;
            align-items: start;
        }

        .announcement-form *,
        .announcement-form *::before,
        .announcement-form *::after {
            box-sizing: border-box;
        }

        .announcement-form-group {
            margin-bottom: 0;
            min-width: 0;
        }

        .form-title-group {
            grid-column: 1;
            grid-row: 1;
        }

        .form-content-group {
            grid-column: 1;
            grid-row: 2 / span 3;
        }

        .form-status-group {
            grid-column: 2;
            grid-row: 1;
        }

        .form-schedule-group {
            grid-column: 2;
            grid-row: 2;
        }

        .form-file-group {
            grid-column: 2;
            grid-row: 3;
        }

        .announcement-form-actions {
            grid-column: 1 / -1;
        }

        .announcement-form-group.file-group {
            margin-bottom: 4px;
        }

        .announcement-form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .announcement-form-group input,
        .announcement-form-group textarea,
        .announcement-form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            background: var(--bg-white);
            color: var(--text-main);
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .announcement-form-group textarea {
            resize: vertical;
        }

        .announcement-form-group input::placeholder,
        .announcement-form-group textarea::placeholder {
            color: var(--text-muted);
            opacity: 0.75;
        }

        .announcement-form-group input:focus,
        .announcement-form-group textarea:focus,
        .announcement-form-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            outline: none;
        }

        .current-file-info {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--primary-light, rgba(59, 130, 246, 0.1));
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            color: var(--primary);
        }

        .current-file-info svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .current-file-info span,
        .current-file-info a {
            font-size: 12px;
            color: var(--primary);
        }

        .current-file-info a {
            font-weight: 600;
            text-decoration: underline;
        }

        .announcement-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border-color);
        }

        .btn-modal-secondary,
        .btn-modal-primary {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-modal-secondary {
            background: var(--bg-main);
            color: var(--text-muted);
            border: 1px solid var(--border-color);
        }

        .btn-modal-secondary:hover {
            background: var(--bg-hover);
            color: var(--text-main);
        }

        .btn-modal-primary {
            background: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }

        .btn-modal-primary:hover {
            background: #2563EB;
            transform: translateY(-1px);
        }

        .btn-modal-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
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
            background: var(--bg-white);
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            min-width: 320px;
            border-left: 6px solid #10B981;
            pointer-events: auto;
            color: var(--text-main);
        }

        .toast-success {
            border-left-color: #10B981;
        }

        .toast-error {
            border-left-color: #EF4444;
        }

        .toast-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }

        .toast-success .toast-icon {
            background: #ECFDF5;
            color: #10B981;
        }

        .toast-error .toast-icon {
            background: #FEF2F2;
            color: #EF4444;
        }

        .toast-content p {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
        }

        @keyframes toastSlideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-enter {
            animation: toastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .toast-leave {
            animation: toastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) reverse;
        }

        /* Existing Styles */
        :root {
            --primary: #3B82F6;
            --secondary: #64748B;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
        }

        .btn-primary {
            background: #3B82F6;
            color: #fff;
            padding: 0 24px;
            border-radius: 99px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            font-size: 14px;
            height: 46px;
        }

        .btn-primary:hover {
            background: #2563EB;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

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
            color: var(--text-muted);
        }

        .search-wrapper input {
            width: 100%;
            padding: 0 16px 0 42px;
            border: 1px solid var(--border-color);
            border-radius: 99px;
            font-size: 14px;
            background: var(--bg-white);
            color: var(--text-main);
            transition: all 0.2s;
            height: 46px;
        }

        .search-wrapper input:focus {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: var(--bg-hover);
            border-color: #BFDBFE;
            color: var(--primary);
            transform: translateY(-1px);
        }

        .admin-table thead th {
            padding: 16px 20px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border-color);
            background: var(--bg-main);
        }

        .admin-table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table tbody tr:hover {
            background-color: var(--bg-hover);
        }

        .action-btn.text-danger:hover {
            background: #FEE2E2;
            border-color: #FECACA;
            color: #B91C1C;
            transform: translateY(-1px);
        }

        .action-btn i {
            width: 18px;
            height: 18px;
        }

        .text-primary {
            color: #64748B;
        }

        /* Base color matches reference image */
        .text-danger {
            color: #EF4444;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            background: var(--bg-white);
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
            border: 1px solid var(--border-color);
            background: var(--bg-white);
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
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

        [x-cloak] {
            display: none !important;
        }

        /* Custom File Upload Styles */
        .file-upload-wrapper {
            align-items: flex-start;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .file-upload-box {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 16px;
            background: var(--bg-main);
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .file-upload-box:hover {
            background: var(--bg-hover);
            border-color: #3B82F6;
        }

        .file-upload-box.has-error {
            background: #FEF2F2;
            border-color: #EF4444;
        }

        .file-error-message {
            display: block;
            width: 100%;
            margin: 8px 0 0;
            color: #EF4444;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.5;
        }

        .file-upload-icon {
            width: 40px;
            height: 40px;
            background: var(--bg-white);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3B82F6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .file-upload-text {
            flex: 1;
        }

        .file-upload-text .main-text {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
        }

        .file-upload-text .sub-text {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        .file-browse-btn {
            padding: 6px 14px;
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .user-management {
                width: 100%;
                min-width: 0;
                overflow-x: hidden;
            }

            .filter-container,
            .filter-bar,
            .search-wrapper,
            .filter-actions-group,
            .filter-actions-group .btn-primary {
                width: 100%;
            }

            .filter-bar {
                align-items: stretch;
                flex-direction: column;
                gap: 12px;
                margin-bottom: 20px;
            }

            .search-wrapper {
                max-width: none;
            }

            .filter-actions-group .btn-primary {
                justify-content: center;
            }

            .table-card {
                overflow: visible;
                background: transparent;
                border: 0;
                box-shadow: none;
            }

            .admin-table,
            .admin-table tbody,
            .admin-table tr,
            .admin-table td {
                display: block;
                width: 100%;
            }

            .admin-table thead {
                display: none;
            }

            .admin-table tbody {
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            .admin-table tbody tr {
                background: var(--bg-white);
                border: 1px solid var(--border-color);
                border-radius: 16px;
                padding: 14px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            }

            .admin-table tbody td {
                border-bottom: 0;
                padding: 9px 0;
                font-size: 13px;
            }

            .admin-table tbody td:first-child > div {
                width: 100% !important;
                max-width: 100%;
                white-space: normal;
                word-break: break-word;
            }

            .admin-table tbody td:nth-child(2) .text-truncate-2 {
                max-width: 100% !important;
                -webkit-line-clamp: 4;
                word-break: break-word;
            }

            .admin-table tbody td:not(:first-child) {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                border-top: 1px solid var(--border-color);
                text-align: right;
            }

            .admin-table tbody td:not(:first-child)::before {
                content: '';
                color: var(--text-muted);
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                text-align: left;
                flex-shrink: 0;
            }

            .admin-table tbody td:nth-child(2)::before { content: 'Konten'; }
            .admin-table tbody td:nth-child(3)::before { content: 'Status'; }
            .admin-table tbody td:nth-child(4)::before { content: 'Dibuat oleh'; }
            .admin-table tbody td:nth-child(5)::before { content: 'Tanggal'; }
            .admin-table tbody td:nth-child(6)::before { content: 'Jadwal Publish'; }
            .admin-table tbody td:nth-child(7)::before { content: 'Aksi'; }

            .action-buttons {
                justify-content: flex-end;
                flex-wrap: wrap;
            }

            .pagination-container {
                align-items: stretch;
                flex-direction: column;
                gap: 12px;
                padding: 18px 0 0;
                background: transparent;
                border-top: 0;
            }

            .pagination-info {
                text-align: center;
            }

            .pagination-buttons,
            .page-numbers {
                flex-wrap: wrap;
                justify-content: center;
            }

            .modal-overlay {
                align-items: flex-end;
                padding: 12px;
            }

            .modal-container,
            .announcement-form-modal {
                width: 100%;
                max-width: 100%;
                max-height: calc(100vh - 24px);
                border-radius: 20px;
            }

            .announcement-modal-header,
            .modal-header {
                padding: 18px;
            }

            .announcement-form {
                grid-template-columns: 1fr;
                gap: 16px;
                padding: 18px;
            }

            .form-title-group,
            .form-content-group,
            .form-status-group,
            .form-schedule-group,
            .form-file-group,
            .announcement-form-actions {
                grid-column: 1;
                grid-row: auto;
            }

            .announcement-form-group textarea {
                min-height: 180px;
                rows: auto;
            }

            .file-upload-box {
                align-items: flex-start;
                flex-direction: column;
            }

            .file-browse-btn {
                width: 100%;
            }

            .announcement-form-actions {
                align-items: stretch;
                flex-direction: column-reverse;
            }

            .announcement-form-actions button,
            .btn-modal-secondary,
            .btn-modal-primary {
                width: 100%;
            }

            .modal-footer {
                align-items: stretch !important;
                flex-direction: column !important;
                padding: 18px !important;
            }

            .modal-footer button,
            .modal-footer .btn-primary {
                width: 100%;
                justify-content: center;
            }

            .modal-container > div[style*='padding: 24px'] {
                padding: 18px !important;
            }

            .modal-container div[style*='grid-template-columns: 1fr 1fr'] {
                grid-template-columns: 1fr !important;
            }

            .modal-container div[style*='justify-content: space-between'] {
                align-items: stretch !important;
                flex-direction: column !important;
                gap: 12px !important;
            }

            .modal-container div[style*='display: flex; gap: 12px'] {
                flex-direction: column !important;
            }

            .toast-wrapper {
                left: 16px;
                right: 16px;
                top: calc(var(--topbar-height) + 12px);
            }

            .toast-box {
                min-width: 0;
                width: 100%;
            }
        }

        @media (max-width: 380px) {
            .pagination-btn {
                padding-left: 10px;
                padding-right: 10px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('announcementApp', (initialData) => ({
                allAnnouncements: initialData,
                search: '',
                nowTs: Date.now(),

                // Modal States
                showFormModal: false,
                showDeleteModal: false,
                showDetailModal: false,
                isEdit: false,
                selectedId: null,
                selectedTitle: '',
                selectedFileName: '',
                fileError: '',
                maxFileSize: 5 * 1024 * 1024,

                formData: {
                    title: '',
                    content: '',
                    status: 'publish',
                    publish_at: '',
                    publish_at_text: '-',
                    is_scheduled: false,
                    file_url: null,
                    creator_name: '',
                    date: ''
                },

                currentPage: 1,
                perPage: 5,

                init() {
                    this.$watch('search', () => this.currentPage = 1);
                    setInterval(() => this.nowTs = Date.now(), 30000);
                },

                openAddModal() {
                    this.isEdit = false;
                    this.selectedId = null;
                    this.formData = { title: '', content: '', status: 'publish', publish_at: '', publish_at_text: '-', is_scheduled: false, file_url: null, creator_name: '', date: '' };
                    this.selectedFileName = '';
                    this.fileError = '';
                    this.showFormModal = true;
                    this.$nextTick(() => {
                        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                    });
                },

                openDetailModal(ann) {
                    this.formData = {
                        title: ann.title,
                        content: ann.content,
                        status: ann.status,
                        publish_at: ann.publish_at || '',
                        publish_at_text: ann.publish_at_text || '-',
                        is_scheduled: ann.is_scheduled,
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
                        status: ann.status,
                        publish_at: ann.publish_at || '',
                        publish_at_text: ann.publish_at_text || '-',
                        is_scheduled: ann.is_scheduled,
                        file_url: ann.file_url
                    };
                    this.selectedFileName = '';
                    this.fileError = '';
                    this.showFormModal = true;
                    this.$nextTick(() => {
                        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                    });
                },

                isScheduled(ann) {
                    return ann.status === 'publish' && ann.publish_at && new Date(ann.publish_at).getTime() > this.nowTs;
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    this.fileError = '';
                    this.selectedFileName = '';

                    if (!file) return;

                    if (file.size > this.maxFileSize) {
                        this.fileError = 'Ukuran file lampiran maksimal 5MB. Silakan pilih file yang lebih kecil.';
                        event.target.value = '';
                        return;
                    }

                    this.selectedFileName = file.name;
                },

                validateFileBeforeSubmit(event) {
                    const file = this.$refs.fileInput?.files?.[0];

                    if (this.fileError) {
                        event.preventDefault();
                        return;
                    }

                    if (file && file.size > this.maxFileSize) {
                        event.preventDefault();
                        this.fileError = 'Ukuran file lampiran maksimal 5MB. File tidak dapat diunggah.';
                        this.$refs.fileInput.value = '';
                        this.selectedFileName = '';
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
