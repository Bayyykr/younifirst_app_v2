@extends('layouts.admin')

@section('title', 'Lost and Found')
@section('page_title', 'Lost and Found')

@section('content')
<div class="lostfound-component" x-data="lostFoundManagement({{ $items->toJson() }})">

@if(auth()->user()->role === 'satpam')
{{-- ================================================================ --}}
{{-- SATPAM VIEW: Social card/feed grid layout                        --}}
{{-- ================================================================ --}}

    {{-- Toolbar --}}
    <div class="sf-toolbar">
        <div class="sf-search-box">
            <i data-lucide="search" class="sf-search-icon"></i>
            <input type="text" x-model.debounce.300ms="search"
                   placeholder="Ketik di pencarian..."
                   class="sf-search-input" id="satpam-search">
        </div>
        <button class="sf-post-btn" @click="openAddModal()">
            <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
            Posting
        </button>
    </div>

    {{-- Filter Pills --}}
    <div class="sf-filter-row">
        <i data-lucide="filter" style="width:16px;height:16px;color:#64748B;flex-shrink:0;"></i>
        <button class="sf-pill"
                :class="statusFilter === 'all' ? 'sf-pill-active' : ''"
                @click="statusFilter = 'all'">Semua</button>
        <button class="sf-pill"
                :class="statusFilter === 'lost' ? 'sf-pill-lost' : ''"
                @click="statusFilter = 'lost'">Hilang</button>
        <button class="sf-pill"
                :class="statusFilter === 'found' ? 'sf-pill-found' : ''"
                @click="statusFilter = 'found'">Ditemukan</button>
    </div>

    {{-- Card Grid --}}
    <div class="sf-grid">
        <template x-for="item in paginatedItems" :key="item.id">
            <div class="sf-card">

                {{-- Header: avatar + name + time + kebab menu --}}
                <div class="sf-card-header">
                    <div class="sf-user-info">
                        <div class="sf-avatar">
                            <template x-if="item.reporter_photo">
                                <img :src="item.reporter_photo" style="width:100%;height:100%;object-fit:cover;" :alt="item.reporter_name">
                            </template>
                            <template x-if="!item.reporter_photo">
                                <div class="sf-avatar-placeholder"
                                     x-text="item.reporter_name ? item.reporter_name.charAt(0).toUpperCase() : '?'"></div>
                            </template>
                        </div>
                        <div class="sf-user-meta">
                            <span class="sf-username" x-text="item.reporter_name"></span>
                            <span class="sf-time" x-text="item.time_ago"></span>
                        </div>
                    </div>
                    <div x-data="{ open: false }" style="position:relative;">
                        <button class="sf-menu-btn" @click="open = !open" @click.stop>
                            <i data-lucide="more-horizontal" style="width:18px;height:18px;"></i>
                        </button>
                        <div class="sf-dropdown" x-show="open" x-cloak
                             @click.outside="open = false">
                            <button class="sf-dropdown-item"
                                    @click="openDetailModal(item); open = false">
                                <i data-lucide="eye" style="width:14px;height:14px;"></i> Lihat Detail
                            </button>
                            <button x-show="item.status !== 'claimed' && item.can_claim"
                                    class="sf-dropdown-item sf-dropdown-success"
                                    @click="openResolveModal(item); open = false">
                                <i data-lucide="check-circle" style="width:14px;height:14px;"></i> Tandai Selesai
                            </button>
                            <button class="sf-dropdown-item sf-dropdown-danger"
                                    @click="openDeleteModal(item); open = false">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Image + Status Badge Overlay --}}
                <div class="sf-card-image-wrapper" @click="openDetailModal(item)">
                    <template x-if="item.photo">
                        <img :src="item.photo" :alt="item.name" class="sf-card-image">
                    </template>
                    <template x-if="!item.photo">
                        <div class="sf-card-image-placeholder">
                            <i data-lucide="package" style="width:48px;height:48px;opacity:0.25;"></i>
                        </div>
                    </template>
                    <span class="sf-status-badge"
                          :class="item.status === 'found'   ? 'sf-badge-found'   :
                                  item.status === 'claimed' ? 'sf-badge-claimed' :
                                                              'sf-badge-lost'"
                          x-text="item.status_label">
                    </span>
                </div>

                {{-- Body --}}
                <div class="sf-card-body">
                    <h4 class="sf-item-name" x-text="item.name"
                        @click="openDetailModal(item)"></h4>
                    <p class="sf-item-location">
                        <i data-lucide="map-pin"
                           style="width:13px;height:13px;flex-shrink:0;color:#4F46E5;"></i>
                        <span x-text="item.location"></span>
                    </p>
                </div>

                {{-- Footer: comment count --}}
                <div class="sf-card-footer">
                    <div class="sf-comment-count">
                        <i data-lucide="message-circle"
                           style="width:15px;height:15px;color:#64748B;"></i>
                        <span x-text="item.comments_count ?? 0"
                              style="font-size:13px;color:#64748B;font-weight:500;"></span>
                    </div>
                </div>

            </div>
        </template>

        {{-- Empty State --}}
        <div class="sf-empty" x-show="filteredItems.length === 0" x-cloak
             style="grid-column: 1 / -1;">
            <i data-lucide="inbox"
               style="width:48px;height:48px;opacity:0.2;margin-bottom:12px;"></i>
            <p>Tidak ada data ditemukan</p>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="sf-pagination" x-show="totalPages > 1" x-cloak>
        <button class="sf-page-btn" @click="prevPage"
                :disabled="currentPage === 1">
            <i data-lucide="chevron-left" style="width:16px;height:16px;"></i> Prev
        </button>
        <div style="display:flex;gap:4px;">
            <template x-for="p in totalPages" :key="p">
                <button class="sf-page-btn"
                        :class="{ 'sf-page-active': currentPage === p }"
                        @click="goToPage(p)" x-text="p"></button>
            </template>
        </div>
        <button class="sf-page-btn" @click="nextPage"
                :disabled="currentPage === totalPages">
            Next <i data-lucide="chevron-right" style="width:16px;height:16px;"></i>
        </button>
    </div>

@else
{{-- ================================================================ --}}
{{-- ADMIN VIEW: Stats + Table layout                                 --}}
{{-- ================================================================ --}}

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
            <input type="text" x-model.debounce.300ms="search"
                   placeholder="Cari nama, email, atau NIM..."
                   class="form-input search-input">
        </div>
        <div class="filter-actions">
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
    <div class="table-container">
        <table class="premium-table lf-table">
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
                    <tr class="table-row-hover">
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
                                    <span class="item-desc"
                                          x-text="item.description.length > 30
                                                   ? item.description.substring(0,30)+'...'
                                                   : item.description"></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="reporter-info">
                                <span class="reporter-name" x-text="item.reporter_name"></span>
                                <span class="reporter-nim"  x-text="item.reporter_nim"></span>
                            </div>
                        </td>
                        <td><span class="location-text" x-text="item.location"></span></td>
                        <td><span class="date-text"     x-text="item.date"></span></td>
                        <td>
                            <span :class="`status-badge ${item.status_class}`"
                                  x-text="item.status_label"></span>
                        </td>
                        <td>
                            <div class="cell-actions">
                                <button title="View" class="action-btn"
                                        @click="openDetailModal(item)">
                                    <i data-lucide="eye" style="width:18px;height:18px;"></i>
                                </button>
                                <button x-show="item.status !== 'claimed' && item.can_claim"
                                        title="Mark as Resolved"
                                        class="action-btn text-primary"
                                        @click="openResolveModal(item)">
                                    <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
                                </button>
                                <button title="Delete" class="action-btn text-danger"
                                        @click="openDeleteModal(item)">
                                    <i data-lucide="trash-2" style="width:18px;height:18px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredItems.length === 0" x-cloak>
                    <td colspan="6" class="text-center py-12">
                        <div class="empty-state">
                            <i data-lucide="inbox" class="icon-lg opacity-20"
                               style="width:48px;height:48px;margin:0 auto;"></i>
                            <p class="text-neutral-500 mt-2">Tidak ada data ditemukan</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div class="pagination-footer" x-show="totalPages > 1" x-cloak>
            <div class="pagination-info">
                Menampilkan <span x-text="startIndex + 1"></span> -
                <span x-text="Math.min(endIndex, totalItemsCount)"></span>
                dari <span x-text="totalItemsCount"></span> data
            </div>
            <div class="pagination-btns">
                <button class="page-nav-btn" @click="prevPage"
                        :disabled="currentPage === 1">
                    <i data-lucide="chevron-left" class="icon-xs"></i> Prev
                </button>
                <div class="page-numbers">
                    <template x-for="p in totalPages" :key="p">
                        <button class="page-num-btn"
                                :class="{ 'active': currentPage === p }"
                                @click="goToPage(p)" x-text="p"></button>
                    </template>
                </div>
                <button class="page-nav-btn" @click="nextPage"
                        :disabled="currentPage === totalPages">
                    Next <i data-lucide="chevron-right" class="icon-xs"></i>
                </button>
            </div>
        </div>
    </div>

@endif

{{-- ================================================================ --}}
{{-- SHARED MODALS (both roles)                                       --}}
{{-- ================================================================ --}}

    <!-- Add Item Modal -->
    <div class="modal-overlay" x-show="showAddModal" x-cloak x-transition>
        <div class="modal-container glass-panel" style="max-width:850px;"
             @click.outside="showAddModal = false">
            <div class="modal-header">
                <h3>Posting Barang Baru</h3>
                <button @click="showAddModal = false" class="close-btn">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <form @submit.prevent="addItem()">
                <div class="modal-body">
                    <div class="lf-add-grid">
                        <!-- Left: image upload -->
                        <div>
                            <label class="lf-upload-label">
                                Foto Barang
                            </label>
                            <div @click="$refs.fileInput.click()" class="lf-upload-zone">
                                <template x-if="!newItem.photo">
                                    <div class="lf-upload-empty">
                                        <i data-lucide="image-plus" class="lf-upload-icon"></i>
                                        <p>Klik untuk Pilih Foto</p>
                                    </div>
                                </template>
                                <template x-if="newItem.photo">
                                    <div class="lf-upload-preview">
                                        <img :src="URL.createObjectURL(newItem.photo)" alt="Preview foto barang">
                                        <div class="lf-upload-overlay">
                                            <span>Ganti Foto</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <input type="file" x-ref="fileInput"
                                   @change="handleFileUpload($event)"
                                   accept="image/*" style="display:none;">
                            <button type="button" @click="$refs.fileInput.click()"
                                    class="btn btn-secondary"
                                    style="width:100%;justify-content:center;border-style:solid;border-width:1.5px;">
                                <i data-lucide="upload-cloud" class="icon-xs"></i>
                                <span x-text="newItem.photo ? 'Ganti File' : 'Pilih File'"></span>
                            </button>
                            <p class="lf-upload-hint">
                                <i data-lucide="info"></i>
                                Format: JPG, PNG. Max 5MB.
                            </p>
                        </div>
                        <!-- Right: fields -->
                        <div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                                <div class="form-group" style="grid-column:span 2;">
                                    <label>Nama Barang</label>
                                    <input type="text" x-model="newItem.item_name" required
                                           placeholder="Contoh: Kunci Motor Vario">
                                </div>
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" x-model="newItem.location" required
                                           placeholder="Contoh: Kantin Pusat">
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select x-model="newItem.status" required>
                                        <option value="lost">Hilang</option>
                                        <option value="found">Ditemukan</option>
                                    </select>
                                </div>
                                <div class="form-group" style="grid-column:span 2;">
                                    <label>Deskripsi</label>
                                    <textarea x-model="newItem.description" required
                                              placeholder="Ciri-ciri barang, dsb..."
                                              rows="4" style="resize:none;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showAddModal = false"
                            class="btn-secondary">Batal</button>
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
        <div class="dm-container" @click.outside="showDetailModal = false">

            {{-- Modal Header --}}
            <div class="dm-header">
                <div class="dm-header-left">
                    <div class="dm-status-icon"
                         :class="selectedItem && selectedItem.status === 'found'   ? 'dm-icon-found'   :
                                 selectedItem && selectedItem.status === 'claimed' ? 'dm-icon-claimed' :
                                                                                    'dm-icon-lost'">
                        <i :data-lucide="selectedItem && selectedItem.status === 'found'   ? 'package-check' :
                                         selectedItem && selectedItem.status === 'claimed' ? 'check-circle'  :
                                                                                             'package-x'"
                           style="width:16px;height:16px;"></i>
                    </div>
                    <span class="dm-header-title"
                          x-text="selectedItem
                                  ? (selectedItem.status === 'found'   ? 'Postingan Ditemukan' :
                                     selectedItem.status === 'claimed' ? 'Postingan Selesai'   :
                                                                         'Postingan Hilang')
                                  : ''">
                    </span>
                </div>
                <button @click="showDetailModal = false" class="close-btn">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <template x-if="selectedItem">
                <div class="dm-body">

                    {{-- LEFT PANEL --}}
                    <div class="dm-left">
                        {{-- Image --}}
                        <div class="dm-image-wrap">
                            <template x-if="selectedItem.photo">
                                <img :src="selectedItem.photo" :alt="selectedItem.name"
                                     class="dm-image">
                            </template>
                            <template x-if="!selectedItem.photo">
                                <div class="dm-image-empty">
                                    <i data-lucide="archive" style="width:40px;height:40px;color:#4F46E5;opacity:0.6;margin-bottom:8px;"></i>
                                    <span style="font-size:13px;color:#94A3B8;">Tidak ada Gambar</span>
                                </div>
                            </template>
                        </div>

                        {{-- User info --}}
                        <div class="dm-user-row">
                            <template x-if="selectedItem.reporter_photo">
                                <img :src="selectedItem.reporter_photo" class="dm-user-avatar" style="object-fit: cover; padding: 0;" :alt="selectedItem.reporter_name">
                            </template>
                            <template x-if="!selectedItem.reporter_photo">
                                <div class="dm-user-avatar"
                                     x-text="selectedItem.reporter_name ? selectedItem.reporter_name.charAt(0).toUpperCase() : '?'">
                                </div>
                            </template>
                            <div class="dm-user-meta">
                                <span class="dm-user-name" x-text="selectedItem.reporter_name"></span>
                                <span class="dm-user-date" x-text="selectedItem.date"></span>
                            </div>
                            <div class="dm-time-badge">
                                <i data-lucide="clock" style="width:12px;height:12px;"></i>
                                <span x-text="selectedItem.time_ago"></span>
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="dm-actions" x-show="selectedItem.status !== 'claimed' && selectedItem.can_claim">
                            <button class="dm-btn-resolve"
                                    @click="openResolveModal(selectedItem); showDetailModal = false">
                                <i data-lucide="check-circle" style="width:16px;height:16px;"></i>
                                Tandai Selesai
                            </button>
                            <button class="dm-btn-delete"
                                    @click="openDeleteModal(selectedItem); showDetailModal = false">
                                <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                                Hapus Postingan
                            </button>
                        </div>

                        {{-- Info note --}}
                        <div class="dm-note" x-show="selectedItem.status !== 'claimed' && selectedItem.can_claim">
                            <i data-lucide="info" style="width:14px;height:14px;flex-shrink:0;color:#64748B;margin-top:1px;"></i>
                            <p>Postingan yang ditandai selesai tidak akan ditampilkan lagi di halaman utama karena kasusnya dianggap sudah selesai atau barang telah ditemukan.</p>
                        </div>

                        {{-- Already claimed note --}}
                        <div class="dm-note dm-note-success" x-show="selectedItem.status === 'claimed'">
                            <i data-lucide="check-circle" style="width:14px;height:14px;flex-shrink:0;color:#10B981;margin-top:1px;"></i>
                            <p>Postingan ini sudah ditandai selesai. Barang dianggap sudah diklaim/ditemukan.</p>
                        </div>
                    </div>

                    {{-- RIGHT PANEL --}}
                    <div class="dm-right">
                        {{-- Title --}}
                        <h2 class="dm-title" x-text="selectedItem.name"></h2>

                        {{-- Location --}}
                        <p class="dm-location">
                            <i data-lucide="map-pin" style="width:14px;height:14px;color:#4F46E5;flex-shrink:0;"></i>
                            <span x-text="selectedItem.location"></span>
                        </p>

                        {{-- Description --}}
                        <p class="dm-description" x-text="selectedItem.description"></p>

                        <div class="dm-divider"></div>

                        {{-- Comments area (Dynamic Threaded) --}}
                        <div class="dm-comments-area" id="comments-container">
                            <template x-for="thread in threadedComments" :key="thread.comment_id">
                                <div style="margin-bottom: 12px;">
                                    <!-- Parent Comment -->
                                    <div class="dm-comment-item" style="margin-bottom: 8px;">
                                        <template x-if="thread.commenter_photo">
                                            <img :src="thread.commenter_photo" x-on:error="$el.src = avatarPlaceholder(thread.commenter_name)" class="dm-comment-avatar" style="object-fit: cover; padding: 0;" :alt="thread.commenter_name">
                                        </template>
                                        <template x-if="!thread.commenter_photo">
                                            <div class="dm-comment-avatar"
                                                 x-text="thread.commenter_name ? thread.commenter_name.charAt(0).toUpperCase() : '?'">
                                            </div>
                                        </template>
                                        <div class="dm-comment-content">
                                            <div class="dm-comment-top">
                                                <span class="dm-comment-name" x-text="thread.commenter_name"></span>
                                                <span class="dm-comment-time" x-text="formatRelativeTime(thread.created_at)"></span>
                                                <template x-if="canManageComment(thread)">
                                                    <div class="dm-comment-actions">
                                                        <button class="dm-comment-menu" @click="startEditComment(thread)" title="Edit Komentar">
                                                            <i data-lucide="pencil" style="width:14px;height:14px;color:#4F46E5;"></i>
                                                        </button>
                                                        <button class="dm-comment-menu" @click="openDeleteCommentModal(thread.comment_id)" title="Hapus Komentar">
                                                            <i data-lucide="trash-2" style="width:14px;height:14px;color:#EF4444;"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="dm-comment-text-wrapper">
                                                <p class="dm-comment-text" x-show="editingCommentId !== thread.comment_id" x-text="getCleanComment(thread.comment)"></p>
                                                <div x-show="editingCommentId === thread.comment_id" class="dm-editing-hint">Sedang diedit di kolom input bawah</div>
                                            </div>
                                            <div style="margin-top: 4px;">
                                                <button @click="replyTo(thread)" style="background:none; border:none; color:#94A3B8; font-size: 11px; cursor: pointer; font-weight: 500; padding: 0;">Balas</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Replies -->
                                    <template x-if="thread.all_replies && thread.all_replies.length > 0">
                                        <div style="padding-left: 36px; position: relative;">
                                            <div style="position: absolute; left: 16px; top: -10px; bottom: 10px; width: 1px; background: #E2E8F0;"></div>
                                            <template x-for="reply in thread.all_replies" :key="reply.comment_id">
                                                <div class="dm-comment-item" style="margin-bottom: 8px;">
                                                    <template x-if="reply.commenter_photo">
                                                        <img :src="reply.commenter_photo" x-on:error="$el.src = avatarPlaceholder(reply.commenter_name)" class="dm-comment-avatar" style="width: 24px; height: 24px; object-fit: cover; padding: 0;" :alt="reply.commenter_name">
                                                    </template>
                                                    <template x-if="!reply.commenter_photo">
                                                        <div class="dm-comment-avatar" style="width: 24px; height: 24px; font-size: 10px;"
                                                             x-text="reply.commenter_name ? reply.commenter_name.charAt(0).toUpperCase() : '?'">
                                                        </div>
                                                    </template>
                                                    <div class="dm-comment-content">
                                                        <div class="dm-comment-top">
                                                            <span class="dm-comment-name" x-text="reply.commenter_name"></span>
                                                            <span class="dm-comment-time" x-text="formatRelativeTime(reply.created_at)"></span>
                                                            <template x-if="canManageComment(reply)">
                                                                <div class="dm-comment-actions">
                                                                    <button class="dm-comment-menu" @click="startEditComment(reply)" title="Edit Komentar">
                                                                        <i data-lucide="pencil" style="width:14px;height:14px;color:#4F46E5;"></i>
                                                                    </button>
                                                                    <button class="dm-comment-menu" @click="openDeleteCommentModal(reply.comment_id)" title="Hapus Komentar">
                                                                        <i data-lucide="trash-2" style="width:14px;height:14px;color:#EF4444;"></i>
                                                                    </button>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <div class="dm-comment-text-wrapper">
                                                            <p class="dm-comment-text" x-show="editingCommentId !== reply.comment_id">
                                                                <span style="color:#4F46E5; font-weight:500; margin-right:4px;" x-text="'@' + getReplyName(reply.comment)"></span>
                                                                <span x-text="getCleanComment(reply.comment)"></span>
                                                            </p>
                                                            <div x-show="editingCommentId === reply.comment_id" class="dm-editing-hint">Sedang diedit di kolom input bawah</div>
                                                        </div>
                                                        <div style="margin-top: 4px;">
                                                            <button @click="replyTo(reply)" style="background:none; border:none; color:#94A3B8; font-size: 11px; cursor: pointer; font-weight: 500; padding: 0;">Balas</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="displayableComments.length === 0 && !loadingComments">
                                <div style="text-align: center; color: #94A3B8; font-size: 13px; padding: 20px 0;">
                                    Belum ada komentar.
                                </div>
                            </template>
                            <template x-if="loadingComments">
                                <div style="text-align: center; color: #94A3B8; font-size: 13px; padding: 20px 0;">
                                    <i data-lucide="loader-2" class="animate-spin" style="width:18px;height:18px;margin:0 auto;"></i>
                                </div>
                            </template>
                        </div>

                        {{-- Comment input --}}
                        <div class="dm-comment-footer">
                            <form @submit.prevent="submitComment" style="display:flex; width:100%; align-items:center; gap:10px;">
                                <input type="text" class="dm-comment-input"
                                       x-ref="commentInput"
                                       x-model="newComment"
                                       :disabled="isCommenting"
                                       :placeholder="editingCommentId ? 'Edit komentar...' : 'Beri Komentar...'" required>
                                <button type="button" x-show="editingCommentId" @click="cancelEditComment" class="dm-comment-cancel" title="Batal edit">
                                    <i data-lucide="x" style="width:16px;height:16px;"></i>
                                </button>
                                <button type="submit" :disabled="isCommenting || !newComment.trim()" style="background:none;border:none;cursor:pointer;color:#4F46E5;display:flex;align-items:center;justify-content:center;padding:8px;">
                                    <i :data-lucide="editingCommentId ? 'check' : 'send'" style="width:18px;height:18px;" :style="(!newComment.trim() || isCommenting) ? 'opacity:0.5' : ''"></i>
                                </button>
                            </form>
                            <div style="display:flex;align-items:center;gap:4px;flex-shrink:0;">
                                <i data-lucide="message-circle" style="width:15px;height:15px;color:#94A3B8;"></i>
                                <span x-text="displayableComments.length"
                                      style="font-size:13px;color:#94A3B8;font-weight:500;"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </div>

    <!-- Resolve Confirmation Modal -->
    <div class="modal-overlay" x-show="showResolveModal" x-cloak x-transition>
        <div class="modal-container glass-panel"
             style="max-width:400px;text-align:center;"
             @click.outside="showResolveModal = false">
            <div class="modal-body" style="padding:40px 30px;">
                <div style="width:64px;height:64px;background:#ECFDF5;color:#10B981;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <i data-lucide="check-circle" style="width:32px;height:32px;"></i>
                </div>
                <h3 style="font-size:18px;margin-bottom:12px;">Selesaikan Postingan?</h3>
                <p style="color:#64748B;font-size:14px;margin-bottom:30px;">
                    Apakah barang ini sudah ditemukan/dikembalikan?
                    Status akan berubah menjadi "Diklaim".
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <button @click="showResolveModal = false"
                            class="btn btn-secondary"
                            style="justify-content:center;">Batal</button>
                    <button @click="confirmResolve()"
                            class="btn btn-primary"
                            style="background:#10B981;justify-content:center;border-color:#10B981;"
                            :disabled="loading">
                        <span x-show="!loading">Ya, Selesai</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Comment Confirmation Modal -->
    <div class="modal-overlay" x-show="showDeleteCommentModal" x-cloak x-transition>
        <div class="modal-container glass-panel"
             style="max-width:400px;text-align:center;"
             @click.outside="closeDeleteCommentModal()">
            <div class="modal-body" style="padding:40px 30px;">
                <div style="width:64px;height:64px;background:#FEF2F2;color:#EF4444;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <i data-lucide="message-circle-x" style="width:32px;height:32px;"></i>
                </div>
                <h3 style="font-size:18px;margin-bottom:12px;">Hapus Komentar?</h3>
                <p style="color:#64748B;font-size:14px;margin-bottom:30px;">
                    Komentar ini akan dihapus permanen dari percakapan.
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <button type="button" @click="closeDeleteCommentModal()"
                            class="btn btn-secondary"
                            style="justify-content:center;">Batal</button>
                    <button type="button" @click="confirmDeleteComment()"
                            class="btn btn-primary"
                            style="background:#EF4444;justify-content:center;border-color:#EF4444;"
                            :disabled="isDeletingComment">
                        <span x-show="!isDeletingComment">Ya, Hapus</span>
                        <span x-show="isDeletingComment">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" x-show="showDeleteModal" x-cloak x-transition>
        <div class="modal-container glass-panel"
             style="max-width:400px;text-align:center;"
             @click.outside="showDeleteModal = false">
            <div class="modal-body" style="padding:40px 30px;">
                <div style="width:64px;height:64px;background:#FEF2F2;color:#EF4444;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <i data-lucide="trash-2" style="width:32px;height:32px;"></i>
                </div>
                <h3 style="font-size:18px;margin-bottom:12px;">Hapus Data Barang?</h3>
                <p style="color:#64748B;font-size:14px;margin-bottom:30px;">
                    Tindakan ini tidak dapat dibatalkan. Data akan dihapus secara
                    permanen dari sistem.
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <button @click="showDeleteModal = false"
                            class="btn btn-secondary"
                            style="justify-content:center;">Batal</button>
                    <button @click="confirmDelete()"
                            class="btn btn-primary"
                            style="background:#EF4444;justify-content:center;border-color:#EF4444;"
                            :disabled="loading">
                        <span x-show="!loading">Ya, Hapus</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-wrapper" x-show="toast.show" x-cloak
         x-transition:enter="toast-enter" x-transition:leave="toast-leave">
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
html.dark {
    --bg-main:    #0F172A !important;
    --bg-white:   #1E293B !important;
    --text-main:  #F1F5F9 !important;
    --text-muted: #94A3B8 !important;
    --border-color:#334155 !important;
    --bg-hover:   #2D3748 !important;
}

/* ======================== SATPAM CARD LAYOUT ======================== */
.sf-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}
.sf-search-box { position: relative; flex: 1; }
.sf-search-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    width: 16px; height: 16px; color: #94A3B8; pointer-events: none;
}
.sf-search-input {
    width: 100%;
    padding: 11px 16px 11px 40px;
    border: 1.5px solid var(--border-color);
    border-radius: 40px;
    background: var(--bg-white);
    color: var(--text-main);
    font-size: 14px; outline: none;
    transition: border-color 0.2s;
}
.sf-search-input:focus { border-color: #4F46E5; }

.sf-post-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 22px;
    background: #4F46E5; color: white;
    border: none; border-radius: 40px;
    font-weight: 600; font-size: 14px;
    cursor: pointer; white-space: nowrap;
    transition: background 0.2s, transform 0.2s;
}
.sf-post-btn:hover { background: #4338CA; transform: translateY(-1px); }

/* Filter pills */
.sf-filter-row {
    display: flex; align-items: center;
    gap: 8px; margin-bottom: 20px; flex-wrap: wrap;
}
.sf-pill {
    padding: 7px 22px;
    border-radius: 40px;
    border: 1.5px solid var(--border-color);
    background: var(--bg-white);
    color: var(--text-main);
    font-size: 14px; font-weight: 500;
    cursor: pointer; transition: all 0.2s;
}
.sf-pill:hover                { border-color: #4F46E5; color: #4F46E5; }
.sf-pill-active               { background: #4F46E5 !important; color: white !important; border-color: #4F46E5 !important; }
.sf-pill-lost                 { background: #FEF2F2 !important; color: #DC2626 !important; border-color: #FCA5A5 !important; }
.sf-pill-found                { background: #ECFDF5 !important; color: #059669 !important; border-color: #6EE7B7 !important; }

/* Grid */
.sf-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px; margin-bottom: 24px;
}
@media (max-width: 1100px) { .sf-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width:  640px) { .sf-grid { grid-template-columns: 1fr; } }

/* Card */
.sf-card {
    background: var(--bg-white);
    border: 1px solid var(--border-color);
    border-radius: 16px; overflow: hidden;
    display: flex; flex-direction: column;
    transition: box-shadow 0.25s, transform 0.25s;
}
.sf-card:hover {
    box-shadow: 0 10px 32px rgba(0,0,0,0.09);
    transform: translateY(-3px);
}

/* Card Header */
.sf-card-header {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
}
.sf-user-info { display: flex; align-items: center; gap: 10px; }
.sf-avatar { width: 36px; height: 36px; border-radius: 50%; overflow: hidden; flex-shrink: 0; }
.sf-avatar-img { width: 100%; height: 100%; object-fit: cover; }
.sf-avatar-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg,#4F46E5,#7C3AED);
    color: white; display: flex;
    align-items: center; justify-content: center;
    font-weight: 700; font-size: 15px;
}
.sf-user-meta { display: flex; flex-direction: column; }
.sf-username  { font-size: 13px; font-weight: 600; color: var(--text-main); }
.sf-time      { font-size: 11px; color: var(--text-muted); }

.sf-menu-btn {
    width: 30px; height: 30px; border: none;
    background: transparent; color: var(--text-muted);
    cursor: pointer; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
}
.sf-menu-btn:hover { background: var(--bg-main); }

/* Dropdown */
.sf-dropdown {
    position: absolute; right: 0; top: 36px;
    background: var(--bg-white);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 100; min-width: 160px; overflow: hidden;
}
.sf-dropdown-item {
    display: flex; align-items: center; gap: 8px;
    width: 100%; padding: 10px 16px;
    background: none; border: none;
    font-size: 13px; color: var(--text-main);
    cursor: pointer; text-align: left;
    transition: background 0.15s;
}
.sf-dropdown-item:hover   { background: var(--bg-main); }
.sf-dropdown-success      { color: #10B981; }
.sf-dropdown-danger       { color: #EF4444; }

/* Card Image */
.sf-card-image-wrapper {
    position: relative; width: 100%;
    aspect-ratio: 4/3; background: #F1F5F9;
    overflow: hidden; cursor: pointer;
}
.sf-card-image {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.35s;
}
.sf-card:hover .sf-card-image { transform: scale(1.04); }
.sf-card-image-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    color: #94A3B8;
}

/* Status Badge Overlay */
.sf-status-badge {
    position: absolute; top: 12px; right: 12px;
    padding: 4px 12px; border-radius: 40px;
    font-size: 12px; font-weight: 700;
}
.sf-badge-found   { background: #10B981; color: white; }
.sf-badge-lost    { background: #EF4444; color: white; }
.sf-badge-claimed { background: #F59E0B; color: white; }

/* Card Body */
.sf-card-body { padding: 12px 14px 6px; }
.sf-item-name {
    font-size: 14px; font-weight: 700;
    color: var(--text-main); margin: 0 0 6px;
    cursor: pointer; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.sf-item-name:hover { color: #4F46E5; }
.sf-item-location {
    display: flex; align-items: center; gap: 5px;
    font-size: 12px; color: #64748B; margin: 0;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Card Footer */
.sf-card-footer {
    padding: 0 14px 14px;
    display: flex;
    justify-content: flex-end;
}
.sf-comment-count {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

/* Empty state */
.sf-empty {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 60px 20px;
    color: var(--text-muted); font-size: 14px;
}

/* Satpam Pagination */
.sf-pagination {
    display: flex; align-items: center;
    justify-content: center; gap: 8px; padding: 16px 0;
}
.sf-page-btn {
    display: flex; align-items: center; gap: 4px;
    padding: 8px 14px;
    border: 1.5px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-white);
    color: var(--text-main);
    font-size: 13px; cursor: pointer; transition: all 0.2s;
}
.sf-page-btn:disabled              { opacity: 0.4; cursor: not-allowed; }
.sf-page-btn:hover:not(:disabled)  { border-color: #4F46E5; color: #4F46E5; }
.sf-page-active { background: #4F46E5 !important; color: white !important; border-color: #4F46E5 !important; }

/* ======================== ADMIN TABLE LAYOUT ======================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem; margin-bottom: 2rem;
}
.stat-card {
    background: var(--bg-white); padding: 1.5rem;
    border-radius: 1rem; border: 1px solid var(--border-color);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,.05);
    transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-4px); }
.stat-label  { font-size:.875rem;font-weight:500;color:var(--text-muted);display:block;margin-bottom:.5rem; }
.stat-value  { font-size:2rem;font-weight:700;margin-bottom:.25rem;color:var(--text-main); }
.stat-sublabel { font-size:.75rem;color:#9CA3AF; }

.toolbar-section {
    display: flex; justify-content: space-between;
    align-items: center; gap: 1rem; margin-bottom: 1.5rem;
}
.search-box { position: relative; flex: 1; }
.search-icon {
    position: absolute; left: 1rem; top: 50%;
    transform: translateY(-50%); color: #9CA3AF;
    width: 16px; height: 16px;
}
.search-input {
    width: 100%; padding: .75rem 1rem .75rem 2.5rem;
    border-radius: .75rem;
    background: var(--bg-white); border: 1px solid var(--border-color);
    color: var(--text-main);
}
.filter-actions { display: flex; gap: .75rem; }
.status-filter {
    padding: .75rem 2.5rem .75rem 1rem;
    border-radius: .75rem; border: 1px solid var(--border-color);
    background: var(--bg-white); font-size: .875rem; color: var(--text-main);
}

.btn-primary {
    background: #4F46E5; color: white;
    padding: .75rem 1.5rem; border-radius: .75rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    border: 1px solid #4F46E5; cursor: pointer; transition: all 0.2s;
}
.btn-primary:hover { background: #4338CA; transform: translateY(-1px); }

.btn-secondary {
    background: var(--bg-white); color: var(--text-main);
    padding: .75rem 1.5rem; border-radius: .75rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s;
}
.btn-secondary:hover { background: var(--bg-hover); }

.table-container {
    background: var(--bg-white) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 16px !important;
    overflow: hidden !important;
    margin-bottom: 20px !important;
}
.premium-table {
    width: 100% !important;
    border-collapse: collapse !important;
    background: var(--bg-white) !important;
}
.premium-table th {
    text-align: left;
    padding: 1rem 1.5rem;
    background: var(--bg-main);
    color: var(--text-muted);
    font-weight: 600;
    font-size: .875rem;
    border-bottom: 1px solid var(--border-color);
}
.premium-table td {
    padding: 16px 24px;
    background: transparent;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
    color: var(--text-main);
}
.premium-table tbody tr {
    transition: all .2s ease;
    border-bottom: 1px solid var(--border-color);
}
.premium-table tbody tr:hover td,
.premium-table tbody tr:hover { background-color: var(--bg-hover) !important; }
.premium-table tbody tr:last-child td { border-bottom: none; }

.item-cell { display: flex; align-items: center; gap: 1rem; }
.item-img-container {
    width: 48px; height: 48px; border-radius: .75rem;
    background: var(--bg-main); overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--border-color);
}
.item-thumb { width: 100%; height: 100%; object-fit: cover; }
.item-thumb-placeholder { color: #9CA3AF; }
.item-info { display: flex; flex-direction: column; min-width: 0; }
.item-name { font-weight: 700; color: var(--text-main); font-size: .9375rem; }
.item-desc { font-size: .75rem; color: var(--text-muted); }
.reporter-info { display: flex; flex-direction: column; }
.reporter-name { font-weight: 600; color: var(--text-main); font-size: .875rem; }
.reporter-nim  { font-size: .75rem; color: var(--text-muted); }
.location-text,
.date-text { color: var(--text-main); font-weight: 500; }

.status-badge {
    padding: .25rem .75rem; border-radius: 9999px;
    font-size: .75rem; font-weight: 600;
}
.status-success { background: #ECFDF5; color: #059669; }
.status-danger  { background: #FEF2F2; color: #DC2626; }
.status-warning { background: #FFFBEB; color: #D97706; }
.status-neutral { background: #F9FAFB; color: #6B7280; }

.cell-actions { display: flex; gap: .5rem; }
.action-btn {
    width: 34px; height: 34px;
    background: var(--bg-main); border: 1px solid var(--border-color);
    border-radius: 10px; display: flex;
    align-items: center; justify-content: center;
    color: var(--text-muted); cursor: pointer;
    transition: all .2s cubic-bezier(.4,0,.2,1);
}
.action-btn:hover              { background: var(--bg-hover) !important; color: var(--text-main) !important; border-color: #CBD5E1 !important; transform: translateY(-1px); }
.action-btn.text-primary:hover { color: var(--primary) !important; border-color: var(--primary) !important; }
.action-btn.text-danger:hover  { color: var(--danger) !important;  border-color: var(--danger)  !important; }

.pagination-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: var(--bg-white);
    border-top: 1px solid var(--border-color);
}
.pagination-info { font-size: .875rem; color: var(--text-muted); font-weight: 500; }
.pagination-btns { display: flex; align-items: center; gap: .5rem; }
.page-numbers { display: flex; gap: .25rem; }
.page-nav-btn,
.page-num-btn {
    min-width: 36px;
    height: 36px;
    padding: 0 .75rem;
    border: 1px solid var(--border-color);
    background: var(--bg-white);
    color: var(--text-main);
    border-radius: 8px;
    font-size: .875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .25rem;
    cursor: pointer;
    transition: all .2s;
}
.page-nav-btn:hover:not(:disabled),
.page-num-btn:hover { border-color: #3B82F6; color: #3B82F6; }
.page-nav-btn:disabled { opacity: .5; cursor: not-allowed; }
.page-num-btn.active { background: #3B82F6; color: #fff; border-color: #3B82F6; }

/* ======================== DETAIL MODAL (dm-*) ======================== */

/* Container */
.dm-container {
    width: calc(100vw - 40px);
    max-width: 860px;
    background: var(--bg-white);
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.22);
    overflow: hidden;
    border: 1px solid var(--border-color);
    animation: modalSlideUp .3s ease-out;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 48px);
}

/* Header */
.dm-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-white);
    flex-shrink: 0;
}
.dm-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.dm-status-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
}
.dm-icon-lost    { background: #FEF2F2; color: #EF4444; }
.dm-icon-found   { background: #ECFDF5; color: #10B981; }
.dm-icon-claimed { background: #FFFBEB; color: #F59E0B; }
.dm-header-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-main);
}

/* Body layout */
.dm-body {
    display: grid;
    grid-template-columns: 300px 1fr;
    overflow: hidden;
    flex: 1;
    min-height: 0;
}

/* LEFT PANEL */
.dm-left {
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.dm-image-wrap {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: #F1F5F9;
    overflow: hidden;
    flex-shrink: 0;
}
.dm-image {
    width: 100%; height: 100%;
    object-fit: cover;
}
.dm-image-empty {
    width: 100%; height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94A3B8;
}

/* User row */
.dm-user-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color);
}
.dm-user-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg,#4F46E5,#7C3AED);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px;
    flex-shrink: 0;
}
.dm-user-meta { display: flex; flex-direction: column; flex: 1; min-width: 0; }
.dm-user-name { font-size: 13px; font-weight: 600; color: var(--text-main); }
.dm-user-date { font-size: 11px; color: var(--text-muted); }
.dm-time-badge {
    display: flex; align-items: center; gap: 4px;
    font-size: 11px; color: var(--text-muted);
    white-space: nowrap; flex-shrink: 0;
}

/* Action buttons */
.dm-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color);
}
.dm-btn-resolve {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 8px;
    background: #10B981; color: white;
    border: none; border-radius: 10px;
    font-size: 12px; font-weight: 700;
    cursor: pointer; transition: background .2s, transform .2s;
}
.dm-btn-resolve:hover { background: #059669; transform: translateY(-1px); }
.dm-btn-delete {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 8px;
    background: white; color: #EF4444;
    border: 1.5px solid #FCA5A5; border-radius: 10px;
    font-size: 12px; font-weight: 700;
    cursor: pointer; transition: all .2s;
}
.dm-btn-delete:hover { background: #FEF2F2; border-color: #EF4444; transform: translateY(-1px); }

/* Info note */
.dm-note {
    display: flex; align-items: flex-start; gap: 8px;
    padding: 12px 16px;
    background: #F8FAFC;
    margin: 12px 16px;
    border-radius: 10px;
}
.dm-note p { font-size: 11.5px; color: #64748B; line-height: 1.5; margin: 0; }
.dm-note-success { background: #ECFDF5; }
.dm-note-success p { color: #065F46; }

/* RIGHT PANEL */
.dm-right {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.dm-title {
    font-size: 18px; font-weight: 700;
    color: var(--text-main);
    padding: 18px 20px 6px;
    line-height: 1.3;
}
.dm-location {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; color: #4F46E5; font-weight: 500;
    padding: 0 20px 12px;
}
.dm-description {
    font-size: 13.5px; color: #4B5563; line-height: 1.65;
    padding: 0 20px 14px;
    margin: 0;
}
.dm-divider {
    height: 1px; background: var(--border-color);
    margin: 0 0 4px;
}

/* Comments area */
.dm-comments-area {
    flex: 1;
    overflow-y: auto;
    padding: 12px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.dm-comment-item {
    display: flex; gap: 10px;
}
.dm-comment-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg,#64748B,#94A3B8);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px; flex-shrink: 0;
}
.dm-comment-content { flex: 1; min-width: 0; }
.dm-comment-top {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 4px;
}
.dm-comment-name { font-size: 13px; font-weight: 600; color: var(--text-main); }
.dm-comment-time { font-size: 11px; color: var(--text-muted); flex: 1; }
.dm-comment-menu {
    width: 24px; height: 24px;
    border: none; background: none;
    color: var(--text-muted);
    cursor: pointer; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
}
.dm-comment-menu:hover { background: var(--bg-main); }
.dm-comment-actions {
    display: flex;
    align-items: center;
    gap: 2px;
}
.dm-editing-hint {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 999px;
    background: #EEF2FF;
    color: #4F46E5;
    font-size: 11px;
    font-weight: 600;
}
.dm-comment-text {
    font-size: 13px; color: #4B5563; line-height: 1.55; margin: 0 0 6px;
}
.dm-reply-link {
    border: none; background: none;
    font-size: 12px; color: #94A3B8;
    cursor: pointer; padding: 0;
    display: flex; align-items: center;
}
.dm-reply-link:hover { color: #4F46E5; }

/* Comment footer input */
.dm-comment-footer {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 20px;
    border-top: 1px solid var(--border-color);
    flex-shrink: 0;
}
.dm-comment-input {
    flex: 1; padding: 8px 14px;
    border: 1.5px solid var(--border-color);
    border-radius: 40px;
    background: var(--bg-main);
    color: var(--text-muted);
    font-size: 13px; outline: none;
    transition: border-color .2s;
}
.dm-comment-input:focus { border-color: #4F46E5; }
.dm-comment-cancel {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    background: #FEF2F2;
    color: #EF4444;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.dm-comment-cancel:hover { background: #FEE2E2; }

/* ======================== SHARED MODALS ======================== */

.modal-overlay {
    position: fixed; top:0;left:0;right:0;bottom:0;
    background: rgba(15,23,42,.6);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000; backdrop-filter: blur(8px); padding: 20px;
}
.modal-container {
    width: 100%; max-width: 650px;
    background: var(--bg-white); border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);
    display: flex; flex-direction: column;
    overflow: hidden; animation: modalSlideUp .3s ease-out;
    border: 1px solid var(--border-color);
}
@keyframes modalSlideUp {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.modal-header {
    padding: 24px 30px; border-bottom: 1px solid var(--border-color);
    display: flex; justify-content: space-between; align-items: center;
    background: var(--bg-white); flex-shrink: 0;
}
.modal-header h3 { font-size: 18px; font-weight: 700; color: var(--text-main); }
.modal-body {
    padding: 30px;
    max-height: 60vh;
    overflow-y: auto;
    flex-grow: 1;
    background: var(--bg-white);
    color: var(--text-main);
}
.modal-footer {
    padding: 24px 30px; background: var(--bg-main);
    border-top: 1px solid var(--border-color);
    display: flex; justify-content: flex-end; gap: 12px; flex-shrink: 0;
}
.lf-add-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 30px;
}
.lf-upload-label,
.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-main);
}
.lf-upload-label {
    display: block;
    margin-bottom: 8px;
}
.lf-upload-zone {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: var(--bg-main);
    border: 2px dashed var(--border-color);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 16px;
    position: relative;
    cursor: pointer;
    transition: all .2s;
}
.lf-upload-zone:hover {
    border-color: #4F46E5;
    background: var(--bg-hover);
}
.lf-upload-empty {
    text-align: center;
    color: var(--text-muted);
}
.lf-upload-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 8px;
}
.lf-upload-empty p {
    font-size: 14px;
    font-weight: 500;
    margin: 0;
}
.lf-upload-preview {
    width: 100%;
    height: 100%;
    position: relative;
}
.lf-upload-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.lf-upload-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, .45);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .2s;
}
.lf-upload-preview:hover .lf-upload-overlay { opacity: 1; }
.lf-upload-overlay span {
    color: #fff;
    background: rgba(15, 23, 42, .7);
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}
.lf-upload-hint {
    text-align: center;
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.lf-upload-hint i,
.lf-upload-hint svg {
    width: 12px;
    height: 12px;
}
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group input, .form-group select, .form-group textarea {
    padding: 12px 16px; border: 1.5px solid var(--border-color);
    border-radius: 12px; font-size: 14px; transition: all .2s;
    background: var(--bg-white); color: var(--text-main);
}
.form-group input::placeholder, .form-group textarea::placeholder { color: #94A3B8; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color: #4F46E5; box-shadow: 0 0 0 4px rgba(79,70,229,.1); outline: none;
}
.close-btn {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    border: none; background: var(--bg-main); color: var(--text-muted);
    cursor: pointer; transition: all .2s;
}
.close-btn:hover { background: var(--bg-hover); color: #EF4444; }

.text-success { color: #10B981; }
.text-danger  { color: #EF4444; }
.text-warning { color: #F59E0B; }
.text-neutral { color: #64748B; }
.icon-sm { width: 18px; height: 18px; }
.icon-xs { width: 16px; height: 16px; }
[x-cloak] { display: none !important; }

/* Toast */
.toast-wrapper {
    position: fixed; top: 30px; right: 30px;
    z-index: 10000; pointer-events: none;
}
.toast-box {
    display: flex; align-items: center; gap: 16px;
    background: var(--bg-white); padding: 16px 24px;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04);
    min-width: 320px; border-left: 6px solid #10B981;
    pointer-events: auto;
    animation: toastSlideIn .4s cubic-bezier(.16,1,.3,1);
    color: var(--text-main);
}
.toast-success { border-color: #10B981; }
.toast-error   { border-color: #EF4444; }
.toast-icon {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 12px;
}
.toast-success .toast-icon { background: #ECFDF5; color: #10B981; }
.toast-error   .toast-icon { background: #FEF2F2; color: #EF4444; }
.toast-content p { font-size: 14px; font-weight: 600; color: var(--text-main); margin: 0; }
@keyframes toastSlideIn {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.toast-enter { animation: toastSlideIn .4s ease-out; }
.toast-leave { animation: toastSlideIn .4s ease-in reverse; }

@media (max-width: 768px) {
    .lostfound-component {
        width: 100%;
        min-width: 0;
        overflow-x: hidden;
    }

    /* Satpam feed */
    .sf-toolbar {
        align-items: stretch;
        flex-direction: column;
        gap: 12px;
    }

    .sf-search-box,
    .sf-post-btn {
        width: 100%;
    }

    .sf-post-btn {
        justify-content: center;
    }

    .sf-filter-row {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }

    .sf-filter-row::-webkit-scrollbar {
        display: none;
    }

    .sf-pill {
        flex: 0 0 auto;
        padding: 7px 16px;
    }

    .sf-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .sf-item-name,
    .sf-item-location {
        white-space: normal;
        word-break: break-word;
    }

    .sf-pagination {
        flex-wrap: wrap;
    }

    .sf-pagination > div {
        flex-wrap: wrap;
        justify-content: center;
    }

    /* Admin stats and filters */
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 14px;
        margin-bottom: 20px;
    }

    .stat-card {
        padding: 18px;
    }

    .stat-label {
        font-size: 13px;
    }

    .stat-value {
        font-size: 28px;
    }

    .toolbar-section {
        align-items: stretch;
        flex-direction: column;
        gap: 12px;
    }

    .search-box,
    .filter-actions,
    .status-filter,
    .filter-actions .btn,
    .filter-actions .btn-primary {
        width: 100%;
    }

    .filter-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .status-filter {
        min-width: 0;
    }

    /* Admin table as mobile cards */
    .table-container {
        overflow: visible !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
    }

    .premium-table,
    .premium-table tbody,
    .premium-table tr,
    .premium-table td {
        display: block;
        width: 100%;
    }

    .premium-table {
        min-width: 0;
        background: transparent !important;
    }

    .premium-table thead {
        display: none;
    }

    .premium-table tbody {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .premium-table tbody tr {
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    .premium-table tbody td {
        border-bottom: 0;
        padding: 9px 0;
        font-size: 13px;
    }

    .premium-table tbody td:not(:first-child) {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border-top: 1px solid var(--border-color);
        text-align: right;
    }

    .premium-table tbody td:not(:first-child)::before {
        content: '';
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: left;
        flex-shrink: 0;
    }

    .premium-table tbody td:nth-child(2)::before { content: 'Pelapor'; }
    .premium-table tbody td:nth-child(3)::before { content: 'Lokasi'; }
    .premium-table tbody td:nth-child(4)::before { content: 'Tanggal'; }
    .premium-table tbody td:nth-child(5)::before { content: 'Status'; }
    .premium-table tbody td:nth-child(6)::before { content: 'Aksi'; }

    .item-cell {
        align-items: flex-start;
    }

    .item-info,
    .reporter-info {
        min-width: 0;
    }

    .item-name,
    .item-desc,
    .reporter-name,
    .reporter-nim,
    .location-text,
    .date-text {
        white-space: normal;
        word-break: break-word;
    }

    .cell-actions {
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .empty-state {
        padding: 24px 0;
    }

    .pagination-footer {
        align-items: stretch;
        flex-direction: column;
        gap: 12px;
        padding: 18px 0 0;
        background: transparent;
    }

    .pagination-info {
        text-align: center;
    }

    .pagination-btns,
    .page-numbers {
        flex-wrap: wrap;
        justify-content: center;
    }

    /* Add modal */
    .modal-overlay {
        align-items: flex-end;
        padding: 12px;
    }

    .modal-container,
    .dm-container {
        width: 100%;
        max-height: calc(100vh - 24px);
        border-radius: 20px;
    }

    .modal-header,
    .modal-body,
    .modal-footer {
        padding-left: 18px;
        padding-right: 18px;
    }

    .modal-footer {
        flex-direction: column-reverse;
    }

    .modal-footer .btn-primary,
    .modal-footer .btn-secondary,
    .modal-footer button {
        width: 100%;
    }

    .lf-add-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .lf-add-grid > div:nth-child(2) > div {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
    }

    .lf-add-grid .form-group {
        grid-column: auto !important;
    }

    .lf-upload-zone {
        max-width: 240px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Detail modal */
    .dm-body {
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    .dm-left {
        border-right: 0;
        border-bottom: 1px solid var(--border-color);
        overflow: visible;
    }

    .dm-image-wrap {
        max-height: 280px;
    }

    .dm-user-row {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .dm-time-badge {
        width: 100%;
        margin-left: 46px;
    }

    .dm-actions {
        grid-template-columns: 1fr;
    }

    .dm-right {
        min-height: 420px;
        overflow: visible;
    }

    .dm-comments-area {
        max-height: 320px;
        overflow-y: auto;
    }

    .dm-comment-top {
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 4px 8px;
    }

    .dm-comment-footer {
        align-items: stretch;
        flex-wrap: wrap;
    }

    .dm-comment-footer form {
        min-width: 0;
    }

    /* Confirm modals */
    .modal-body div[style*='grid-template-columns:1fr 1fr'] {
        grid-template-columns: 1fr !important;
    }

    /* Toast */
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
    .filter-actions {
        grid-template-columns: 1fr;
    }

    .page-nav-btn,
    .page-num-btn,
    .sf-page-btn {
        padding-left: 10px;
        padding-right: 10px;
    }
}
</style>

@push('scripts')
<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-database-compat.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        // Initialize Firebase
        const firebaseConfig = @json($firebaseConfig);
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        const database = firebase.database();

        Alpine.data('lostFoundManagement', (initialItems) => ({
            allItems: initialItems,
            currentUserRole: @json(auth()->user()->role),
            currentUserId: @json(trim((string) auth()->id())),
            currentUserName: @json(auth()->user()->name),
            currentUserPhoto: @json(auth()->user()->photo_url ?? null),
            relativeTimeInterval: null,
            search: '',
            statusFilter: 'all',
            currentPage: 1,
            perPage: {{ auth()->user()->role === 'satpam' ? 9 : 5 }},
            loading: false,

            // Modal States
            showAddModal: false,
            showDetailModal: false,
            showResolveModal: false,
            showDeleteModal: false,
            showDeleteCommentModal: false,

            // Current Selection
            selectedItem: null,
            commentToDeleteId: null,
            isDeletingComment: false,
            newItem: {
                item_name: '', description: '', location: '',
                status: 'lost', photo: null
            },

            // Toast
            toast: { show: false, message: '', type: 'success' },

            // Comments State
            comments: [],
            newComment: '',
            isCommenting: false,
            loadingComments: false,
            editingCommentId: null,
            editingOriginalComment: '',
            activeFirebaseRef: null,
            activeFirebasePath: null,
            firebaseListenStartedAt: null,

            init() {
                this.$watch('search',       () => this.currentPage = 1);
                this.$watch('statusFilter', () => this.currentPage = 1);
                this.$watch('paginatedItems', () => {
                    this.$nextTick(() => lucide.createIcons());
                });
                this.relativeTimeInterval = setInterval(() => {
                    this.comments = this.comments.map(comment => ({ ...comment }));
                }, 60000);
                this.$nextTick(() => lucide.createIcons());
            },

            get filteredItems() {
                let q = this.search.toLowerCase();
                let s = this.statusFilter;
                return this.allItems.filter(item => {
                    if (this.currentUserRole === 'satpam' && item.status === 'claimed') return false;
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
            get totalPages()      { return Math.ceil(this.totalItemsCount / this.perPage) || 1; },
            get startIndex()      { return (this.currentPage - 1) * this.perPage; },
            get endIndex()        { return this.startIndex + this.perPage; },
            get paginatedItems()  { return this.filteredItems.slice(this.startIndex, this.endIndex); },

            prevPage()   { if (this.currentPage > 1) this.currentPage--; },
            nextPage()   { if (this.currentPage < this.totalPages) this.currentPage++; },
            goToPage(p)  { this.currentPage = p; },

            isReply(text) {
                return text && text.match(/^\[re:CMT[A-Z0-9]+\]/i) !== null;
            },
            getReplyName(text) {
                if (!text) return '';
                const match = text.match(/^\[re:(CMT[A-Z0-9]+)\]/i);
                if (match) {
                    const replyId = match[1];
                    const originalComment = this.comments.find(c => c.comment_id === replyId);
                    // Instead of full name, take first name
                    return originalComment ? originalComment.commenter_name.split(' ')[0] : 'seseorang';
                }
                return '';
            },
            getCleanComment(text) {
                if (!text) return '';
                return text.replace(/^\[re:CMT[A-Z0-9]+\]\s*/i, '');
            },
            replyTo(comment) {
                if (this.editingCommentId) this.cancelEditComment();
                this.newComment = `[re:${comment.comment_id}] `;
                this.$refs.commentInput.focus();
            },

            formatRelativeTime(value) {
                if (!value) return '';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return '';

                const diffSeconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
                if (diffSeconds < 60) return 'Baru saja';

                const units = [
                    { limit: 3600, seconds: 60, label: 'menit' },
                    { limit: 86400, seconds: 3600, label: 'jam' },
                    { limit: 604800, seconds: 86400, label: 'hari' },
                    { limit: 2592000, seconds: 604800, label: 'minggu' },
                    { limit: 31536000, seconds: 2592000, label: 'bulan' },
                    { limit: Infinity, seconds: 31536000, label: 'tahun' },
                ];

                const unit = units.find(item => diffSeconds < item.limit);
                const amount = Math.floor(diffSeconds / unit.seconds);
                return `${amount} ${unit.label} yang lalu`;
            },

            get displayableComments() {
                return this.comments.filter(comment =>
                    comment.comment_id &&
                    comment.user_id &&
                    comment.commenter_name &&
                    comment.comment
                );
            },

            get threadedComments() {
                const validComments = this.displayableComments;
                let map = {};
                validComments.forEach(c => {
                    map[c.comment_id] = { ...c, is_reply: false, root_id: null };
                });

                let roots = [];

                validComments.forEach(c => {
                    const match = c.comment.match(/^\[re:(CMT[A-Z0-9]+)\]/i);
                    let targetId = match ? match[1] : null;

                    if (targetId && map[targetId]) {
                        let rootId = targetId;
                        while(map[rootId] && map[rootId].root_id) {
                            rootId = map[rootId].root_id;
                        }
                        map[c.comment_id].root_id = rootId;
                        map[c.comment_id].is_reply = true;
                    }
                });

                let threaded = [];
                validComments.forEach(c => {
                    if (!map[c.comment_id].is_reply) {
                        threaded.push({ ...c, all_replies: [] });
                    }
                });

                let threadMap = {};
                threaded.forEach(t => threadMap[t.comment_id] = t);

                validComments.forEach(c => {
                    if (map[c.comment_id].is_reply) {
                        let rootId = map[c.comment_id].root_id;
                        if (threadMap[rootId]) {
                            threadMap[rootId].all_replies.push(c);
                        } else {
                            threaded.push({ ...c, all_replies: [] });
                            threadMap[c.comment_id] = threaded[threaded.length - 1];
                            map[c.comment_id].is_reply = false;
                        }
                    }
                });

                return threaded;
            },

            openAddModal()       { this.newItem = { item_name: '', description: '', location: '', status: 'lost', photo: null }; this.showAddModal = true; },

            async openDetailModal(i) {
                this.selectedItem = i;
                this.showDetailModal = true;
                this.comments = [];
                this.newComment = '';
                this.editingCommentId = null;
                this.editingOriginalComment = '';

                await this.loadComments(i.id);
                this.listenToFirebase(i.id);

                this.$nextTick(() => lucide.createIcons());
            },

            async loadComments(lostfoundId) {
                this.loadingComments = true;
                try {
                    const response = await fetch(`{{ url('/admin/lostfound') }}/${lostfoundId}/comments?per_page=100`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (response.ok) {
                        const result = await response.json();
                        this.comments = result.data.map(comment => this.normalizeComment(comment));
                        this.firebaseListenStartedAt = Date.now();
                        this.updateItemCommentCount(lostfoundId);
                        this.scrollToBottom();
                    }
                } catch (e) {
                    console.error('Failed to load comments', e);
                } finally {
                    this.loadingComments = false;
                }
            },

            listenToFirebase(lostfoundId) {
                if (this.activeFirebaseRef) {
                    this.activeFirebaseRef.off();
                }

                this.activeFirebasePath = 'lostfound_comments/' + lostfoundId;
                this.activeFirebaseRef = database.ref(this.activeFirebasePath);
                this.activeFirebaseRef.on('child_added', (snapshot) => {
                    const incoming = this.normalizeComment({
                        ...snapshot.val(),
                        firebase_key: snapshot.key,
                    });
                    if (!this.isValidComment(incoming)) return;

                    const index = this.comments.findIndex(c => c.comment_id === incoming.comment_id);
                    const createdAt = new Date(incoming.created_at).getTime();
                    const isOldFirebaseOnlyComment = index === -1
                        && this.firebaseListenStartedAt
                        && !Number.isNaN(createdAt)
                        && createdAt < this.firebaseListenStartedAt - 5000;

                    if (isOldFirebaseOnlyComment) return;

                    if (index === -1) {
                        this.comments.push(incoming);
                        this.scrollToBottom();
                    } else {
                        this.comments = this.comments.map(comment =>
                            comment.comment_id === incoming.comment_id
                                ? { ...comment, ...incoming }
                                : comment
                        );
                    }

                    this.updateItemCommentCount(lostfoundId);
                    this.$nextTick(() => lucide.createIcons());
                });

                this.activeFirebaseRef.on('child_changed', (snapshot) => {
                    const updatedComment = this.normalizeComment({
                        ...snapshot.val(),
                        firebase_key: snapshot.key,
                    });
                    if (!this.isValidComment(updatedComment)) return;

                    this.upsertComment(updatedComment);
                    this.$nextTick(() => lucide.createIcons());
                });

                this.activeFirebaseRef.on('child_removed', (snapshot) => {
                    const deletedComment = this.normalizeComment({
                        ...snapshot.val(),
                        firebase_key: snapshot.key,
                    });
                    this.comments = this.comments.filter(c => c.comment_id !== deletedComment.comment_id);
                    if (this.editingCommentId === deletedComment.comment_id) {
                        this.cancelEditComment();
                    }
                    this.updateItemCommentCount(lostfoundId);
                });
            },

            avatarPlaceholder(name) {
                return `https://ui-avatars.com/api/?name=${encodeURIComponent(name || '?')}&background=64748B&color=fff`;
            },

            isValidComment(comment) {
                return Boolean(
                    comment &&
                    comment.comment_id &&
                    comment.user_id &&
                    comment.commenter_name &&
                    comment.comment
                );
            },

            normalizeComment(comment) {
                return {
                    ...comment,
                    comment_id: comment.comment_id ? String(comment.comment_id).trim() : comment.comment_id,
                    user_id: comment.user_id ? String(comment.user_id).trim() : comment.user_id,
                    update_at: comment.update_at || null,
                };
            },

            upsertComment(comment) {
                const normalized = this.normalizeComment(comment);
                const index = this.comments.findIndex(c => c.comment_id === normalized.comment_id);

                if (index === -1) {
                    this.comments.push(normalized);
                    this.scrollToBottom();
                } else {
                    this.comments = this.comments.map(comment =>
                        comment.comment_id === normalized.comment_id
                            ? { ...comment, ...normalized }
                            : comment
                    );
                }

                if (this.selectedItem) {
                    this.updateItemCommentCount(this.selectedItem.id);
                }
            },

            updateItemCommentCount(lostfoundId) {
                const count = this.displayableComments.length;
                const idx = this.allItems.findIndex(i => i.id === lostfoundId);
                if (idx !== -1) this.allItems[idx].comments_count = count;
                if (this.selectedItem && this.selectedItem.id === lostfoundId) {
                    this.selectedItem.comments_count = count;
                }
            },

            canManageComment(comment) {
                return String(comment.user_id).trim() === this.currentUserId;
            },

            startEditComment(comment) {
                if (!this.canManageComment(comment)) return;

                this.editingCommentId = comment.comment_id;
                this.editingOriginalComment = comment.comment;
                this.newComment = comment.comment;
                this.$nextTick(() => {
                    this.$refs.commentInput.focus();
                    lucide.createIcons();
                });
            },

            cancelEditComment() {
                this.editingCommentId = null;
                this.editingOriginalComment = '';
                this.newComment = '';
                this.$nextTick(() => lucide.createIcons());
            },

            async submitComment() {
                if (!this.newComment.trim() || this.isCommenting) return;

                if (this.editingCommentId) {
                    await this.updateComment();
                    return;
                }

                this.isCommenting = true;
                const tempComment = this.newComment.trim();
                const tempId = `TMP${Date.now()}`;
                this.newComment = '';
                this.upsertComment({
                    comment_id: tempId,
                    lostfound_id: this.selectedItem.id,
                    user_id: this.currentUserId,
                    comment: tempComment,
                    created_at: new Date().toISOString(),
                    update_at: null,
                    commenter_name: this.currentUserName,
                    commenter_photo: this.currentUserPhoto,
                    time_ago: 'Baru saja',
                });
                this.scrollToBottom();
                this.$nextTick(() => lucide.createIcons());

                try {
                    const response = await fetch(`{{ url('/admin/lostfound') }}/${this.selectedItem.id}/comments`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ comment: tempComment })
                    });

                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(result.message || 'Gagal mengirim komentar');
                    }

                    this.comments = this.comments.filter(c => c.comment_id !== tempId);
                    if (result.data) {
                        this.upsertComment(result.data);
                        this.scrollToBottom();
                        this.$nextTick(() => lucide.createIcons());
                    }
                } catch (e) {
                    this.comments = this.comments.filter(c => c.comment_id !== tempId);
                    if (this.selectedItem) this.updateItemCommentCount(this.selectedItem.id);
                    this.showToast(e.message, 'error');
                    this.newComment = tempComment;
                } finally {
                    this.isCommenting = false;
                }
            },

            async updateComment() {
                const commentId = String(this.editingCommentId).trim();
                const newText = this.newComment.trim();
                const index = this.comments.findIndex(c => c.comment_id === commentId);
                if (index === -1) {
                    this.cancelEditComment();
                    return;
                }

                this.isCommenting = true;
                const previousComment = this.comments[index].comment;
                const previousUpdateAt = this.comments[index].update_at || null;
                const optimisticUpdateAt = new Date().toISOString();
                this.comments = this.comments.map(comment =>
                    comment.comment_id === commentId
                        ? { ...comment, comment: newText, update_at: optimisticUpdateAt }
                        : comment
                );
                this.cancelEditComment();

                try {
                    const response = await fetch(`{{ url('/admin/lostfound/comments') }}/${encodeURIComponent(commentId)}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ comment: newText })
                    });

                    const result = await response.json().catch(() => ({}));
                    if (!response.ok && response.status !== 404) {
                        throw new Error(result.message || 'Gagal mengedit komentar');
                    }

                    const updatedComment = result.data
                        ? this.normalizeComment(result.data)
                        : {
                            comment_id: commentId,
                            comment: newText,
                            update_at: optimisticUpdateAt,
                        };

                    this.upsertComment(updatedComment);
                    await this.syncFirebaseSafely(() => this.updateFirebaseComment(commentId, {
                        comment: updatedComment.comment,
                        update_at: updatedComment.update_at || optimisticUpdateAt,
                    }));
                    this.showToast(result.message || 'Komentar diperbarui');
                } catch (e) {
                    this.comments = this.comments.map(comment =>
                        comment.comment_id === commentId
                            ? { ...comment, comment: previousComment, update_at: previousUpdateAt }
                            : comment
                    );
                    this.showToast(e.message, 'error');
                } finally {
                    this.isCommenting = false;
                }
            },

            openDeleteCommentModal(commentId) {
                this.commentToDeleteId = String(commentId).trim();
                this.showDeleteCommentModal = true;
                this.$nextTick(() => lucide.createIcons());
            },

            closeDeleteCommentModal() {
                if (this.isDeletingComment) return;
                this.showDeleteCommentModal = false;
                this.commentToDeleteId = null;
            },

            async confirmDeleteComment() {
                if (!this.commentToDeleteId || this.isDeletingComment) return;
                await this.deleteComment(this.commentToDeleteId);
            },

            async deleteComment(commentId) {
                commentId = String(commentId).trim();
                this.isDeletingComment = true;
                const previousComments = [...this.comments];
                this.comments = this.comments.filter(c => c.comment_id !== commentId);
                if (this.editingCommentId === commentId) this.cancelEditComment();
                if (this.selectedItem) this.updateItemCommentCount(this.selectedItem.id);

                try {
                    const response = await fetch(`{{ url('/admin/lostfound/comments') }}/${encodeURIComponent(commentId)}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json().catch(() => ({}));
                    if(!response.ok && response.status !== 404) {
                        throw new Error(result.message || 'Gagal menghapus komentar');
                    }

                    await this.syncFirebaseSafely(() => this.removeFirebaseComment(commentId));
                    this.showDeleteCommentModal = false;
                    this.commentToDeleteId = null;
                    this.showToast(result.message || 'Komentar dihapus');
                } catch (e) {
                    this.comments = previousComments;
                    if (this.selectedItem) this.updateItemCommentCount(this.selectedItem.id);
                    this.showToast(e.message, 'error');
                } finally {
                    this.isDeletingComment = false;
                }
            },

            async findFirebaseCommentKeys(commentId) {
                if (!this.activeFirebaseRef) return [];

                commentId = String(commentId).trim();
                const cachedKeys = this.comments
                    .filter(comment => comment.comment_id === commentId && comment.firebase_key)
                    .map(comment => comment.firebase_key);

                if (cachedKeys.length > 0) return [...new Set(cachedKeys)];

                const snapshot = await this.activeFirebaseRef.once('value');
                const keys = [];
                snapshot.forEach(childSnapshot => {
                    const value = childSnapshot.val() || {};
                    if (String(value.comment_id || '').trim() === commentId) {
                        keys.push(childSnapshot.key);
                    }
                });

                return keys;
            },

            async syncFirebaseSafely(callback) {
                try {
                    await callback();
                } catch (error) {
                    console.warn('Firebase comment sync failed', error);
                }
            },

            firebaseChildRef(key) {
                return database.ref(`${this.activeFirebasePath}/${key}`);
            },

            async updateFirebaseComment(commentId, payload) {
                if (!this.activeFirebaseRef || !this.activeFirebasePath) return;

                const keys = await this.findFirebaseCommentKeys(commentId);
                await Promise.all(keys.map(key => this.firebaseChildRef(key).update(payload)));
            },

            async removeFirebaseComment(commentId) {
                if (!this.activeFirebaseRef || !this.activeFirebasePath) return;

                const keys = await this.findFirebaseCommentKeys(commentId);
                await Promise.all(keys.map(key => this.firebaseChildRef(key).set(null)));
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('comments-container');
                    if(container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },
            openResolveModal(i)  { this.selectedItem = i; this.showResolveModal = true; },
            openDeleteModal(i)   { this.selectedItem = i; this.showDeleteModal = true; },

            handleFileUpload(event) { this.newItem.photo = event.target.files[0]; },

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type    = type;
                this.toast.show    = true;
                setTimeout(() => {
                    this.toast.show = false;
                    this.$nextTick(() => lucide.createIcons());
                }, 3000);
            },

            async addItem() {
                this.loading = true;
                const formData = new FormData();
                formData.append('item_name',   this.newItem.item_name);
                formData.append('description', this.newItem.description);
                formData.append('location',    this.newItem.location);
                formData.append('status',      this.newItem.status);
                if (this.newItem.photo) formData.append('photo', this.newItem.photo);

                try {
                    const response = await fetch('{{ route('admin.lostfound.store') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                } catch {
                    this.showToast('Terjadi kesalahan sistem', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async confirmResolve() {
                this.loading = true;
                try {
                    const response = await fetch(
                        `{{ url('/admin/lostfound') }}/${this.selectedItem.id}/resolve`, {
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
                            if (this.currentUserRole === 'satpam') {
                                this.allItems.splice(index, 1);
                            } else {
                                this.allItems[index].status       = 'claimed';
                                this.allItems[index].status_label = 'Diklaim';
                                this.allItems[index].status_class = 'status-warning';
                            }
                        }
                        this.showResolveModal = false;
                        this.showDetailModal  = false;
                        this.showToast(result.message);
                    } else {
                        this.showToast(result.message || 'Gagal memperbarui status', 'error');
                    }
                } catch {
                    this.showToast('Gagal memperbarui status', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async confirmDelete() {
                this.loading = true;
                try {
                    const response = await fetch(
                        `{{ url('/admin/lostfound') }}/${this.selectedItem.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const result = await response.json();
                    if (response.ok) {
                        this.allItems = this.allItems.filter(i => i.id !== this.selectedItem.id);
                        this.showDeleteModal = false;
                        this.showToast(result.message);
                    }
                } catch {
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
