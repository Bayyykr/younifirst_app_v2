<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Lost & Found - Younifirst</title>
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
            border-bottom: 2px solid #F59E0B;
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
            border-left: 3px solid #F59E0B;
            padding-left: 8px;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-cell {
            width: 25%;
            padding: 10px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            text-align: center;
        }
        .stats-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748B;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .stats-value {
            font-size: 18px;
            font-weight: 700;
            color: #F59E0B;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.data-table th {
            background-color: #F59E0B;
            color: #FFFFFF;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 10px;
            border: 1px solid #F59E0B;
            text-align: left;
        }
        table.data-table td {
            padding: 8px 10px;
            border: 1px solid #E2E8F0;
            font-size: 10px;
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
        .badge-lost { background: #FEE2E2; color: #B91C1C; }
        .badge-found { background: #FEF3C7; color: #D97706; }
        .badge-resolved { background: #DCFCE7; color: #15803D; }

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
                <p>Laporan Rekapitulasi Data Barang Lost & Found</p>
            </td>
            <td class="meta-td">
                <strong>Dicetak Oleh:</strong> {{ $printedBy->name }} ({{ ucfirst($printedBy->role) }})<br>
                <strong>Tanggal Cetak:</strong> {{ $printedDate }}<br>
                <strong>Format:</strong> PDF Document
            </td>
        </tr>
    </table>

    <div class="section-title">Ringkasan Barang Lost & Found</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-cell">
                <div class="stats-label">Total Laporan</div>
                <div class="stats-value" style="color: #3B82F6;">{{ $stats['total'] }}</div>
            </td>
            <td class="stats-cell" style="margin-left: 10px;">
                <div class="stats-label">Belum Ditemukan (Lost)</div>
                <div class="stats-value" style="color: #EF4444;">{{ $stats['lost'] }}</div>
            </td>
            <td class="stats-cell" style="margin-left: 10px;">
                <div class="stats-label">Ditemukan (Found)</div>
                <div class="stats-value" style="color: #F59E0B;">{{ $stats['found'] }}</div>
            </td>
            <td class="stats-cell" style="margin-left: 10px;">
                <div class="stats-label">Selesai / Dikembalikan</div>
                <div class="stats-value" style="color: #10B981;">{{ $stats['resolved'] }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Daftar Barang Lost & Found ({{ $filterLabel ?? 'Belum Ditemukan & Selesai' }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Barang</th>
                <th style="width: 20%;">Dilaporkan Oleh</th>
                <th style="width: 20%;">Lokasi Kejadian</th>
                <th style="width: 18%;">Tanggal Laporan</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->item_name }}</strong><br>
                    <span style="font-size: 8px; color: #64748B;">{{ Str::limit($item->description, 50) }}</span>
                </td>
                <td>{{ $item->user->name ?? 'Pengguna' }}</td>
                <td>{{ $item->location }}</td>
                <td>{{ $item->created_at->format('d M Y H:i') }} WIB</td>
                <td>
                    <span class="badge badge-{{ strtolower($item->status) }}">
                        {{ $item->status === 'lost' ? 'Lost' : ($item->status === 'found' ? 'Found' : 'Resolved') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">Tidak ada data barang hilang/ditemukan.</td>
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
