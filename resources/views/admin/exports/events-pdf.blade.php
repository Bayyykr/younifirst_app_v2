<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Event Management - Younifirst</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #10B981;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo-title-td {
            text-align: left;
        }
        .logo-title-td h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1E293B;
            margin: 0 0 5px 0;
            letter-spacing: -0.5px;
        }
        .logo-title-td p {
            font-size: 12px;
            color: #64748B;
            margin: 0;
        }
        .meta-td {
            text-align: right;
            vertical-align: bottom;
            font-size: 10px;
            color: #64748B;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1E293B;
            margin: 25px 0 12px 0;
            border-left: 3px solid #10B981;
            padding-left: 8px;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-cell {
            width: 20%;
            padding: 10px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            text-align: center;
        }
        .stats-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748B;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .stats-value {
            font-size: 16px;
            font-weight: 700;
            color: #10B981;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.data-table th {
            background-color: #10B981;
            color: #FFFFFF;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 10px;
            border: 1px solid #10B981;
            text-align: left;
        }
        table.data-table td {
            padding: 8px 10px;
            border: 1px solid #E2E8F0;
            font-size: 9.5px;
            color: #334155;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-approved { background: #DCFCE7; color: #15803D; }
        .badge-pending { background: #FEF3C7; color: #D97706; }
        .badge-rejected { background: #FEE2E2; color: #B91C1C; }

        .footer-section {
            margin-top: 40px;
            width: 100%;
        }
        .info-cell {
            width: 60%;
            font-size: 10px;
            color: #64748B;
            vertical-align: top;
        }
        .signature-cell {
            width: 40%;
            text-align: center;
            vertical-align: top;
        }
        .signature-title {
            font-size: 10px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 30px;
        }
        .signature-graphic {
            font-family: 'Times New Roman', Times, serif;
            font-style: italic;
            font-weight: bold;
            font-size: 24px;
            color: #1E293B;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .signature-name {
            font-size: 10px;
            font-weight: 600;
            color: #1E293B;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo-title-td">
                <h1>Younifirst</h1>
                <p>Laporan Rekapitulasi Kegiatan & Event Mahasiswa</p>
            </td>
            <td class="meta-td">
                <strong>Dicetak Oleh:</strong> {{ $printedBy->name }} ({{ ucfirst($printedBy->role) }})<br>
                <strong>Tanggal Cetak:</strong> {{ $printedDate }}<br>
                <strong>Format:</strong> PDF Document
            </td>
        </tr>
    </table>

    <div class="section-title">Rekap Statistik Kegiatan (Selesai & Berjalan)</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-cell">
                <div class="stats-label">Total Event</div>
                <div class="stats-value" style="color: #3B82F6;">{{ $stats['total'] }}</div>
            </td>
            <td class="stats-cell" style="margin-left: 8px;">
                <div class="stats-label">Approved</div>
                <div class="stats-value" style="color: #10B981;">{{ $stats['approved'] }}</div>
            </td>
            <td class="stats-cell" style="margin-left: 8px;">
                <div class="stats-label">Menunggu Review</div>
                <div class="stats-value" style="color: #F59E0B;">{{ $stats['pending'] }}</div>
            </td>
            <td class="stats-cell" style="margin-left: 8px;">
                <div class="stats-label">Ditolak</div>
                <div class="stats-value" style="color: #EF4444;">{{ $stats['rejected'] }}</div>
            </td>
            <td class="stats-cell" style="margin-left: 8px;">
                <div class="stats-label">Event Selesai</div>
                <div class="stats-value" style="color: #6366F1;">{{ $stats['finished'] }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Detail Kegiatan & Event Mahasiswa</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Event</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 20%;">Penyelenggara / Oleh</th>
                <th style="width: 20%;">Tanggal & Waktu</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $index => $event)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $event->title }}</strong><br>
                    <span style="font-size: 8px; color: #64748B;">Lokasi: {{ $event->location }}</span>
                </td>
                <td>{{ $event->category->name_category ?? 'Lain-lain' }}</td>
                <td>{{ $event->creator->name ?? 'User' }}</td>
                <td>
                    {{ $event->start_date->format('d M Y') }} - <br>
                    {{ $event->end_date->format('d M Y') }}
                </td>
                <td>
                    <span class="badge badge-{{ strtolower($event->status) }}">
                        {{ $event->status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">Tidak ada data event ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Statistik Partisipasi Event Berdasarkan Kategori</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">No</th>
                <th style="width: 50%;">Nama Kategori</th>
                <th style="width: 40%; text-align: center;">Jumlah Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categoryStats as $index => $cat)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $cat['name'] }}</strong></td>
                <td style="text-align: center;">{{ $cat['count'] }} Event</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center;">Tidak ada statistik kategori tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-section">
        <tr>
            <td class="info-cell">
                <strong>Informasi Hukum & Kerahasiaan</strong><br>
                Laporan ini dibuat secara otomatis oleh sistem administrasi Younifirst. Data yang terkandung di dalam dokumen ini bersifat rahasia dan dilindungi oleh kebijakan privasi perguruan tinggi. Dilarang menyebarluaskan dokumen ini tanpa persetujuan tertulis dari pihak berwenang.
            </td>
            <td class="signature-cell">
                <div class="signature-title">Tanda Tangan Admin</div>
                @if($signatureBase64)
                    <div style="height: 60px; text-align: center; margin-bottom: 5px;">
                        <img src="{{ $signatureBase64 }}" style="max-height: 60px; max-width: 150px; display: inline-block;">
                    </div>
                @else
                    <div class="signature-graphic">{{ $printedBy->name }}</div>
                @endif
                <div class="signature-name">( {{ $printedBy->name }} )</div>
                <div style="font-size: 8px; color: #64748B; margin-top: 3px;">Admin Younifirst</div>
            </td>
        </tr>
    </table>

</body>
</html>
