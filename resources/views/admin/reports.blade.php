@extends('layouts.admin')

@section('title', 'Export Laporan')

@push('styles')
<style>
    .reports-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .reports-header {
        margin-bottom: 2.5rem;
    }

    .reports-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0 0 0.5rem 0;
    }

    .reports-header p {
        font-size: 1rem;
        color: var(--text-muted);
        margin: 0;
    }

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .report-card {
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
    }

    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    }

    .report-form {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .card-top {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .icon-wrapper {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .icon-users {
        background: #EFF6FF;
        color: #3B82F6;
    }

    .icon-events {
        background: #ECFDF5;
        color: #10B981;
    }

    .icon-lostfound {
        background: #FFF7ED;
        color: #F59E0B;
    }

    .icon-teams {
        background: #F5F3FF;
        color: #8B5CF6;
    }

    .card-info h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0 0 0.25rem 0;
    }

    .card-info p {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.4;
    }

    .card-bullets {
        margin-bottom: 1.5rem;
    }

    .bullet-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .bullet-item {
        font-size: 0.875rem;
        color: var(--text-muted);
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .bullet-item i,
    .bullet-item svg{
        flex-shrink: 0;
        margin-top: 2px;
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0 !important;
        display: inline-block;
        margin-top: 3px;
    }

    .bullet-item-users i { color: #3B82F6; }
    .bullet-item-events i { color: #10B981; }
    .bullet-item-lostfound i { color: #F59E0B; }
    .bullet-item-teams i { color: #8B5CF6; }

    /* Filters styling */
    .filter-section {
        margin: 0 0 2rem 0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .filter-title {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .filter-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-muted);
    }

    .filter-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: var(--bg-white);
        color: var(--text-main);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    html.dark .filter-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }

    .filter-select:focus {
        outline: none;
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .filter-title-users { color: #2563EB; }
    .filter-title-events { color: #10B981; }
    .filter-title-lostfound { color: #F59E0B; }
    .filter-title-teams { color: #7C3AED; }

    .btn-export {
        width: 100%;
        padding: 0.875rem 1rem;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 600;
        border: none;
        background: #2563EB;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: background 0.2s, transform 0.1s;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);
    }

    .btn-export:hover {
        background: #1D4ED8;
    }

    .btn-export:active {
        transform: scale(0.98);
    }

    /* Footer Summary Row */
    .summary-section {
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 2rem;
        display: grid;
        grid-template-columns: 1.2fr 1fr 1.2fr;
        gap: 2.5rem;
        align-items: start;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }

    @media (max-width: 900px) {
        .summary-section {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }

    .summary-col {
        display: flex;
        flex-direction: column;
    }

    .info-box {
        display: flex;
        gap: 1rem;
    }

    .info-icon {
        color: #2563EB;
        flex-shrink: 0;
        margin-top: 3px;
    }

    .info-text h4 {
        font-size: 1rem;
        font-weight: 700;
        color: #2563EB;
        margin: 0 0 0.5rem 0;
    }

    .info-text p {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.5;
    }

    /* Signature Box styling */
    .signature-container {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
    }

    .signature-box {
        width: 100%;
        max-width: 260px;
        border: 1px dashed #CBD5E1;
        border-radius: 12px;
        padding: 1.25rem;
        background: #FAFAFA;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 96px;
        text-align: center;
        position: relative;
        transition: all 0.2s ease-in-out;
        overflow: hidden;
    }

    html.dark .signature-box {
        background: #1E293B;
        border-color: #475569;
    }

    .signature-box:not(.has-signature):hover {
        background: #F1F5F9;
        border-color: #3B82F6;
    }

    html.dark .signature-box:not(.has-signature):hover {
        background: #2D3748;
        border-color: #60A5FA;
    }

    .signature-preview-wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .signature-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        color: #FFFFFF;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 11px;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
        cursor: pointer;
    }

    .signature-box.has-signature:hover .signature-hover-overlay {
        opacity: 1;
    }

    .uploading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.85);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #3B82F6;
        z-index: 10;
        border-radius: 11px;
    }

    html.dark .uploading-overlay {
        background: rgba(30, 41, 59, 0.85);
        color: #60A5FA;
    }

    .spinner {
        width: 24px;
        height: 24px;
        border: 3px solid rgba(59, 130, 246, 0.2);
        border-radius: 50%;
        border-top-color: #3B82F6;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="reports-container">

    <div class="reports-header">
        <h1>Export Laporan</h1>
        <p>Unduh laporan data dari modul sistem Younifirst.</p>
    </div>

    <div class="reports-grid">

        <!-- User Management Card -->
        <div class="report-card">
            <form method="POST" action="{{ route('admin.reports.export') }}" class="report-form">
                @csrf
                <input type="hidden" name="type" value="users">
                <input type="hidden" name="format" value="pdf">

                <div>
                    <div class="card-top">
                        <div class="icon-wrapper icon-users">
                            <i data-lucide="user" style="width: 26px; height: 26px;"></i>
                        </div>
                        <div class="card-info">
                            <h3>User Management</h3>
                            <p>Laporan data mahasiswa dan laporan tindakan moderasi akun.</p>
                        </div>
                    </div>

                    <div class="card-bullets">
                        <ul class="bullet-list">
                            <li class="bullet-item bullet-item-users">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Data mahasiswa (semua status)</span>
                            </li>
                            <li class="bullet-item bullet-item-users">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Laporan tindakan moderasi (suspended)</span>
                            </li>
                            <li class="bullet-item bullet-item-users">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Alasan dan catatan internal admin</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="filter-section">
                        <div class="filter-title filter-title-users">FILTER</div>

                        <div class="filter-group">
                            <label class="filter-label">Status Akun</label>
                            <select name="status_akun" class="filter-select">
                                <option value="all">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="suspended">Suspended</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-export">
                        <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                        Export PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Event Management Card -->
        <div class="report-card">
            <form method="POST" action="{{ route('admin.reports.export') }}" class="report-form">
                @csrf
                <input type="hidden" name="type" value="events">
                <input type="hidden" name="format" value="pdf">

                <div>
                    <div class="card-top">
                        <div class="icon-wrapper icon-events">
                            <i data-lucide="calendar" style="width: 26px; height: 26px;"></i>
                        </div>
                        <div class="card-info">
                            <h3>Event Management</h3>
                            <p>Laporan data dan aktivitas event yang sedang berlangsung maupun telah selesai.</p>
                        </div>
                    </div>

                    <div class="card-bullets">
                        <ul class="bullet-list">
                            <li class="bullet-item bullet-item-events">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Total Event</span>
                            </li>
                            <li class="bullet-item bullet-item-events">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Detail event (nama, tanggal, kategori, penyelenggara, peserta, status)</span>
                            </li>
                            <li class="bullet-item bullet-item-events">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Total interaksi pada postingan event (jumlah suka)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="filter-section">
                        <div class="filter-title filter-title-events">FILTER</div>

                        <div class="filter-group">
                            <label class="filter-label">Kategori</label>
                            <select name="category_id" class="filter-select">
                                <option value="all">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}">{{ $category->name_category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Periode (berdasarkan tanggal mulai & selesai)</label>
                            <select name="periode" class="filter-select">
                                <option value="all">Semua Status</option>
                                <option value="today">Hari Ini</option>
                                <option value="week">Minggu Ini</option>
                                <option value="month">Bulan Ini</option>
                                <option value="year">Tahun Ini</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-export" style="background: #10B981; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);">
                        <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                        Export PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Lost and Found Card -->
        <div class="report-card">
            <form method="POST" action="{{ route('admin.reports.export') }}" class="report-form">
                @csrf
                <input type="hidden" name="type" value="lostfound">
                <input type="hidden" name="format" value="pdf">

                <div>
                    <div class="card-top">
                        <div class="icon-wrapper icon-lostfound">
                            <i data-lucide="search" style="width: 26px; height: 26px;"></i>
                        </div>
                        <div class="card-info">
                            <h3>Lost and Found</h3>
                            <p>Laporan data barang. Fokus pada kasus yang belum selesai.</p>
                        </div>
                    </div>

                    <div class="card-bullets">
                        <ul class="bullet-list">
                            <li class="bullet-item bullet-item-lostfound">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Data barang (hilang / ditemukan / selesai)</span>
                            </li>
                            <li class="bullet-item bullet-item-lostfound">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Fokus: kasus belum selesai (hilang & ditemukan)</span>
                            </li>
                            <li class="bullet-item bullet-item-lostfound">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Lokasi dan tanggal laporan</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="filter-section">
                        <div class="filter-title filter-title-lostfound">FILTER</div>

                        <div class="filter-group">
                            <label class="filter-label">Status Laporan</label>
                            <select name="status_laporan" class="filter-select">
                                <option value="unresolved" selected>Belum Selesai (Hilang & Ditemukan)</option>
                                <option value="all">Semua Status</option>
                                <option value="lost">Hilang (Lost)</option>
                                <option value="found">Ditemukan (Found)</option>
                                <option value="resolved">Selesai (Resolved)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-export" style="background: #F59E0B; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.15);">
                        <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                        Export PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Team Monitoring Card -->
        <div class="report-card">
            <form method="POST" action="{{ route('admin.reports.export') }}" class="report-form">
                @csrf
                <input type="hidden" name="type" value="teams">
                <input type="hidden" name="format" value="pdf">

                <div>
                    <div class="card-top">
                        <div class="icon-wrapper icon-teams">
                            <i data-lucide="users" style="width: 26px; height: 26px;"></i>
                        </div>
                        <div class="card-info">
                            <h3>Team Monitoring</h3>
                            <p>Laporan data tim monitoring kompetisi yang sudah mengunggah laporan hasil lomba beserta nama anggota dan NIM.</p>
                        </div>
                    </div>

                    <div class="card-bullets">
                        <ul class="bullet-list">
                            <li class="bullet-item bullet-item-teams">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Daftar tim kompetisi yang telah selesai</span>
                            </li>
                            <li class="bullet-item bullet-item-teams">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Tingkat kompetisi dan prestasi yang diraih</span>
                            </li>
                            <li class="bullet-item bullet-item-teams">
                                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                <span>Detail nama anggota tim beserta NIM</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="filter-section">
                        <div class="filter-title filter-title-teams">FILTER</div>

                        <div class="filter-group">
                            <label class="filter-label">Tingkat Lomba</label>
                            <select name="tingkat_lomba" class="filter-select">
                                <option value="all">Semua Tingkat</option>
                                <option value="kampus">Kampus</option>
                                <option value="regional">Regional</option>
                                <option value="nasional">Nasional</option>
                                <option value="internasional">Internasional</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-export" style="background: #8B5CF6; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.15);">
                        <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                        Export PDF
                    </button>
                </div>
            </form>
        </div>

    </div> <!-- end reports-grid -->

    <!-- Footer Summary Row -->
    <div class="summary-section">

        <!-- Information Col -->
        <div class="summary-col">
            <div class="info-box">
                <div class="info-icon">
                    <i data-lucide="info" style="width: 24px; height: 24px;"></i>
                </div>
                <div class="info-text">
                    <h4>Informasi Laporan</h4>
                    <p>Semua laporan akan digenerate otomatis dalam format PDF. Pastikan data yang ingin diekspor sudah sesuai.</p>
                </div>
            </div>
        </div>

        <!-- Cetak Oleh Col -->
        <div class="summary-col">
            <div class="print-meta-box">
                <h4 style="color: var(--text-main); font-weight: 700; font-size: 1rem; text-transform: none; letter-spacing: normal; margin-bottom: 0.75rem;">Diekspor oleh:</h4>
                <div class="meta-user-info" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <img src="{{ auth()->user()->photo_url }}" class="meta-avatar" alt="{{ auth()->user()->name }}" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">
                    <div class="meta-name-tag" style="display: flex; align-items: center; gap: 0.5rem; flex-direction: row;">
                        <span class="meta-name" style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);">{{ auth()->user()->name }}</span>
                        <span class="meta-badge" style="font-size: 0.75rem; font-weight: 600; padding: 2px 8px; background: #EFF6FF; color: #2563EB; border-radius: 9999px;">Admin</span>
                    </div>
                </div>
                <div class="meta-date" style="font-size: 0.875rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                    <span>{{ now()->translatedFormat('d M Y H.i') }} WIB</span>
                </div>
            </div>
        </div>

        <!-- Tanda Tangan Col -->
        <div class="summary-col signature-container" x-data="signatureUpload({
            initialUrl: '{{ $signatureUrl }}',
            uploadUrl: '{{ route('admin.reports.upload-signature') }}',
            deleteUrl: '{{ route('admin.reports.delete-signature') }}',
            csrf: '{{ csrf_token() }}'
        })">
            <h4 style="color: var(--text-main); font-weight: 700; font-size: 1rem; text-transform: none; letter-spacing: normal; margin-bottom: 0.5rem; width: 100%;">Tanda Tangan Admin</h4>
            <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4; margin: 0 0 1rem 0; width: 100%;">Unggah tanda tangan menggunakan tinta hitam dengan latar belakang putih atau transparan.</p>

            <div class="signature-box"
                 :class="{ 'has-signature': signatureUrl, 'is-uploading': uploading }"
                 @click="signatureUrl ? null : $refs.fileInput.click()"
                 style="cursor: pointer; position: relative;">

                 <!-- Hidden File Input -->
                 <input type="file" x-ref="fileInput" accept="image/*" @change="uploadFile" style="display: none;">

                 <!-- Loading Overlay -->
                 <div x-show="uploading" style="display: none;" class="uploading-overlay">
                     <div class="spinner"></div>
                     <span style="margin-top: 0.25rem;">Mengunggah...</span>
                 </div>

                 <!-- State 1: Has Signature -->
                 <div x-show="signatureUrl" style="display: none;" class="signature-preview-wrapper">
                     <img :src="signatureUrl" alt="Tanda Tangan Admin" style="max-height: 60px; max-width: 100%; object-fit: contain;">

                     <!-- Hover Action Overlay -->
                     <div class="signature-hover-overlay" @click.stop="deleteSignature">
                         <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                         <span>Hapus Tanda Tangan</span>
                     </div>
                 </div>

                 <!-- State 2: No Signature -->
                 <div x-show="!signatureUrl" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem;">
                     <div class="upload-icon-wrapper" style="color: #64748B;">
                         <i data-lucide="upload" style="width: 24px; height: 24px;"></i>
                     </div>
                     <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-main);">Upload Tanda Tangan</div>
                     <div style="font-size: 0.75rem; color: var(--text-muted);">Format jpg/jpeg/png, Maks 2 MB</div>
                 </div>
            </div>
        </div>

    </div> <!-- end summary-section -->

</div>
@endsection

@push('scripts')
<script>
    function signatureUpload(config) {
        return {
            signatureUrl: config.initialUrl,
            uploading: false,
            uploadFile(e) {
                const file = e.target.files[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal adalah 2 MB.');
                    return;
                }

                this.uploading = true;
                const formData = new FormData();
                formData.append('signature', file);

                fetch(config.uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': config.csrf
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.uploading = false;
                    if (data.status === 'success') {
                        this.signatureUrl = data.url;
                        setTimeout(() => {
                            if (window.lucide) {
                                window.lucide.createIcons();
                            }
                        }, 50);
                    } else {
                        alert(data.message || 'Gagal mengunggah tanda tangan.');
                    }
                })
                .catch(err => {
                    this.uploading = false;
                    console.error(err);
                    alert('Terjadi kesalahan saat mengunggah.');
                });
            },
            deleteSignature() {
                if (!confirm('Apakah Anda yakin ingin menghapus tanda tangan ini?')) return;

                this.uploading = true;
                fetch(config.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': config.csrf
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.uploading = false;
                    if (data.status === 'success') {
                        this.signatureUrl = null;
                        if (this.$refs.fileInput) {
                            this.$refs.fileInput.value = '';
                        }
                        setTimeout(() => {
                            if (window.lucide) {
                                window.lucide.createIcons();
                            }
                        }, 50);
                    } else {
                        alert(data.message || 'Gagal menghapus tanda tangan.');
                    }
                })
                .catch(err => {
                    this.uploading = false;
                    console.error(err);
                    alert('Terjadi kesalahan saat menghapus.');
                });
            }
        };
    }
</script>
@endpush
