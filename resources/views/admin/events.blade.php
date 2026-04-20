@extends('layouts.admin')

@section('title', 'Event Management')
@section('page_title', 'Event Management')

@section('content')
     <div class="event-management" 
         x-data="eventManagement({
            initialEvents: {{ json_encode($allEvents) }},
            categories: {{ json_encode($categories) }}
         })"
         x-cloak>
        
        <!-- View 1: Main Dashboard -->
        <div x-show="viewMode === 'dashboard'" x-transition:enter="transition-fade" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <!-- Stats Section -->
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
                    <span class="stat-sublabel text-orange">User awaiting approval &nearr;</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Rejected</span>
                    <div class="stat-value text-red">{{ $stats['rejected'] }}</div>
                </div>
            </div>

            <!-- Pending Requests Section (Dashboard View) -->
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
                                            <img src="{{ $event->poster }}" alt="{{ $event->title }}">
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
                                        <span>{{ $event->start_date->format('H:i') }} - {{ $event->end_date->format('H:i') }} WIB</span>
                                        <span>&bull;</span>
                                        <span>{{ $event->location }}</span>
                                    </div>
                                    <div class="event-submitter">
                                        Submitted by : <span class="submitter-name">{{ $event->creator->name ?? 'User' }}</span>
                                        <span class="submitter-time"> &bull; {{ $event->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <div class="pending-card-right">
                                    <button class="btn btn-action-outline"><i data-lucide="eye" style="width: 16px;"></i> Lihat Detail</button>
                                    <form action="{{ route('admin.events.respond', $event->event_id) }}" method="POST"
                                        @submit.prevent="if(confirm('Apakah Anda yakin ingin menyetujui event ini?')) $el.submit()">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-action-success">
                                            <i data-lucide="check" style="width: 16px;"></i> Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.events.respond', $event->event_id) }}" method="POST"
                                        @submit.prevent="if(confirm('Apakah Anda yakin ingin menolak event ini?')) $el.submit()">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-action-danger">
                                            <i data-lucide="x" style="width: 16px;"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Main Toolbar -->
            <div class="main-toolbar">
                <div class="toolbar-left">
                    <div class="search-wrapper">
                        <i data-lucide="search" style="width: 18px;"></i>
                        <input type="text" x-model.debounce.300ms="search" placeholder="Cari event...">
                    </div>
                    <div class="filter-dropdown">
                        <select x-model="statusFilter" class="custom-select">
                            <option value="all">Semua Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary-blue" @click="openAddModal()">
                    <i data-lucide="plus-circle" style="width: 18px;"></i> Tambah Event
                </button>
            </div>

            <!-- Category Filter Bar -->
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

            <!-- Events Table -->
            <div class="table-container">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th style="width: 300px;">Event</th>
                            <th>Kategori</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Dibuat oleh</th>
                            <th class="text-center">Suka</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="eventItem in paginatedEvents" :key="eventItem.id">
                            <tr>
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
                                <td class="text-center" x-text="eventItem.likes_count"></td>
                                <td>
                                    <span :class="getStatusBadgeClass(eventItem.status)" x-text="getStatusLabel(eventItem.status)"></span>
                                </td>
                                <td>
                                    <div class="cell-actions">
                                        <button class="action-icon-btn" @click="openViewModal(eventItem)" title="Detail">
                                            <i data-lucide="eye" style="width: 18px; pointer-events: none;"></i>
                                        </button>
                                        <button class="action-icon-btn icon-red" @click="deleteEvent(eventItem.id)" title="Hapus">
                                            <i data-lucide="trash-2" style="width: 18px; pointer-events: none;"></i>
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
                        <button @click="currentPage = p" 
                                :class="{ 'active': currentPage === p }" 
                                class="page-num-btn" 
                                x-text="p"></button>
                    </template>

                    <button @click="currentPage++" :disabled="currentPage === totalPages" class="page-nav-btn">
                        Next <i data-lucide="chevron-right" style="width: 16px;"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- View 2: All Pending Requests Dedicated View (PREMIUM REDESIGN) -->
        <div x-show="viewMode === 'requests'" 
             class="requests-view-wrapper"
             x-transition:enter="transition-fade" 
             x-transition:enter-start="opacity-0 translate-y-4" 
             x-transition:enter-end="opacity-100 translate-y-0">
            
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
                                        <img src="{{ $event->poster }}" alt="{{ $event->title }}">
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
                                                <div class="submitter-avatar">{{ substr($event->creator->name ?? 'U', 0, 1) }}</div>
                                                <span>{{ $event->creator->name ?? 'User' }}</span>
                                            </div>
                                            <span class="req-time-ago">{{ $event->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="req-details-grid">
                                        <div class="req-detail-item">
                                            <div class="detail-icon"><i data-lucide="calendar"></i></div>
                                            <div class="detail-text"><label>Tanggal</label><span>{{ $event->start_date->format('d F Y') }}</span></div>
                                        </div>
                                        <div class="req-detail-item">
                                            <div class="detail-icon"><i data-lucide="clock"></i></div>
                                            <div class="detail-text"><label>Waktu</label><span>{{ $event->start_date->format('H:i') }} WIB</span></div>
                                        </div>
                                        <div class="req-detail-item">
                                            <div class="detail-icon"><i data-lucide="map-pin"></i></div>
                                            <div class="detail-text"><label>Lokasi</label><span>{{ $event->location }}</span></div>
                                        </div>
                                        <div class="req-detail-item">
                                            <div class="detail-icon"><i data-lucide="bar-chart-2"></i></div>
                                            <div class="detail-text"><label>Kategori</label><span>{{ $event->category->name_category }}</span></div>
                                        </div>
                                    </div>
                                    <div class="req-description-preview">{{ Str::limit($event->description, 200) }}</div>
                                </div>
                            </div>

                            <div class="req-card-actions">
                                <button class="req-btn-secondary"><i data-lucide="eye" style="width: 16px;"></i> Full Details</button>
                                <div class="req-decision-btns">
                                    <form action="{{ route('admin.events.respond', $event->event_id) }}" method="POST"
                                        @submit.prevent="if(confirm('Approve this event?')) $el.submit()">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="req-btn-approve"><i data-lucide="check-circle" style="width: 18px;"></i> Setujui</button>
                                    </form>
                                    <form action="{{ route('admin.events.respond', $event->event_id) }}" method="POST"
                                        @submit.prevent="if(confirm('Reject this event?')) $el.submit()">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="req-btn-reject"><i data-lucide="slash" style="width: 18px;"></i> Tolak</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="req-empty-state">
                        <div class="empty-icon-box"><i data-lucide="shield-check" style="width: 48px;"></i></div>
                        <h3>All Done!</h3>
                        <p>No more pending permohonan to review.</p>
                        <button @click="viewMode = 'dashboard'" class="btn btn-primary-blue" style="margin-top: 1rem;">Back to Dashboard</button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Global Delete Form -->
        <form id="delete-event-form" x-ref="deleteForm" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <!-- Custom Delete Confirmation Modal -->
        <div x-show="showDeleteConfirm" 
             class="modal-overlay delete-modal-overlay"
             x-transition:enter="transition-fade"
             x-transition:leave="transition-fade"
             style="display: none; z-index: 9999;">
            
            <div class="modal-container delete-confirm-modal" @click.away="showDeleteConfirm = false" x-transition:enter="modal-slide-in">
                <div class="delete-icon-circle">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <h3>Hapus Event?</h3>
                <p>Apakah Anda yakin ingin menghapus event ini? Tindakan ini tidak dapat dibatalkan dan data akan hilang permanen.</p>
                
                <div class="delete-modal-actions">
                    <button @click="showDeleteConfirm = false" class="btn btn-secondary-gray flex items-center justify-center"><i data-lucide="x" style="width: 18px;"></i> Batal</button>
                    <button @click="confirmDelete()" class="btn btn-danger-solid flex items-center justify-center"><i data-lucide="trash" style="width: 18px;"></i> Ya, Hapus</button>
                </div>
            </div>
        </div>

        <!-- NEW: Tambah Event Modal -->
        <div x-show="showAddModal" 
             class="modal-overlay"
             x-transition:enter="transition-fade"
             x-transition:leave="transition-fade"
             @keydown.escape.window="showAddModal = false"
             style="display: none;">
            
            <div class="modal-container" @click.away="showAddModal = false" x-transition:enter="modal-slide-in">
                <div class="modal-header">
                    <h2 x-text="isEditMode ? 'Detail Event' : 'Tambah Event Baru'"></h2>
                    <button @click="showAddModal = false" class="modal-close-btn">
                        <i data-lucide="x" style="width: 20px;"></i>
                    </button>
                </div>

                <form class="modal-form-content">
                    <div class="modal-layout">
                        <!-- Left Column: Poster -->
                        <div class="modal-left">
                            <div class="form-group">
                                <label>Poster Event<span>*</span></label>
                                <div class="poster-preview-area" :class="{ 'has-image': newPosterPreview }">
                                    <template x-if="newPosterPreview">
                                        <div style="width: 100%; height: 100%;">
                                            <img :src="isEditMode ? (selectedEvent?.poster || newPosterPreview) : newPosterPreview" alt="Preview">
                                            <button type="button" class="change-poster-btn" @click="$refs.posterInput.click()">
                                                <i data-lucide="edit-3" style="width: 14px; display: inline; margin-right: 4px;"></i> Ubah
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!(isEditMode ? (selectedEvent?.poster || newPosterPreview) : newPosterPreview)">
                                        <div class="poster-placeholder-text" @click="$refs.posterInput.click()" style="cursor: pointer;">
                                            <i data-lucide="image"></i>
                                            <p>Klik untuk upload poster</p>
                                            <span style="font-size: 11px; color: #94A3B8;">Rasio 3:4 disarankan</span>
                                        </div>
                                    </template>
                                    <input type="file" x-ref="posterInput" hidden @change="handlePosterChange($event)">
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top: 20px;">
                                <label>Kategori Event<span>*</span></label>
                                <div class="category-grid">
                                    <template x-for="cat in categories" :key="cat.category_id">
                                        <label class="category-chip">
                                            <input type="radio" name="category" :value="cat.category_id" :checked="isEditMode && selectedEvent?.category_id == cat.category_id"> 
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
                                <input type="text" placeholder="Masukkan judul event" :value="isEditMode ? selectedEvent?.title : ''">
                            </div>

                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label><i data-lucide="calendar" style="width: 14px; display: inline; margin-right: 4px;"></i> Tanggal Mulai<span>*</span></label>
                                    <div class="input-with-icon">
                                        <input type="text" placeholder="day/month/year" :value="isEditMode ? selectedEvent?.start_date : ''">
                                        <i data-lucide="calendar"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i data-lucide="clock" style="width: 14px; display: inline; margin-right: 4px;"></i> Waktu Mulai<span>*</span></label>
                                    <div class="input-with-icon">
                                        <input type="text" placeholder="--:-- --" :value="isEditMode ? selectedEvent?.start_time : ''">
                                        <i data-lucide="clock"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label><i data-lucide="calendar" style="width: 14px; display: inline; margin-right: 4px;"></i> Tanggal Selesai<span>*</span></label>
                                    <div class="input-with-icon">
                                        <input type="text" placeholder="day/month/year" :value="isEditMode ? selectedEvent?.end_date : ''">
                                        <i data-lucide="calendar"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i data-lucide="clock" style="width: 14px; display: inline; margin-right: 4px;"></i> Waktu Selesai<span>*</span></label>
                                    <div class="input-with-icon">
                                        <input type="text" placeholder="--:-- --" :value="isEditMode ? selectedEvent?.end_time : ''">
                                        <i data-lucide="clock"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i data-lucide="map-pin" style="width: 14px; display: inline; margin-right: 4px;"></i> Lokasi Event<span>*</span></label>
                                <input type="text" placeholder="Masukkan lokasi event" :value="isEditMode ? selectedEvent?.location : ''">
                            </div>

                            <div class="form-group">
                                <label>Deskripsi<span>*</span></label>
                                <textarea placeholder="Jelaskan detail event, kontak, kuota peserta, dll..." x-text="isEditMode ? selectedEvent?.description : ''"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-actions" style="padding: 20px 28px;">
                        <button type="button" @click="showAddModal = false" class="btn-cancel" x-text="isEditMode ? 'Tutup' : 'Batal'"></button>
                        <button type="button" class="btn-save" x-show="!isEditMode">Simpan Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Add your existing styles plus these critical ones */
        [x-cloak] { display: none !important; }
        
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
        
        /* Table styles */
        .premium-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .premium-table th {
            text-align: left;
            padding: 1rem;
            background: #F8FAFC;
            color: #475569;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .premium-table td {
            padding: 1rem;
            border-bottom: 1px solid #E2E8F0;
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
                    newPosterPreview: null,
                    showDeleteConfirm: false,
                    itemToDelete: null,

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
                        this.isEditMode = true;
                        this.selectedEvent = event;
                        this.newPosterPreview = null;
                        this.showAddModal = true;
                    },

                    openAddModal() {
                        this.isEditMode = false;
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

                            let matchesStatus = st === 'all' || e.status === st;
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