<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Baru Menunggu Persetujuan</title>
    <style>
        body {
            font-family: 'Inter', Helvetica, Arial, sans-serif;
            background-color: #F8FAFC;
            color: #334155;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #E2E8F0;
        }
        .header {
            background: linear-gradient(135deg, #3B82F6, #1D4ED8);
            padding: 32px;
            text-align: center;
            color: #FFFFFF;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 8px 0;
            font-weight: 700;
        }
        .header p {
            font-size: 14px;
            margin: 0;
            opacity: 0.9;
        }
        .content {
            padding: 32px;
        }
        .item-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .item-title {
            font-size: 16px;
            font-weight: 700;
            color: #1E293B;
            margin: 0 0 12px 0;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .detail-row strong {
            width: 140px;
            color: #64748B;
            flex-shrink: 0;
        }
        .detail-row span {
            color: #334155;
        }
        .btn-wrapper {
            text-align: center;
            margin-top: 24px;
        }
        .btn-primary {
            display: inline-block;
            background-color: #3B82F6;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
            transition: background-color 0.2s;
        }
        .footer {
            background-color: #F1F5F9;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #94A3B8;
            border-top: 1px solid #E2E8F0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Younifirst Admin Notification</h1>
            <p>Ada postingan event baru yang membutuhkan persetujuan Anda</p>
        </div>
        <div class="content">
            <div class="item-card">
                <div class="item-title">{{ $event->name_event }}</div>
                <div class="detail-row">
                    <strong>Penyelenggara:</strong>
                    <span>{{ $event->organizer }}</span>
                </div>
                <div class="detail-row">
                    <strong>Tanggal Mulai:</strong>
                    <span>{{ \Carbon\Carbon::parse($event->start_date)->translatedFormat('d M Y H:i') }} WIB</span>
                </div>
                <div class="detail-row">
                    <strong>Kategori:</strong>
                    <span>{{ $event->category->name_category ?? '-' }}</span>
                </div>
            </div>
            
            <p style="font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">
                Silakan masuk ke Dashboard Admin Younifirst untuk meninjau detail lengkap dan memutuskan persetujuan postingan ini.
            </p>

            <div class="btn-wrapper">
                <a href="{{ url('/admin/events') }}" class="btn-primary">Tinjau Event</a>
            </div>
        </div>
        <div class="footer">
            Sistem Notifikasi Otomatis Younifirst &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
