@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-wrapper">
    <!-- Top Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon-wrapper blue">
                <i data-lucide="users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $totalUsers }}</span>
                <span class="stat-label">Total Pengguna</span>
                <span class="stat-trend blue">+{{ $usersThisWeek }} minggu ini <i data-lucide="trending-up"></i></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper green">
                <i data-lucide="calendar"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $totalEvents }}</span>
                <span class="stat-label">Total Event</span>
                <span class="stat-trend green">+{{ $pendingEvents }} pending <i data-lucide="trending-up"></i></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper orange">
                <i data-lucide="users-2"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $totalTeams }}</span>
                <span class="stat-label">Total Tim</span>
                <span class="stat-trend orange">{{ $openTeams }} total tim open <i data-lucide="trending-up"></i></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper yellow">
                <i data-lucide="search"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $totalLF }}</span>
                <span class="stat-label">Lost and Found</span>
                <span class="stat-trend yellow">{{ $resolvedLF }} ditandai selesai <i data-lucide="trending-up"></i></span>
            </div>
        </div>
    </div>

    <!-- Alert Bar -->
    <div class="alert-bar">
        <div class="alert-content">
            <i data-lucide="clock" class="alert-icon"></i>
            <span><strong>{{ $pendingEvents }}</strong> Event menunggu persetujuan & <strong>{{ $pendingTeams }}</strong> tim menunggu persetujuan - <strong>{{ $unresolvedLF }}</strong> item lost & found belum selesai</span>
        </div>
    </div>

    <!-- Main Charts Row -->
    <div class="charts-main-grid">
        <div class="chart-container large">
            <div class="chart-header">
                <h3>Pertumbuhan Pengguna, Event & Tim</h3>
            </div>
            <div id="growthChart"></div>
        </div>

        <div class="chart-container small">
            <div class="chart-header">
                <h3>Kategori Event</h3>
            </div>
            <div id="categoryChart"></div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="bottom-grid">
        <div class="chart-container">
            <div class="chart-header">
                <h3>Status Tim</h3>
            </div>
            <div id="teamStatusChart"></div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h3>Status Lost & Found</h3>
            </div>
            <div id="lostFoundStatusChart"></div>
        </div>

        <div class="activity-container">
            <div class="activity-header">
                <h3>Aktivitas Terbaru</h3>
            </div>
            <div class="activity-list">
                @forelse($activities as $activity)
                <div class="activity-item">
                    <div class="activity-icon {{ $activity['type'] == 'event' ? 'green' : ($activity['type'] == 'team' ? 'orange' : 'yellow') }}">
                        <i data-lucide="{{ $activity['type'] == 'event' ? 'calendar' : ($activity['type'] == 'team' ? 'users-2' : 'search') }}"></i>
                    </div>
                    <div class="activity-info">
                        <p>
                            @if($activity['type'] == 'event')
                                Event <strong>{{ $activity['title'] }}</strong> {{ $activity['status'] == 'pending' ? 'menunggu persetujuan' : 'disubmit oleh ' . $activity['user'] }}
                            @elseif($activity['type'] == 'team')
                                Tim <strong>{{ $activity['title'] }}</strong> {{ $activity['status'] == 'pending' ? 'menunggu persetujuan' : 'telah terdaftar' }}
                            @else
                                Item <strong>{{ $activity['title'] }}</strong> dilaporkan {{ $activity['status'] }}
                            @endif
                        </p>
                        <span>{{ $activity['created_at']->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center" style="font-size: 13px; margin-top: 20px;">Belum ada aktivitas terbaru.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Announcements Section -->
    <div class="announcements-card">
        <div class="announcements-header">
            <div class="header-title">
                <i data-lucide="megaphone" class="text-primary"></i>
                <h3>Pengumuman Terbaru</h3>
            </div>
            <a href="{{ route('admin.announcement') }}" class="manage-link">Kelola Pengumuman <i data-lucide="chevron-right"></i></a>
        </div>
        <div class="announcements-list">
            @forelse($announcements as $announcement)
            <div class="announcement-item">
                <h4>{{ $announcement->title }}</h4>
                <p>{{ Str::limit($announcement->content, 150) }}</p>
                <div class="announcement-meta">
                    <span>{{ $announcement->created_at->format('d April Y') }}</span>
                    <span class="dot">•</span>
                    <span>Admin Younifirst</span>
                </div>
            </div>
            @empty
            <p class="text-muted text-center" style="font-size: 13px;">Belum ada pengumuman.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ... existing styles remain unchanged ... */
    .dashboard-wrapper {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        display: flex;
        gap: 16px;
        align-items: flex-start;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-wrapper i {
        width: 24px;
        height: 24px;
    }

    .stat-icon-wrapper.blue { background: #EEF2FF; color: #3B82F6; }
    .stat-icon-wrapper.green { background: #ECFDF5; color: #10B981; }
    .stat-icon-wrapper.orange { background: #FFF7ED; color: #F59E0B; }
    .stat-icon-wrapper.yellow { background: #FEFCE8; color: #EAB308; }

    .stat-content {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
    }

    .stat-label {
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .stat-trend {
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
        font-weight: 500;
    }

    .stat-trend i { width: 14px; height: 14px; }
    .stat-trend.blue { color: #3B82F6; }
    .stat-trend.green { color: #10B981; }
    .stat-trend.orange { color: #F59E0B; }
    .stat-trend.yellow { color: #EAB308; }

    /* Alert Bar */
    .alert-bar {
        background: #FFF7ED;
        border: 1px solid #FFEDD5;
        border-radius: 12px;
        padding: 16px 24px;
    }

    .alert-content {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #9A3412;
        font-size: 14px;
    }

    .alert-icon {
        width: 20px;
        height: 20px;
        color: #F97316;
    }

    /* Charts Row */
    .charts-main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .chart-container {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    .chart-header {
        margin-bottom: 20px;
    }

    .chart-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-main);
    }

    /* Bottom Grid */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1.5fr;
        gap: 24px;
    }

    /* Activities */
    .activity-container {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
    }

    .activity-header {
        margin-bottom: 20px;
    }

    .activity-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-main);
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .activity-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .activity-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-icon i { width: 16px; height: 16px; }
    .activity-icon.orange { background: #FFF7ED; color: #F59E0B; }
    .activity-icon.green { background: #ECFDF5; color: #10B981; }
    .activity-icon.yellow { background: #FEFCE8; color: #EAB308; }

    .activity-info p {
        font-size: 13px;
        color: var(--text-main);
        line-height: 1.4;
        margin: 0;
    }

    .activity-info span {
        font-size: 11px;
        color: var(--text-muted);
    }

    /* Announcements Card */
    .announcements-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    .announcements-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .header-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-title h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-main);
    }

    .manage-link {
        font-size: 13px;
        color: var(--primary);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }

    .manage-link i { width: 14px; height: 14px; }

    .announcements-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .announcement-item {
        padding: 16px;
        background: #F8FAFC;
        border-radius: 12px;
        border: 1px solid #F1F5F9;
    }

    .announcement-item h4 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .announcement-item p {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.5;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .announcement-meta {
        font-size: 11px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dot { font-size: 8px; }

    /* Dark Mode Adjustments */
    .dark .stat-card, 
    .dark .chart-container, 
    .dark .activity-container, 
    .dark .announcements-card {
        background: #1E293B;
        border-color: #334155;
    }

    .dark .announcement-item {
        background: #0F172A;
        border-color: #1E293B;
    }

    .dark .alert-bar {
        background: rgba(249, 115, 22, 0.1);
        border-color: rgba(249, 115, 22, 0.2);
    }

    .dark .alert-content { color: #FDBA74; }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .charts-main-grid { grid-template-columns: 1fr; }
        .bottom-grid { grid-template-columns: 1fr 1fr; }
        .activity-container { grid-column: span 2; }
    }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .bottom-grid { grid-template-columns: 1fr; }
        .activity-container { grid-column: span 1; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Growth Chart
        var growthOptions = {
            series: [{
                name: 'Pengguna',
                data: @json($userGrowth)
            }, {
                name: 'Event',
                data: @json($eventGrowth)
            }, {
                name: 'Tim',
                data: @json($teamGrowth)
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded',
                    borderRadius: 4
                },
            },
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: @json($months),
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                title: { text: '' },
                labels: {
                    formatter: function (val) { return val.toFixed(0) }
                }
            },
            fill: { opacity: 1 },
            tooltip: {
                y: {
                    formatter: function (val) { return val }
                }
            },
            colors: ['#3B82F6', '#10B981', '#F59E0B'],
            legend: { position: 'top', horizontalAlign: 'left' }
        };

        var growthChart = new ApexCharts(document.querySelector("#growthChart"), growthOptions);
        growthChart.render();

        // Category Chart
        var categoryOptions = {
            series: @json($categories->pluck('events_count')),
            chart: {
                type: 'donut',
                height: 300
            },
            labels: @json($categories->pluck('name_category')),
            colors: ['#1D4ED8', '#10B981', '#EF4444', '#F59E0B', '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            }
        };

        var categoryChart = new ApexCharts(document.querySelector("#categoryChart"), categoryOptions);
        categoryChart.render();

        // Team Status Chart
        var teamStatusOptions = {
            series: [@json($teamStatus['open']), @json($teamStatus['full'])],
            chart: {
                type: 'donut',
                height: 250
            },
            labels: ['Open', 'Full'],
            colors: ['#10B981', '#EF4444'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: { size: '70%' }
                }
            }
        };

        var teamStatusChart = new ApexCharts(document.querySelector("#teamStatusChart"), teamStatusOptions);
        teamStatusChart.render();

        // L&F Status Chart
        var lfStatusOptions = {
            series: [@json($lfStatus['lost']), @json($lfStatus['found']), @json($lfStatus['resolved'])],
            chart: {
                type: 'donut',
                height: 250
            },
            labels: ['Hilang', 'Ditemukan', 'Selesai'],
            colors: ['#EF4444', '#F59E0B', '#1D4ED8'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: { size: '70%' }
                }
            }
        };

        var lfStatusChart = new ApexCharts(document.querySelector("#lostFoundStatusChart"), lfStatusOptions);
        lfStatusChart.render();

        // Re-create icons for dynamic elements
        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>
@endpush
