@extends('layouts.admin')

@section('title', 'Event Management')
@section('page_title', 'Event Management')

@section('content')
    <div class="event-management" x-data="eventManagement({
                                initialEvents: {{ json_encode($allEvents) }},
                                categories: {{ json_encode($categories) }}
                             })" x-cloak>

        <div x-show="viewMode === 'dashboard'" x-transition:enter="transition-fade"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">Total Events</span>
                    <div class="stat-value text-blue">{{ $stats['total'] }}</div>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Approved</span>
                    <div class="stat-value text-green">{{ $stats['approved'] }}</div>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Upload Request</span>
                    <div class="stat-value text-orange">{{ $stats['pending'] }}</div>
                    <span class="stat-sublabel text-orange">User awaiting approval</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Rejected</span>
                    <div class="stat-value text-red">{{ $stats['rejected'] }}</div>
                </div>
            </div>

            @if($pendingEvents->count() > 0)
                <div class="pending-section">
                    <div class="section-header">
                        <h3>Menunggu Persetujuan ({{ $stats['pending'] }})</h3>
                        <a href="#" @click.prevent="viewMode = 'requests'" class="view-all">Lihat Semua</a>
                    </div>

                    <div class="pending-list">
                        @foreach($pendingEvents->take(3) as $event)
                            <div class="pending-card">
                                <div class="pending-card-left">
                                    <div class="pending-poster">
                                        @if($event->poster)
                                            <img src="{{ $event->poster_url }}" alt="{{ $event->title }}">
                                        @else
                                            <div class="poster-placeholder">
                                                <i data-lucide="image"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="pending-card-mid">
                                    <div class="info-badges">
                                        <span class="badge badge-category">{{ $event->category->name_category }}</span>
                                        <span class="badge badge-status-pending">Pending</span>
                                    </div>
                                    <h4 class="event-title">{{ $event->title }}</h4>
                                    <div class="event-meta">
                                        <span>{{ $event->start_date->format('d F Y') }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $event->start_date->format('H:i') }} - {{ $event->end_date->format('H:i') }}
                                            WIB</span>
                                        <span>&bull;</span>
                                        <span>{{ $event->location }}</span>
                                    </div>
                                    <div class="event-submitter">
                                        Submitted by : <span class="submitter-name">{{ $event->creator->name ?? 'User' }}</span>
                                        <span class="submitter-time"> &bull; {{ $event->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <div class="pending-card-right">
                                    <button class="btn btn-action-outline"
                                        @click="openViewModal(allEvents.find(e => e.id === '{{ $event->event_id }}'))">
                                        <i data-lucide="eye" style="width: 16px;"></i>
                                    </button>
                                    <button class="btn btn-action-success"
                                        @click="openRespondModal('{{ $event->event_id }}', '{{ addslashes($event->title) }}', 'approve')">
                                        <i data-lucide="check" style="width: 16px;"></i>
                                    </button>
                                    <button class="btn btn-action-danger"
                                        @click="openRespondModal('{{ $event->event_id }}', '{{ addslashes($event->title) }}', 'reject')">
                                        <i data-lucide="x" style="width: 16px;"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="main-toolbar">
                <div class="toolbar-left">
                    <div class="search-wrapper">
                        <i data-lucide="search" style="width: 18px;"></i>
                        <input type="text" x-model.debounce.300ms="search" placeholder="Cari event...">
                    </div>
                    <div class="dropdown-wrapper" x-data="{ open: false }">
                        <button type="button" class="dropdown-btn" @click="open = !open">
                            <span x-text="getStatusFilterLabel(statusFilter)">Semua Status</span>
                            <i data-lucide="chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" x-show="open" @click.outside="open = false" x-cloak>
                            <div class="dropdown-item" @click="statusFilter = 'all'; open = false">
                                Semua Status
                                <i data-lucide="check" x-show="statusFilter === 'all'"></i>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-item" @click="statusFilter = 'approved'; open = false">
                                Approved
                                <i data-lucide="check" x-show="statusFilter === 'approved'"></i>
                            </div>
                            <div class="dropdown-item" @click="statusFilter = 'pending'; open = false">
                                Pending
                                <i data-lucide="check" x-show="statusFilter === 'pending'"></i>
                            </div>
                            <div class="dropdown-item" @click="statusFilter = 'rejected'; open = false">
                                Rejected
                                <i data-lucide="check" x-show="statusFilter === 'rejected'"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary-blue" @click="openAddModal()">
                    <i data-lucide="plus-circle" style="width: 18px;"></i> Tambah Event
                </button>
            </div>

            <div class="category-filter-bar">
                <span class="filter-label"><i data-lucide="filter" style="width: 16px;"></i> Filter Kategori</span>
                <div class="filter-pills">
                    <button class="pill-btn" :class="{ 'active': categoryFilter === 'all' }"
                        @click="categoryFilter = 'all'">Semua</button>
                    <template x-for="cat in categories" :key="cat.category_id">
                        <button class="pill-btn" :class="{ 'active': categoryFilter == cat.category_id }"
                            @click="categoryFilter = cat.category_id" x-text="cat.name_category"></button>
                    </template>
                </div>
            </div>

            <div class="table-container">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Kategori</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Dibuat oleh</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="eventItem in paginatedEvents" :key="eventItem.id">
                            <tr class="table-row-hover">
                                <td>
                                    <div class="cell-event">
                                        <div class="cell-thumb">
                                            <template x-if="eventItem.poster">
                                                <img :src="eventItem.poster" :alt="eventItem.title">
                                            </template>
                                            <template x-if="!eventItem.poster">
                                                <div class="thumb-placeholder-sm"><i data-lucide="image"></i></div>
                                            </template>
                                        </div>
                                        <div class="cell-titles">
                                            <div class="main-title" x-text="eventItem.title"></div>
                                            <div class="sub-title" x-text="eventItem.location"></div>
                                        </div>
                                    </div>
                                </td>
                                <td x-text="eventItem.category_name"></td>
                                <td>
                                    <div class="cell-datetime">
                                        <div class="date" x-text="eventItem.start_date"></div>
                                        <div class="time" x-text="eventItem.start_time"></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-datetime">
                                        <div class="date" x-text="eventItem.end_date"></div>
                                        <div class="time" x-text="eventItem.end_time"></div>
                                    </div>
                                </td>
                                <td x-text="eventItem.creator_name"></td>
                                <td>
                                    <span :class="getStatusBadgeClass(eventItem.status)"
                                        x-text="getStatusLabel(eventItem.status)"></span>
                                </td>
                                <td>
                                    <div class="cell-actions">
                                        <button class="action-icon-btn" @click="openViewModal(eventItem)" title="Detail">
                                            <i data-lucide="eye"
                                                style="width: 18px; height: 18px; pointer-events: none;"></i>
                                        </button>
                                        <button class="action-icon-btn text-primary" @click="openEditModal(eventItem)"
                                            title="Edit">
                                            <i data-lucide="edit-3"
                                                style="width: 18px; height: 18px; pointer-events: none;"></i>
                                        </button>
                                        <button class="action-icon-btn text-danger" @click="deleteEvent(eventItem.id)"
                                            title="Hapus">
                                            <i data-lucide="trash-2"
                                                style="width: 18px; height: 18px; pointer-events: none;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div class="pagination-footer" x-show="totalPages > 1">
                <div class="pagination-info">
                    Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to
                    <span x-text="Math.min(currentPage * perPage, filteredEvents.length)"></span> of
                    <span x-text="filteredEvents.length"></span> entries
                </div>
                <div class="pagination-btns">
                    <button @click="currentPage--" :disabled="currentPage === 1" class="page-nav-btn">
                        <i data-lucide="chevron-left" style="width: 16px;"></i> Prev
                    </button>

                    <template x-for="p in totalPages" :key="p">
                        <button @click="currentPage = p" :class="{ 'active': currentPage === p }" class="page-num-btn"
                            x-text="p"></button>
                    </template>

                    <button @click="currentPage++" :disabled="currentPage === totalPages" class="page-nav-btn">
                        Next <i data-lucide="chevron-right" style="width: 16px;"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- View 2: All Pending Requests Dedicated View (PREMIUM REDESIGN) -->
        <div x-show="viewMode === 'requests'" class="requests-view-wrapper" x-transition:enter="transition-fade"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

            <div class="requests-view-header">
                <div class="header-content-left">
                    <button @click="viewMode = 'dashboard'" class="compact-back-btn">
                        <i data-lucide="arrow-left" style="width: 18px;"></i>
                        Dashboard
                    </button>
                    <div class="header-title-group">
                        <h2>Permohonan Event</h2>
                        <div class="header-stats-badges">
                            <span class="badge-count-orange">{{ $stats['pending'] }} Requests Pending</span>
                            <span class="badge-count-gray">Audit Mode</span>
                        </div>
                    </div>
                </div>
                <div class="header-content-right">
                    <div class="search-mini">
                        <i data-lucide="search" style="width: 16px;"></i>
                        <input type="text" placeholder="Quick search pending...">
                    </div>
                </div>
            </div>

            <div class="requests-grid">
                @forelse($pendingEvents as $event)
                    <div class="req-card">
                        <div class="req-accent-bar"></div>
                        <div class="req-card-body">
                            <div class="req-card-main">
                                <div class="req-poster-wrap">
                                    @if($event->poster)
                                        <img src="{{ $event->poster_url }}" alt="{{ $event->title }}">
                                    @else
                                        <div class="req-poster-placeholder">
                                            <i data-lucide="image" style="width: 32px;"></i>
                                        </div>
                                    @endif
                                    <div class="req-category-float">{{ $event->category->name_category }}</div>
                                </div>

                                <div class="req-card-content">
                                    <div class="req-card-top">
                                        <h3 class="req-title">{{ $event->title }}</h3>
                                        <div class="req-submission-info">
                                            <div class="submitter-pill">
                                                <div class="submitter-avatar">{{ substr($event->creator->name ?? 'U', 0, 1) }}
                                                </div>
                                                <span>{{ $event->creator->name ?? 'User' }}</span>
                                            </div>
                                            <span class="req-time-ago">{{ $event->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="req-details-grid">
                                        <div class="req-detail-item">
                                            <div class="detail-icon"><i data-lucide="calendar"></i></div>
                                            <div class="detail-text">
                                                <label>Tanggal</label><span>{{ $event->start_date->format('d F Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="req-detail-item">
                                            <div class="detail-icon"><i data-lucide="clock"></i></div>
                                            <div class="detail-text">
                                                <label>Waktu</label><span>{{ $event->start_date->format('H:i') }} WIB</span>
                                            </div>
                                        </div>
                                        <div class="req-detail-item">
                                            <div class="detail-icon"><i data-lucide="map-pin"></i></div>
                                            <div class="detail-text"><label>Lokasi</label><span>{{ $event->location }}</span>
                                            </div>
                                        </div>
                                        <div class="req-detail-item">
                                            <div class="detail-icon"><i data-lucide="bar-chart-2"></i></div>
                                            <div class="detail-text">
                                                <label>Kategori</label><span>{{ $event->category->name_category }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="req-description-preview">{{ Str::limit($event->description, 200) }}</div>
                                </div>
                            </div>

                            <div class="req-card-actions">
                                <button class="req-btn-secondary"
                                    @click="openViewModal(allEvents.find(e => e.id === '{{ $event->event_id }}'))">
                                    <i data-lucide="eye" style="width: 16px;"></i> Full Details
                                </button>
                                <div class="req-decision-btns">
                                    <button class="req-btn-approve"
                                        @click="openRespondModal('{{ $event->event_id }}', '{{ addslashes($event->title) }}', 'approve')">
                                        <i data-lucide="check-circle" style="width: 18px;"></i> Setujui
                                    </button>
                                    <button class="req-btn-reject"
                                        @click="openRespondModal('{{ $event->event_id }}', '{{ addslashes($event->title) }}', 'reject')">
                                        <i data-lucide="x" style="width: 18px;"></i> Tolak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="req-empty-state">
                        <div class="empty-icon-box"><i data-lucide="shield-check" style="width: 48px;"></i></div>
                        <h3>All Done!</h3>
                        <p>No more pending permohonan to review.</p>
                        <button @click="viewMode = 'dashboard'" class="btn btn-primary-blue" style="margin-top: 1rem;">Back to
                            Dashboard</button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Global Delete Form -->
        <form id="delete-event-form" x-ref="deleteForm" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <!-- Toast Notifications -->
        <div class="toast-container" x-show="toast.show" x-cloak x-transition:enter="toast-enter"
            x-transition:leave="toast-leave">
            <div :class="`toast toast-${toast.type}`">
                <i :data-lucide="toast.icon"></i>
                <span x-text="toast.message"></span>
            </div>
        </div>

        <!-- Custom Delete Confirmation Modal -->
        <div x-show="showDeleteConfirm" class="modal-overlay delete-modal-overlay" x-transition:enter="transition-fade"
            x-transition:leave="transition-fade" style="display: none; z-index: 9999;">

            <div class="modal-container delete-confirm-modal" @click.away="showDeleteConfirm = false"
                x-transition:enter="modal-slide-in">
                <div class="delete-icon-circle">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <h3>Hapus Event?</h3>
                <p>Apakah Anda yakin ingin menghapus event ini? Tindakan ini tidak dapat dibatalkan dan data akan hilang
                    permanen.</p>

                <div class="delete-modal-actions">
                    <button @click="showDeleteConfirm = false"
                        class="btn btn-secondary-gray flex items-center justify-center"><i data-lucide="x"
                            style="width: 18px;"></i> Batal</button>
                    <button @click="confirmDelete()" class="btn btn-danger-solid flex items-center justify-center"><i
                            data-lucide="trash" style="width: 18px;"></i> Ya, Hapus</button>
                </div>
            </div>
        </div>

        <!-- Approve Confirmation Modal -->
        <div x-show="showRespondModal && respondAction === 'approve'" class="modal-overlay"
            x-transition:enter="transition-fade" x-transition:leave="transition-fade" style="display: none; z-index: 9999;">
            <div class="modal-container respond-confirm-modal" @click.away="showRespondModal = false"
                x-transition:enter="modal-slide-in">
                <div class="respond-icon-circle respond-approve-circle">
                    <i data-lucide="check-circle"></i>
                </div>
                <h3>Setujui Event?</h3>
                <p>Event <strong x-text="respondEventTitle"></strong> akan disetujui dan segera dipublikasikan ke seluruh
                    pengguna.</p>
                <div class="delete-modal-actions">
                    <button @click="showRespondModal = false" class="btn btn-secondary-gray" :disabled="respondLoading">
                        <i data-lucide="x" style="width: 16px;"></i> Batal
                    </button>
                    <button @click="confirmRespond()" class="btn btn-approve-solid" :disabled="respondLoading">
                        <template x-if="respondLoading">
                            <span class="loading-spinner"></span>
                        </template>
                        <i x-show="!respondLoading" data-lucide="check" style="width: 16px;"></i>
                        <span x-text="respondLoading ? 'Menyetujui...' : 'Ya, Setujui'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Confirmation Modal -->
        <div x-show="showRespondModal && respondAction === 'reject'" class="modal-overlay"
            x-transition:enter="transition-fade" x-transition:leave="transition-fade" style="display: none; z-index: 9999;">
            <div class="modal-container respond-confirm-modal" @click.away="showRespondModal = false"
                x-transition:enter="modal-slide-in">
                <div class="respond-icon-circle respond-reject-circle">
                    <i data-lucide="x"></i>
                </div>
                <h3>Tolak Event?</h3>
                <p>Event <strong x-text="respondEventTitle"></strong> akan ditolak dan pembuat event akan mendapatkan
                    notifikasi.</p>
                <div class="form-group rejection-reason-group">
                    <label>Alasan Penolakan<span>*</span></label>
                    <textarea x-model="rejectionReason" rows="4" maxlength="1000"
                        placeholder="Jelaskan alasan event ditolak agar pembuat dapat memperbaikinya."></textarea>
                </div>
                <div class="delete-modal-actions">
                    <button @click="showRespondModal = false" class="btn btn-secondary-gray" :disabled="respondLoading">
                        <span>Batal</span>
                    </button>
                    <button @click="confirmRespond()" class="btn btn-danger-solid" :disabled="respondLoading">
                        <template x-if="respondLoading">
                            <span class="loading-spinner"></span>
                        </template>
                        <span x-text="respondLoading ? 'Menolak...' : 'Ya, Tolak'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- NEW: Tambah Event Modal -->
        <div x-show="showAddModal" class="modal-overlay" x-transition:enter="transition-fade"
            x-transition:leave="transition-fade" @keydown.escape.window="showAddModal = false" style="display: none;">

            <div class="modal-container" @click.away="showAddModal = false" x-transition:enter="modal-slide-in">
                <div class="modal-header">
                    <h2 x-text="isViewOnly ? 'Detail Event' : (isEditMode ? 'Edit Event' : 'Tambah Event Baru')"></h2>
                    <button @click="showAddModal = false" class="modal-close-btn">
                        <i data-lucide="x" style="width: 20px;"></i>
                    </button>
                </div>

                <form id="add-event-form"
                    :action="isEditMode ? `/admin/events/${selectedEvent?.id}` : '{{ route('admin.events.store') }}'"
                    method="POST" enctype="multipart/form-data" class="modal-form-content">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditMode ? 'PUT' : 'POST'">
                    <div class="modal-layout">
                        <!-- Left Column: Poster -->
                        <div class="modal-left">
                            <div class="form-group">
                                <label>Poster Event<span>*</span></label>
                                <div class="poster-preview-area"
                                    :class="{ 'has-image': (isEditMode ? (selectedEvent?.poster || newPosterPreview) : newPosterPreview) }">
                                    <template x-if="(newPosterPreview || (isEditMode && selectedEvent?.poster))">
                                        <div style="width: 100%; height: 100%; position: relative;">
                                            <img :src="newPosterPreview || (isEditMode ? selectedEvent?.poster : '')"
                                                alt="Preview" @click="!isViewOnly && $refs.posterInput.click()"
                                                :style="!isViewOnly ? 'cursor: pointer' : ''">
                                            <button type="button" class="change-poster-btn"
                                                @click="$refs.posterInput.click()" x-show="!isViewOnly">
                                                <i data-lucide="edit-3"
                                                    style="width: 14px; display: inline; margin-right: 4px;"></i> Ubah
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!(newPosterPreview || (isEditMode && selectedEvent?.poster))">
                                        <div class="poster-placeholder-text" @click="$refs.posterInput.click()"
                                            style="cursor: pointer;">
                                            <i data-lucide="image"></i>
                                            <p>Klik untuk upload poster</p>
                                            <span style="font-size: 11px; color: #94A3B8;">Rasio 3:4 disarankan</span>
                                        </div>
                                    </template>
                                    <input type="file" name="poster" x-ref="posterInput" hidden accept="image/*"
                                        @change="handlePosterChange($event)">
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 20px;">
                                <label>Kategori Event<span>*</span></label>
                                <div class="category-grid">
                                    <template x-for="cat in categories" :key="cat.category_id">
                                        <label class="category-chip">
                                            <input type="radio" name="category_id" :value="cat.category_id"
                                                :checked="isEditMode && selectedEvent?.category_id == cat.category_id"
                                                :disabled="isViewOnly" :required="!isEditMode">
                                            <div class="chip-content" x-text="cat.name_category"></div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Details -->
                        <div class="modal-right">
                            <div class="form-group">
                                <label>Judul Event<span>*</span></label>
                                <input type="text" name="title" placeholder="Masukkan judul event"
                                    :value="isEditMode ? selectedEvent?.title : ''" :readonly="isViewOnly" required>
                            </div>

                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label><i data-lucide="calendar"
                                            style="width: 14px; display: inline; margin-right: 4px;"></i> Tanggal
                                        Mulai<span>*</span></label>
                                    <div class="input-with-icon">
                                        <input type="date" name="start_date"
                                            :value="isEditMode ? selectedEvent?.start_date_raw : ''" :readonly="isViewOnly"
                                            required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i data-lucide="clock"
                                            style="width: 14px; display: inline; margin-right: 4px;"></i> Waktu
                                        Mulai<span>*</span></label>
                                    <div class="input-with-icon">
                                        <input type="time" name="start_time"
                                            :value="isEditMode ? selectedEvent?.start_time_raw : ''" :readonly="isViewOnly"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label><i data-lucide="calendar"
                                            style="width: 14px; display: inline; margin-right: 4px;"></i> Tanggal
                                        Selesai<span>*</span></label>
                                    <div class="input-with-icon">
                                        <input type="date" name="end_date"
                                            :value="isEditMode ? selectedEvent?.end_date_raw : ''" :readonly="isViewOnly"
                                            required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i data-lucide="clock"
                                            style="width: 14px; display: inline; margin-right: 4px;"></i> Waktu
                                        Selesai<span>*</span></label>
                                    <div class="input-with-icon">
                                        <input type="time" name="end_time"
                                            :value="isEditMode ? selectedEvent?.end_time_raw : ''" :readonly="isViewOnly"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i data-lucide="map-pin"
                                        style="width: 14px; display: inline; margin-right: 4px;"></i> Lokasi
                                    Event<span>*</span></label>
                                <input type="text" name="location" placeholder="Masukkan lokasi event"
                                    :value="isEditMode ? selectedEvent?.location : ''" :readonly="isViewOnly" required>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi<span>*</span></label>
                                <textarea name="description"
                                    placeholder="Jelaskan detail event, kontak, kuota peserta, dll..."
                                    x-text="isEditMode ? selectedEvent?.description : ''" :readonly="isViewOnly"
                                    required></textarea>
                            </div>

                            <div class="form-group" x-show="isViewOnly && selectedEvent?.status === 'rejected' && selectedEvent?.rejection_reason">
                                <label>Alasan Penolakan</label>
                                <textarea x-text="selectedEvent?.rejection_reason" readonly></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-actions" style="padding: 20px 28px;">
                        <button type="button" @click="showAddModal = false" class="btn-cancel"
                            x-text="isViewOnly ? 'Tutup' : 'Batal'"></button>
                        <button type="submit" class="btn-save" x-show="!isViewOnly"
                            x-text="isEditMode ? 'Update Event' : 'Simpan Event'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Root Variables */
        :root {
            --primary: #3B82F6;
            --secondary: #64748B;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --orange: #F97316;
        }



        /* Add your existing styles plus these critical ones */
        [x-cloak] {
            display: none !important;
        }

        /* Base styles for elements without Tailwind */
        .transition-fade-enter-active,
        .transition-fade-leave-active {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .transition-fade-enter-from {
            opacity: 0;
            transform: translateY(1rem);
        }

        .transition-fade-enter-to {
            opacity: 1;
            transform: translateY(0);
        }

        .transition-fade-leave-from {
            opacity: 1;
            transform: translateY(0);
        }

        .transition-fade-leave-to {
            opacity: 0;
            transform: translateY(1rem);
        }

        .modal-slide-in-enter-active {
            transition: all 0.3s ease;
        }

        .modal-slide-in-enter-from {
            opacity: 0;
            transform: scale(0.95);
        }

        .modal-slide-in-enter-to {
            opacity: 1;
            transform: scale(1);
        }

        .event-management {
            max-width: 100%;
            min-width: 0;
            overflow-x: hidden;
        }

        .event-management *,
        .event-management *::before,
        .event-management *::after {
            box-sizing: border-box;
        }

        /* Layout & Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-white);
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .stat-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-sublabel {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Filter Pills */
        .category-filter-bar {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-label {
            font-weight: 700;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .filter-pills {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .pill-btn {
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .pill-btn:hover {
            background: var(--bg-hover);
            border-color: var(--primary);
            color: var(--primary);
        }

        .pill-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Table styles */
        .table-container {
            background: var(--bg-white) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .premium-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            background: transparent !important;
        }

        .event-management .premium-table th:nth-child(1),
        .event-management .premium-table td:nth-child(1) {
            width: 30%;
        }

        .event-management .premium-table th:nth-child(2),
        .event-management .premium-table td:nth-child(2) {
            width: 11%;
        }

        .event-management .premium-table th:nth-child(3),
        .event-management .premium-table td:nth-child(3),
        .event-management .premium-table th:nth-child(4),
        .event-management .premium-table td:nth-child(4) {
            width: 11%;
        }

        .event-management .premium-table th:nth-child(5),
        .event-management .premium-table td:nth-child(5) {
            width: 11%;
        }

        .event-management .premium-table th:nth-child(6),
        .event-management .premium-table td:nth-child(6) {
            width: 11%;
        }

        .event-management .premium-table th:nth-child(7),
        .event-management .premium-table td:nth-child(7) {
            width: 15%;
        }

        .premium-table th {
            text-align: left;
            padding: 16px 20px;
            background: var(--bg-main);
            color: var(--text-muted);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border-color);
        }

        .premium-table td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            background: transparent;
            color: var(--text-main);
            vertical-align: middle;
            min-width: 0;
            overflow: hidden;
        }

        .premium-table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border-color);
        }

        .premium-table tbody tr:hover td {
            background-color: var(--bg-hover) !important;
        }

        .premium-table tbody tr:hover {
            background-color: var(--bg-hover) !important;
        }

        .badge-table {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-approved {
            background: #DCFCE7;
            color: #166534;
        }

        .badge-pending-tbl {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-rejected {
            background: #FEE2E2;
            color: #991B1B;
        }

        /* Add more basic styles as needed */

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
            background: var(--bg-white);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            color: var(--text-main);
            font-weight: 600;
            min-width: 300px;
            border-left: 4px solid var(--border-color);
        }

        .toast-success {
            border-left-color: #10B981;
        }

        .toast-error {
            border-left-color: #EF4444;
        }

        .toast-warning {
            border-left-color: #F59E0B;
        }

        .toast-enter {
            transform: translateY(-20px);
            opacity: 0;
        }

        .toast-leave {
            transform: translateX(20px);
            opacity: 0;
        }

        .pending-section {
            margin: 1.5rem 0;
            background: transparent;
            border: none;
            padding: 0;
        }

        .pending-section .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .pending-section .section-header h3 {
            font-size: 1.25rem;
            color: var(--text-main);
            font-weight: 700;
        }

        .pending-section .view-all {
            color: #3B82F6;
            font-weight: 600;
            text-decoration: none;
        }

        .pending-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .pending-card {
            display: flex;
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 16px;
            gap: 15px;
            align-items: center;
            max-width: 100%;
            min-width: 0;
            overflow: hidden;
        }

        .pending-poster {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .pending-poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .poster-placeholder {
            width: 100%;
            height: 100%;
            background: var(--bg-main);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }

        .pending-card-mid {
            flex: 1 1 auto;
            min-width: 0;
        }

        .info-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .badge-category {
            background: #DBEAFE;
            color: #2563EB;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-status-pending {
            background: #FFEDD5;
            color: #F97316;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .event-title {
            font-size: 1rem;
            color: var(--text-main);
            font-weight: 700;
            margin-bottom: 4px;
            overflow-wrap: anywhere;
        }

        .event-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            color: #64748B;
            font-size: 0.8rem;
            margin-bottom: 4px;
        }

        .event-submitter {
            font-size: 0.8rem;
            color: #64748B;
        }

        .submitter-name {
            font-weight: 700;
            color: var(--text-muted);
        }

        .pending-card-right {
            display: flex;
            flex: 0 0 auto;
            flex-direction: row;
            gap: 8px;
            min-width: 0;
        }

        .pending-card-right .btn {
            background: var(--bg-white);
            padding: 0;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-main);
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
        }

        .btn-action-outline {
            border-color: var(--border-color) !important;
            color: var(--text-muted) !important;
        }

        .btn-action-success {
            border-color: #10B981 !important;
            color: #10B981 !important;
        }

        .btn-action-danger {
            border-color: #EF4444 !important;
            color: #EF4444 !important;
        }

        .btn-action-outline:hover {
            background: var(--bg-hover);
            color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        .btn-action-success:hover {
            background: #F0FDF4;
            color: #059669 !important;
            border-color: #059669 !important;
        }

        .btn-action-danger:hover {
            background: #FEF2F2;
            color: #DC2626 !important;
            border-color: #DC2626 !important;
        }

        /* Action Icon Buttons in Table */
        .cell-actions {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            justify-content: flex-start;
            min-width: max-content;
        }

        .action-icon-btn {
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-main);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-icon-btn:hover {
            background: var(--bg-hover);
            color: var(--text-main);
            border-color: #CBD5E1;
            transform: translateY(-1px);
        }

        .action-icon-btn.text-primary:hover {
            color: var(--primary) !important;
            border-color: var(--primary) !important;
            background: var(--bg-hover) !important;
        }

        .action-icon-btn.text-danger:hover {
            color: var(--danger) !important;
            border-color: var(--danger) !important;
            background: var(--bg-hover) !important;
        }

        /* Pagination */
        .pagination-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .pagination-info {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .pagination-btns {
            display: flex;
            gap: 0.5rem;
        }

        .page-nav-btn,
        .page-num-btn {
            height: 38px;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-nav-btn:hover:not(:disabled),
        .page-num-btn:hover {
            background: var(--bg-hover);
            border-color: var(--primary);
            color: var(--primary);
        }

        .page-num-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .page-nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Match User Management filter style */
        .event-management .main-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            gap: 32px;
            width: 100%;
            flex-wrap: nowrap;
        }

        .event-management .toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }

        .event-management .cell-event,
        .event-management .cell-titles {
            min-width: 0;
            max-width: 100%;
        }

        .event-management .main-title,
        .event-management .sub-title {
            max-width: 100%;
        }

        .event-management .search-wrapper {
            position: relative;
            flex: 1;
            max-width: 600px;
            display: block;
        }

        .event-management .search-wrapper i,
        .event-management .search-wrapper svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #94A3B8;
            pointer-events: none;
        }

        .event-management .search-wrapper input {
            width: 100%;
            height: 46px;
            padding: 0 16px 0 42px;
            border: 1px solid var(--border-color);
            border-radius: 99px;
            font-size: 14px;
            background: var(--bg-white);
            color: var(--text-main);
            transition: all 0.2s;
            outline: none;
        }

        .event-management .search-wrapper input:focus {
            background: var(--bg-white);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .event-management .dropdown-wrapper {
            position: relative;
            flex-shrink: 0;
        }

        .event-management .dropdown-btn {
            background: #fff;
            border: 1px solid #E2E8F0;
            padding: 0 20px;
            border-radius: 99px;
            font-size: 14px;
            font-weight: 500;
            color: #1E293B;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 160px;
            height: 46px;
        }

        .event-management .dropdown-btn:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
        }

        .event-management .dropdown-btn i,
        .event-management .dropdown-btn svg {
            width: 16px;
            height: 16px;
        }

        .event-management .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 200px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 80;
            padding: 6px;
            animation: eventDropdownIn 0.2s ease-out;
        }

        @keyframes eventDropdownIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .event-management .dropdown-item {
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }

        .event-management .dropdown-item:hover {
            background: #F1F5F9;
            color: var(--primary);
        }

        .event-management .dropdown-item i,
        .event-management .dropdown-item svg {
            width: 16px;
            height: 16px;
        }

        .event-management .dropdown-divider {
            height: 1px;
            background: #F1F5F9;
            margin: 4px 6px;
        }

        .event-management .main-toolbar .btn-primary-blue {
            height: 46px;
            padding: 0 24px;
            border-radius: 99px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        @media (max-width: 1180px) {
            .event-management .pending-card {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .event-management .pending-card-right {
                justify-content: flex-end;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .event-management {
                width: 100%;
                min-width: 0;
                overflow-x: hidden;
            }

            .event-management .stats-grid {
                grid-template-columns: 1fr;
                gap: 14px;
                margin-bottom: 20px;
            }

            .event-management .stat-card {
                padding: 18px;
            }

            .event-management .stat-label {
                font-size: 12px;
            }

            .event-management .stat-value {
                font-size: 28px;
            }

            .pending-section .section-header {
                align-items: flex-start;
                gap: 10px;
            }

            .pending-section .section-header h3 {
                font-size: 1rem;
            }

            .event-management .pending-card {
                align-items: stretch;
                flex-direction: column;
                padding: 14px;
            }

            .event-management .pending-card-left,
            .event-management .pending-poster {
                width: 100%;
            }

            .event-management .pending-poster {
                height: 150px;
            }

            .event-management .info-badges,
            .event-management .event-meta {
                flex-wrap: wrap;
            }

            .event-management .event-meta {
                align-items: flex-start;
                line-height: 1.45;
            }

            .event-management .pending-card-right {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                min-width: 0;
                width: 100%;
            }

            .event-management .pending-card-right .btn {
                width: 100%;
            }

            .event-management .main-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                margin-bottom: 20px;
            }

            .event-management .toolbar-left {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .event-management .search-wrapper {
                max-width: none;
                width: 100%;
            }

            .event-management .dropdown-wrapper,
            .event-management .dropdown-btn,
            .event-management .main-toolbar .btn-primary-blue {
                width: 100%;
                min-width: 0;
            }

            .event-management .dropdown-btn {
                justify-content: space-between;
            }

            .event-management .dropdown-menu {
                width: 100%;
                min-width: 0;
            }

            .event-management .category-filter-bar {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
                margin-bottom: 20px;
            }

            .event-management .filter-pills {
                display: flex;
                gap: 8px;
                overflow-x: auto;
                flex-wrap: nowrap;
                width: 100%;
                padding-bottom: 4px;
                scrollbar-width: none;
            }

            .event-management .filter-pills::-webkit-scrollbar {
                display: none;
            }

            .event-management .pill-btn {
                flex: 0 0 auto;
                padding: 8px 14px;
            }

            .event-management .table-container {
                overflow: visible;
                background: transparent !important;
                border: 0 !important;
                border-radius: 0;
            }

            .event-management .premium-table,
            .event-management .premium-table tbody,
            .event-management .premium-table tr,
            .event-management .premium-table td {
                display: block;
                width: 100% !important;
            }

            .event-management .premium-table thead {
                display: none;
            }

            .event-management .premium-table tbody {
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            .event-management .premium-table tbody tr {
                background: var(--bg-white);
                border: 1px solid var(--border-color);
                border-radius: 16px;
                padding: 14px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            }

            .event-management .premium-table tbody td {
                border-bottom: 0;
                padding: 9px 0;
                font-size: 13px;
            }

            .event-management .premium-table tbody td:not(:first-child) {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                border-top: 1px solid var(--border-color);
                text-align: right;
            }

            .event-management .premium-table tbody td:not(:first-child)::before {
                content: '';
                color: var(--text-muted);
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                text-align: left;
                flex-shrink: 0;
            }

            .event-management .premium-table tbody td:nth-child(2)::before { content: 'Kategori'; }
            .event-management .premium-table tbody td:nth-child(3)::before { content: 'Mulai'; }
            .event-management .premium-table tbody td:nth-child(4)::before { content: 'Selesai'; }
            .event-management .premium-table tbody td:nth-child(5)::before { content: 'Dibuat oleh'; }
            .event-management .premium-table tbody td:nth-child(6)::before { content: 'Status'; }
            .event-management .premium-table tbody td:nth-child(7)::before { content: 'Aksi'; }

            .event-management .cell-event {
                align-items: flex-start;
            }

            .event-management .cell-titles {
                min-width: 0;
            }

            .event-management .main-title,
            .event-management .sub-title {
                white-space: normal;
                word-break: break-word;
            }

            .event-management .cell-actions {
                justify-content: flex-end;
            }

            .event-management .pagination-footer {
                align-items: stretch;
                flex-direction: column;
                gap: 12px;
                margin-top: 18px;
            }

            .event-management .pagination-info {
                text-align: center;
            }

            .event-management .pagination-btns {
                flex-wrap: wrap;
                justify-content: center;
            }

            .event-management .requests-view-header,
            .event-management .header-content-left {
                align-items: stretch;
                flex-direction: column;
                gap: 14px;
            }

            .event-management .header-title-group h2 {
                font-size: 1.25rem;
            }

            .event-management .header-stats-badges {
                flex-wrap: wrap;
            }

            .event-management .header-content-right,
            .event-management .search-mini {
                width: 100%;
            }

            .event-management .requests-grid {
                grid-template-columns: 1fr;
            }

            .event-management .req-card-main {
                flex-direction: column;
            }

            .event-management .req-poster-wrap {
                width: 100%;
                height: 170px;
            }

            .event-management .req-card-body {
                padding: 16px;
            }

            .event-management .req-details-grid {
                grid-template-columns: 1fr;
            }

            .event-management .req-card-actions,
            .event-management .req-decision-btns {
                align-items: stretch;
                flex-direction: column;
                width: 100%;
            }

            .event-management .req-btn-secondary,
            .event-management .req-btn-approve,
            .event-management .req-btn-reject {
                justify-content: center;
                width: 100%;
            }

            .event-management .modal-overlay {
                align-items: flex-end;
                padding: 12px;
            }

            .event-management .modal-container {
                width: 100%;
                max-height: calc(100vh - 24px);
                border-radius: 20px;
            }

            .event-management .modal-layout,
            .event-management .form-row-grid {
                grid-template-columns: 1fr;
                flex-direction: column;
            }

            .event-management .modal-left {
                border-right: 0;
                border-bottom: 1px solid var(--border-color);
                padding: 18px;
            }

            .event-management .modal-right {
                padding: 18px;
            }

            .event-management .poster-preview-area {
                max-width: 220px;
                margin: 0 auto;
            }

            .event-management .modal-footer-actions,
            .event-management .delete-modal-actions {
                display: flex;
                flex-direction: column-reverse;
                gap: 10px;
                padding: 18px !important;
            }

            .event-management .modal-footer-actions button,
            .event-management .delete-modal-actions button {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 380px) {
            .event-management .pending-card-right {
                grid-template-columns: 1fr;
            }

            .event-management .page-nav-btn,
            .event-management .page-num-btn {
                padding: 0 0.75rem;
            }
        }

        /* Dark mode fixes for Event Management filters and modals */
        html.dark .event-management .search-wrapper input {
            background: #111827 !important;
            border-color: #334155 !important;
            color: #F8FAFC !important;
            caret-color: #F8FAFC !important;
        }

        html.dark .event-management .search-wrapper input::placeholder {
            color: #94A3B8 !important;
        }

        html.dark .event-management .search-wrapper i,
        html.dark .event-management .search-wrapper svg {
            color: #94A3B8 !important;
        }

        html.dark .event-management .search-wrapper input:focus {
            background: #111827 !important;
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18) !important;
            outline: none !important;
        }

        html.dark .event-management .dropdown-btn {
            background: #111827 !important;
            border-color: #334155 !important;
            color: #F8FAFC !important;
        }

        html.dark .event-management .dropdown-btn i,
        html.dark .event-management .dropdown-btn svg {
            color: #CBD5E1 !important;
        }

        html.dark .event-management .dropdown-btn:hover,
        html.dark .event-management .dropdown-wrapper:focus-within .dropdown-btn {
            background: #1E293B !important;
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.14) !important;
        }

        html.dark .event-management .dropdown-menu {
            background: #111827 !important;
            border-color: #334155 !important;
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.45) !important;
        }

        html.dark .event-management .dropdown-item {
            color: #CBD5E1 !important;
        }

        html.dark .event-management .dropdown-item:hover {
            background: #1E293B !important;
            color: #93C5FD !important;
        }

        html.dark .event-management .dropdown-item i,
        html.dark .event-management .dropdown-item svg {
            color: #3B82F6 !important;
        }

        html.dark .event-management .dropdown-divider {
            background: #334155 !important;
        }

        html.dark .event-management .pill-btn {
            background: #1E293B;
            border-color: #334155;
            color: #CBD5E1;
        }

        html.dark .event-management .pill-btn:hover {
            background: #263449;
            border-color: #3B82F6;
            color: #93C5FD;
        }

        html.dark .event-management .pill-btn.active {
            background: #3B82F6;
            border-color: #3B82F6;
            color: #FFFFFF;
        }

        html.dark .event-management .modal-overlay {
            background: rgba(2, 6, 23, 0.76) !important;
        }

        html.dark .event-management .modal-container {
            background: #1E293B !important;
            border: 1px solid #334155 !important;
            color: #F8FAFC !important;
        }

        html.dark .event-management .modal-header {
            background: #1E293B !important;
            border-bottom-color: #334155 !important;
        }

        html.dark .event-management .modal-header h2,
        html.dark .event-management .modal-header h3,
        html.dark .event-management .delete-confirm-modal h3,
        html.dark .event-management .respond-confirm-modal h3 {
            color: #F8FAFC !important;
        }

        html.dark .event-management .modal-close-btn {
            background: #E2E8F0 !important;
            color: #475569 !important;
        }

        html.dark .event-management .modal-close-btn:hover {
            background: #FEE2E2 !important;
            color: #B91C1C !important;
        }

        html.dark .event-management .modal-form-content,
        html.dark .event-management .modal-left,
        html.dark .event-management .modal-right {
            background: #1E293B !important;
            border-color: #334155 !important;
        }

        html.dark .event-management .modal-left {
            border-right-color: #334155 !important;
        }

        html.dark .event-management .modal-footer-actions {
            background: #0F172A !important;
            border-top-color: #334155 !important;
        }

        html.dark .event-management .form-group label,
        html.dark .event-management .form-group label i,
        html.dark .event-management .input-with-icon i {
            color: #CBD5E1 !important;
        }

        html.dark .event-management .form-group label span {
            color: #F87171 !important;
        }

        html.dark .event-management .form-group input,
        html.dark .event-management .form-group textarea,
        html.dark .event-management .form-group select {
            background: #111827 !important;
            border-color: #334155 !important;
            color: #F8FAFC !important;
            caret-color: #F8FAFC !important;
            color-scheme: dark;
        }

        html.dark .event-management .form-group input::placeholder,
        html.dark .event-management .form-group textarea::placeholder {
            color: #94A3B8 !important;
        }

        html.dark .event-management .form-group input:focus,
        html.dark .event-management .form-group textarea:focus,
        html.dark .event-management .form-group select:focus {
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
            outline: none !important;
        }

        html.dark .event-management .form-group input[readonly],
        html.dark .event-management .form-group textarea[readonly] {
            background: #0F172A !important;
            color: #E2E8F0 !important;
            opacity: 1 !important;
        }

        html.dark .event-management .poster-preview-area,
        html.dark .event-management .poster-placeholder-text {
            background: #0F172A !important;
            border-color: #334155 !important;
            color: #CBD5E1 !important;
        }

        html.dark .event-management .poster-placeholder-text p,
        html.dark .event-management .poster-placeholder-text span {
            color: #CBD5E1 !important;
        }

        html.dark .event-management .category-chip .chip-content {
            background: #111827 !important;
            border-color: #334155 !important;
            color: #CBD5E1 !important;
        }

        html.dark .event-management .category-chip input:checked + .chip-content {
            background: rgba(59, 130, 246, 0.18) !important;
            border-color: #3B82F6 !important;
            color: #93C5FD !important;
        }

        html.dark .event-management .btn-cancel,
        html.dark .event-management .btn-secondary-gray {
            background: #111827 !important;
            border-color: #334155 !important;
            color: #F8FAFC !important;
        }

        html.dark .event-management .btn-cancel:hover,
        html.dark .event-management .btn-secondary-gray:hover {
            background: #1E293B !important;
            border-color: #475569 !important;
        }

        html.dark .event-management .delete-confirm-modal p,
        html.dark .event-management .respond-confirm-modal p {
            color: #CBD5E1 !important;
        }

        .event-management .respond-confirm-modal .rejection-reason-group {
            margin-bottom: 0 !important;
            padding-bottom: 24px !important;
            text-align: left;
        }

        .event-management .respond-confirm-modal .rejection-reason-group textarea {
            display: block;
            width: 100%;
        }

        .event-management .respond-confirm-modal .rejection-reason-group + .delete-modal-actions {
            margin-top: 0 !important;
        }

        .event-management .respond-confirm-modal .delete-modal-actions button {
            align-items: center !important;
            display: flex !important;
            justify-content: center !important;
            text-align: center !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('eventManagement', (config) => ({
                allEvents: config.initialEvents || [],
                categories: config.categories || [],
                search: '',
                statusFilter: 'all',
                categoryFilter: 'all',
                currentPage: 1,
                perPage: 5,
                viewMode: 'dashboard',
                showAddModal: false,
                newPosterPreview: null,
                isEditMode: false,
                isViewOnly: false,
                selectedEvent: null,
                showDeleteConfirm: false,
                itemToDelete: null,
                // Respond (approve/reject) state
                showRespondModal: false,
                respondEventId: null,
                respondEventTitle: '',
                respondAction: 'approve', // 'approve' | 'reject'
                rejectionReason: '',
                respondLoading: false,
                toast: { show: false, message: '', type: 'success', icon: 'check-circle' },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'alert-triangle');
                    this.toast.show = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                    setTimeout(() => { this.toast.show = false; }, 3000);
                },

                handlePosterChange(event) {
                    console.log('Poster change triggered');
                    const file = event.target.files[0];
                    if (file) {
                        this.newPosterPreview = URL.createObjectURL(file);
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                },

                openViewModal(event) {
                    console.log('Opening View Modal for event:', event);
                    this.isEditMode = true;
                    this.isViewOnly = true;
                    this.selectedEvent = event;
                    this.newPosterPreview = null;
                    this.showAddModal = true;
                },

                openEditModal(event) {
                    this.isEditMode = true;
                    this.isViewOnly = false;
                    this.selectedEvent = event;
                    this.newPosterPreview = null;
                    this.showAddModal = true;
                },

                openAddModal() {
                    this.isEditMode = false;
                    this.isViewOnly = false;
                    this.selectedEvent = null;
                    this.newPosterPreview = null;
                    this.showAddModal = true;
                },

                deleteEvent(id) {
                    console.log('Opening delete confirmation for:', id);
                    this.itemToDelete = id;
                    this.showDeleteConfirm = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                confirmDelete() {
                    if (!this.itemToDelete) return;
                    const form = this.$refs.deleteForm;
                    if (form) {
                        form.action = `/admin/events/${this.itemToDelete}`;
                        form.submit();
                    }
                },

                openRespondModal(eventId, eventTitle, action) {
                    this.respondEventId = eventId;
                    this.respondEventTitle = eventTitle;
                    this.respondAction = action;
                    this.rejectionReason = '';
                    this.respondLoading = false;
                    this.showRespondModal = true;
                    this.$nextTick(() => this.reinitIcons());
                },

                async confirmRespond() {
                    if (!this.respondEventId || this.respondLoading) return;
                    if (this.respondAction === 'reject' && !this.rejectionReason.trim()) {
                        this.showToast('Alasan penolakan wajib diisi.', 'error');
                        return;
                    }
                    this.respondLoading = true;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const url = `/admin/events/${this.respondEventId}/respond`;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: new URLSearchParams({
                                '_token': csrfToken,
                                'action': this.respondAction,
                                'rejection_reason': this.respondAction === 'reject' ? this.rejectionReason.trim() : '',
                            }),
                        });

                        if (response.ok || response.redirected) {
                            // Update in-memory allEvents: change status
                            const newStatus = this.respondAction === 'approve' ? 'upcoming' : 'rejected';
                            const eventIndex = this.allEvents.findIndex(e => e.id === this.respondEventId);
                            if (eventIndex !== -1) {
                                this.allEvents[eventIndex].status = newStatus;
                                this.allEvents[eventIndex].rejection_reason = this.respondAction === 'reject' ? this.rejectionReason.trim() : null;
                            }

                            const msg = this.respondAction === 'approve'
                                ? `Event berhasil disetujui!`
                                : `Event berhasil ditolak.`;
                            const type = 'success';

                            this.showRespondModal = false;
                            this.rejectionReason = '';
                            this.respondLoading = false;
                            this.showToast(msg, type);

                            // Reload after short delay so pending section updates
                            setTimeout(() => window.location.reload(), 1800);
                        } else {
                            let errorMsg = 'Gagal memperbarui status';
                            try {
                                const result = await response.json();
                                errorMsg = result.message || errorMsg;
                            } catch (e) {
                                console.error('Failed to parse error JSON:', e);
                            }
                            this.showToast(errorMsg, 'error');
                        }
                    } catch (error) {
                        console.error(error);
                        this.showToast('Terjadi kesalahan sistem. Silakan coba lagi.', 'error');
                    } finally {
                        this.respondLoading = false;
                    }
                },

                init() {
                    this.$watch('search', () => this.currentPage = 1);
                    this.$watch('statusFilter', () => this.currentPage = 1);
                    this.$watch('categoryFilter', () => this.currentPage = 1);

                    // Broad watch for any UI data changes to re-init icons
                    this.$watch('currentPage', () => this.$nextTick(() => this.reinitIcons()));
                    this.$watch('search', () => this.$nextTick(() => this.reinitIcons()));
                    this.$watch('statusFilter', () => this.$nextTick(() => this.reinitIcons()));
                    this.$watch('categoryFilter', () => this.$nextTick(() => this.reinitIcons()));

                    this.$watch('showAddModal', (value) => {
                        if (value) {
                            this.$nextTick(() => this.reinitIcons());
                            // Extra delay for complex modal content
                            setTimeout(() => this.reinitIcons(), 100);
                        }
                    });

                    this.$watch('showDeleteConfirm', (value) => {
                        if (value) this.$nextTick(() => this.reinitIcons());
                    });

                    this.$watch('showRespondModal', (value) => {
                        if (value) this.$nextTick(() => this.reinitIcons());
                    });

                    @if(session('success'))
                        this.$nextTick(() => {
                            this.showToast("{{ session('success') }}", 'success');
                        });
                    @endif

                    @if(session('error'))
                        this.$nextTick(() => {
                            this.showToast("{{ session('error') }}", 'error');
                        });
                    @endif

                    this.reinitIcons();
                },

                reinitIcons() {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                },

                get filteredEvents() {
                    let s = this.search.toLowerCase();
                    let st = this.statusFilter;
                    let cat = this.categoryFilter;

                    return this.allEvents.filter(e => {
                        let matchesSearch = s === '' ||
                            (e.title && e.title.toLowerCase().includes(s)) ||
                            (e.location && e.location.toLowerCase().includes(s)) ||
                            (e.creator_name && e.creator_name.toLowerCase().includes(s));

                        let matchesStatus = st === 'all' ||
                            (st === 'approved' && ['upcoming', 'ongoing', 'completed'].includes(e.status)) ||
                            e.status === st;
                        let matchesCat = cat === 'all' || e.category_id == cat;

                        return matchesSearch && matchesStatus && matchesCat;
                    });
                },

                get totalPages() {
                    return Math.ceil(this.filteredEvents.length / this.perPage);
                },

                get paginatedEvents() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    return this.filteredEvents.slice(start, end);
                },

                getStatusBadgeClass(status) {
                    if (status === 'upcoming' || status === 'ongoing' || status === 'completed') return 'badge-table badge-approved';
                    if (status === 'pending') return 'badge-table badge-pending-tbl';
                    if (status === 'rejected') return 'badge-table badge-rejected';
                    return 'badge-table badge-pending-tbl';
                },

                getStatusLabel(status) {
                    if (status === 'upcoming' || status === 'ongoing' || status === 'completed') return 'Approved';
                    if (status === 'pending') return 'Pending';
                    if (status === 'rejected') return 'Rejected';
                    return status.charAt(0).toUpperCase() + status.slice(1);
                },

                getStatusFilterLabel(status) {
                    const labels = {
                        all: 'Semua Status',
                        approved: 'Approved',
                        pending: 'Pending',
                        rejected: 'Rejected'
                    };
                    return labels[status] || this.capitalize(status);
                },

                capitalize(str) {
                    if (!str) return '';
                    return str.charAt(0).toUpperCase() + str.slice(1);
                }
            }));
        });

        // Re-initialize Lucide when Alpine updates the DOM
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
