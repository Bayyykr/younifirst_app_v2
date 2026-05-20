<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring Tim Kompetisi - Younifirst</title>
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
            border-bottom: 2px solid #8B5CF6;
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
            border-left: 3px solid #8B5CF6;
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
            color: #8B5CF6;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.data-table th {
            background-color: #8B5CF6;
            color: #FFFFFF;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 10px;
            border: 1px solid #8B5CF6;
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
        .badge-kampus { background: #EFF6FF; color: #1D4ED8; }
        .badge-regional { background: #ECFDF5; color: #047857; }
        .badge-nasional { background: #FFF7ED; color: #C2410C; }
        .badge-internasional { background: #F5F3FF; color: #6D28D9; }
        
        .footer-section {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }
        .info-cell {
            width: 70%;
            font-size: 10px;
            color: #64748B;
            vertical-align: top;
        }
        .signature-cell {
            width: 30%;
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
        .member-list {
            margin: 0;
            padding-left: 12px;
        }
        .member-item {
            font-size: 9px;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    @php
    function get_image_base64($path) {
        if (!$path) return null;
        $fullPath = public_path('storage/' . $path);
        if (!file_exists($fullPath)) return null;
        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        try {
            $data = file_get_contents($fullPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        } catch (\Exception $e) {
            return null;
        }
    }
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-title-td">
                <h1>Younifirst</h1>
                <p>Laporan Hasil Lomba & Monitoring Tim Kompetisi</p>
            </td>
            <td class="meta-td">
                <strong>Dicetak Oleh:</strong> {{ $printedBy->name }} ({{ ucfirst($printedBy->role) }})<br>
                <strong>Tanggal Cetak:</strong> {{ $printedDate }}<br>
                <strong>Format:</strong> PDF Document (Landscape)
            </td>
        </tr>
    </table>

    <div class="section-title">Ringkasan Prestasi Tim</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-cell">
                <div class="stats-label">Total Laporan Tim</div>
                <div class="stats-value">{{ $stats['total'] }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Tingkat Kampus</div>
                <div class="stats-value" style="color: #1D4ED8;">{{ $stats['kampus'] }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Tingkat Regional</div>
                <div class="stats-value" style="color: #047857;">{{ $stats['regional'] }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Tingkat Nasional</div>
                <div class="stats-value" style="color: #C2410C;">{{ $stats['nasional'] }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Internasional</div>
                <div class="stats-value" style="color: #6D28D9;">{{ $stats['internasional'] }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Detail Laporan Tim Kompetisi ({{ $filterLabel ?? 'Semua Tingkat' }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 14%;">Nama Team</th>
                <th style="width: 22%;">Nama Anggota & NIM</th>
                <th style="width: 15%;">Nama Lomba</th>
                <th style="width: 12%;">Tingkat Lomba</th>
                <th style="width: 11%;">Juara ke Berapa</th>
                <th style="width: 11%;">Foto Kegiatan</th>
                <th style="width: 11%;">Gambar Sertifikat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teams as $index => $team)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $team->team_name }}</strong></td>
                <td>
                    <ol class="member-list">
                        @forelse($team->members as $member)
                            @if($member->user)
                                <li class="member-item">
                                    {{ $member->user->name }} 
                                    <span style="color: #64748B;">({{ $member->user->nim ?? '-' }})</span>
                                    @if($member->role === 'leader')
                                        <strong style="color: #8B5CF6;">[Leader]</strong>
                                    @endif
                                </li>
                            @endif
                        @empty
                            <span style="color: #94A3B8; font-style: italic;">Tidak ada anggota aktif</span>
                        @endforelse
                    </ol>
                </td>
                <td>{{ $team->competition_name }}</td>
                <td>
                    <span class="badge badge-{{ strtolower($team->competition_level) }}">
                        {{ ucfirst($team->competition_level) }}
                    </span>
                </td>
                <td><strong>{{ $team->achievement_rank }}</strong></td>
                <td style="text-align: center;">
                    @php $photoAct = get_image_base64($team->photo_activity); @endphp
                    @if($photoAct)
                        <img src="{{ $photoAct }}" style="max-height: 45px; max-width: 70px; border-radius: 4px; border: 1px solid #E2E8F0;">
                    @else
                        <span style="color: #94A3B8; font-style: italic; font-size: 8px;">Tidak ada foto</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    @php $photoCert = get_image_base64($team->photo_certificate); @endphp
                    @if($photoCert)
                        <img src="{{ $photoCert }}" style="max-height: 45px; max-width: 70px; border-radius: 4px; border: 1px solid #E2E8F0;">
                    @else
                        <span style="color: #94A3B8; font-style: italic; font-size: 8px;">Tidak ada sertifikat</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada data tim kompetisi ditemukan.</td>
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
                    <div style="height: 50px; text-align: center; margin-bottom: 5px;">
                        <img src="{{ $signatureBase64 }}" style="max-height: 50px; max-width: 150px; display: inline-block;">
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
