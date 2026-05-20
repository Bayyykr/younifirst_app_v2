<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Views\ViewUser;
use App\Models\UserSuspension;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\LostfoundItem;
use App\Models\Team;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends Controller
{
    public function index()
    {
        $categories = EventCategory::orderBy('name_category', 'asc')->get();
        $signatureFile = $this->getUserSignature(auth()->id());
        $signatureUrl = $signatureFile ? \Illuminate\Support\Facades\Storage::disk('public')->url($signatureFile) : null;

        return view('admin.reports', compact('categories', 'signatureUrl'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,events,lostfound,teams',
            'format' => 'nullable|in:pdf,csv',
            'status_akun' => 'nullable|string',
            'category_id' => 'nullable|string',
            'periode' => 'nullable|string',
            'status_laporan' => 'nullable|string',
            'tingkat_lomba' => 'nullable|string',
        ]);

        $type = $request->type;
        $format = $request->input('format', 'pdf');
        $printedBy = auth()->user();
        $printedDate = now()->translatedFormat('d F Y H:i') . ' WIB';

        // Get signature path
        $signatureFile = $this->getUserSignature(auth()->id());
        $signatureBase64 = $this->getSignatureBase64($signatureFile);

        if ($type === 'users') {
            $statusAkun = $request->input('status_akun', 'all');
            
            $usersQuery = ViewUser::query();
            if ($statusAkun && $statusAkun !== 'all') {
                $usersQuery->where('status', $statusAkun);
            }
            $users = $usersQuery->orderBy('created_at', 'desc')->get();

            $suspensionsQuery = UserSuspension::with('user');
            if ($statusAkun && $statusAkun !== 'all') {
                if ($statusAkun === 'suspended' || $statusAkun === 'blocked') {
                    $suspensionsQuery->whereHas('user', function($q) use ($statusAkun) {
                        $q->where('status', $statusAkun);
                    });
                } else {
                    $suspensionsQuery->whereRaw('1=0');
                }
            }
            $suspensions = $suspensionsQuery->orderBy('created_at', 'desc')->get();

            // Stats remain system-wide summary for context
            $allUsers = ViewUser::all();
            $stats = [
                'total' => $allUsers->count(),
                'active' => $allUsers->where('status', 'active')->count(),
                'suspended' => $allUsers->where('status', 'suspended')->count(),
                'blocked' => $allUsers->where('status', 'blocked')->count(),
            ];

            $statusLabels = [
                'all' => 'Semua Status',
                'active' => 'Aktif',
                'suspended' => 'Suspended',
                'blocked' => 'Blocked',
            ];
            $filterLabel = $statusLabels[$statusAkun] ?? 'Semua Status';

            if ($format === 'csv') {
                return $this->exportUsersCsv($users, $suspensions);
            }

            $dompdf = $this->initDompdf();
            $html = view('admin.exports.users-pdf', compact('users', 'suspensions', 'stats', 'printedBy', 'printedDate', 'signatureBase64', 'filterLabel'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $filename = 'Laporan_User_Management_' . date('Ymd_His') . '.pdf';

        } elseif ($type === 'events') {
            $categoryId = $request->input('category_id', 'all');
            $periode = $request->input('periode', 'all');

            $eventsQuery = Event::with(['category', 'creator']);
            
            if ($categoryId && $categoryId !== 'all') {
                $eventsQuery->where('category_id', $categoryId);
            }
            
            if ($periode && $periode !== 'all') {
                if ($periode === 'today') {
                    $eventsQuery->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now());
                } elseif ($periode === 'week') {
                    $eventsQuery->where(function($q) {
                        $q->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()])
                          ->orWhereBetween('end_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    });
                } elseif ($periode === 'month') {
                    $eventsQuery->where(function($q) {
                        $q->whereMonth('start_date', now()->month)->whereYear('start_date', now()->year);
                    });
                } elseif ($periode === 'year') {
                    $eventsQuery->whereYear('start_date', now()->year);
                }
            }

            $events = $eventsQuery->orderBy('created_at', 'desc')->get();
            
            // Overall stats
            $allEvents = Event::all();
            $stats = [
                'total' => $allEvents->count(),
                'approved' => $allEvents->whereIn('status', ['upcoming', 'ongoing', 'completed'])->count(),
                'pending' => $allEvents->where('status', 'pending')->count(),
                'rejected' => $allEvents->where('status', 'rejected')->count(),
                'finished' => Event::query()->whereIn('status', ['upcoming', 'ongoing', 'completed'])->where('end_date', '<', now())->count(),
            ];

            $categories = EventCategory::all();
            $categoryStats = [];
            foreach ($categories as $cat) {
                $count = Event::query()->where('category_id', $cat->category_id)->count();
                $categoryStats[] = [
                    'name' => $cat->name_category,
                    'count' => $count
                ];
            }

            if ($format === 'csv') {
                return $this->exportEventsCsv($events);
            }

            $dompdf = $this->initDompdf();
            $html = view('admin.exports.events-pdf', compact('events', 'stats', 'categoryStats', 'printedBy', 'printedDate', 'signatureBase64'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $filename = 'Laporan_Event_Management_' . date('Ymd_His') . '.pdf';

        } elseif ($type === 'lostfound') {
            $statusLaporan = $request->input('status_laporan', 'all');

            $lostfoundQuery = LostfoundItem::with('user');
            
            if ($statusLaporan && $statusLaporan !== 'all') {
                if ($statusLaporan === 'unresolved') {
                    $lostfoundQuery->whereIn('status', ['lost', 'found']);
                } else {
                    $lostfoundQuery->where('status', $statusLaporan);
                }
            }

            $items = $lostfoundQuery->orderBy('created_at', 'desc')->get();

            // Overall stats
            $allLF = LostfoundItem::all();
            $stats = [
                'total' => $allLF->count(),
                'lost' => $allLF->where('status', 'lost')->count(),
                'found' => $allLF->where('status', 'found')->count(),
                'resolved' => $allLF->where('status', 'resolved')->count(),
            ];

            if ($format === 'csv') {
                return $this->exportLostfoundCsv($items);
            }

            $dompdf = $this->initDompdf();
            $html = view('admin.exports.lostfound-pdf', compact('items', 'stats', 'printedBy', 'printedDate', 'signatureBase64'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $filename = 'Laporan_Lost_Found_' . date('Ymd_His') . '.pdf';

        } elseif ($type === 'teams') {
            $tingkatLomba = $request->input('tingkat_lomba', 'all');

            $teamsQuery = Team::with(['members' => function($q) {
                $q->where('status', 'active');
            }, 'members.user'])
            ->whereNotNull('achievement_rank')
            ->where('achievement_rank', '!=', '');

            if ($tingkatLomba && $tingkatLomba !== 'all') {
                $teamsQuery->where('competition_level', $tingkatLomba);
            }

            $teams = $teamsQuery->orderBy('created_at', 'desc')->get();

            // Overall stats for teams with reports
            $allReportedTeams = Team::whereNotNull('achievement_rank')
                ->where('achievement_rank', '!=', '')
                ->get();

            $stats = [
                'total' => $allReportedTeams->count(),
                'kampus' => $allReportedTeams->where('competition_level', 'kampus')->count(),
                'regional' => $allReportedTeams->where('competition_level', 'regional')->count(),
                'nasional' => $allReportedTeams->where('competition_level', 'nasional')->count(),
                'internasional' => $allReportedTeams->where('competition_level', 'internasional')->count(),
            ];

            $levelLabels = [
                'all' => 'Semua Tingkat',
                'kampus' => 'Kampus',
                'regional' => 'Regional',
                'nasional' => 'Nasional',
                'internasional' => 'Internasional',
            ];
            $filterLabel = $levelLabels[$tingkatLomba] ?? 'Semua Tingkat';

            $dompdf = $this->initDompdf();
            $html = view('admin.exports.teams-pdf', compact('teams', 'stats', 'printedBy', 'printedDate', 'signatureBase64', 'filterLabel'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $filename = 'Laporan_Team_Monitoring_' . date('Ymd_His') . '.pdf';
        }

        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();
        $file = $request->file('signature');
        
        $directory = 'signatures';
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        
        // Find existing signature files for this user and delete them to keep storage clean
        if ($disk->exists($directory)) {
            $existingFiles = $disk->files($directory);
            foreach ($existingFiles as $existingFile) {
                if (strpos(basename($existingFile), 'user_' . $user->user_id . '_') === 0) {
                    $disk->delete($existingFile);
                }
            }
        }

        // Save new signature with timestamp to bust caching
        $filename = 'user_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        return response()->json([
            'status' => 'success',
            'message' => 'Tanda tangan berhasil diunggah.',
            'url' => $disk->url($path)
        ]);
    }

    public function deleteSignature()
    {
        $user = auth()->user();
        $directory = 'signatures';
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        
        if ($disk->exists($directory)) {
            $existingFiles = $disk->files($directory);
            $deleted = false;
            foreach ($existingFiles as $existingFile) {
                if (strpos(basename($existingFile), 'user_' . $user->user_id . '_') === 0) {
                    $disk->delete($existingFile);
                    $deleted = true;
                }
            }

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tanda tangan berhasil dihapus.'
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Tidak ada tanda tangan untuk dihapus.'
        ], 400);
    }

    private function initDompdf()
    {
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        return $dompdf;
    }

    private function getUserSignature($userId)
    {
        $directory = 'signatures';
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        
        if ($disk->exists($directory)) {
            $files = $disk->files($directory);
            foreach ($files as $file) {
                if (strpos(basename($file), 'user_' . $userId . '_') === 0) {
                    return $file;
                }
            }
        }
        return null;
    }

    private function getSignatureBase64($signatureFile)
    {
        if (!$signatureFile) return null;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if ($disk->exists($signatureFile)) {
            $path = $disk->path($signatureFile);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        return null;
    }

    private function exportUsersCsv($users, $suspensions)
    {
        $filename = 'Laporan_User_Management_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users, $suspensions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['LAPORAN USER MANAGEMENT - YOUNIFIRST']);
            fputcsv($file, ['Tanggal Ekspor', now()->translatedFormat('d F Y H:i') . ' WIB']);
            fputcsv($file, ['Diekspor Oleh', auth()->user()->name]);
            fputcsv($file, []);

            fputcsv($file, ['No', 'NIM', 'Nama', 'Email', 'Program Studi', 'Status']);

            foreach ($users as $index => $user) {
                fputcsv($file, [
                    $index + 1,
                    $user->nim ?? '-',
                    $user->name,
                    $user->email,
                    $user->prodi ?? '-',
                    ucfirst($user->status),
                ]);
            }

            if ($suspensions->count() > 0) {
                fputcsv($file, []);
                fputcsv($file, ['RIWAYAT PENANGGUHAN AKUN (MODERASI)']);
                fputcsv($file, ['No', 'Nama Pengguna', 'Durasi', 'Alasan Penangguhan', 'Catatan Internal Admin']);
                
                foreach ($suspensions as $index => $susp) {
                    $dur = $susp->duration === 'permanent' || $susp->duration === 'custom' 
                        ? $susp->duration 
                        : $susp->duration . ' Hari';
                    fputcsv($file, [
                        $index + 1,
                        $susp->user->name ?? 'User dihapus',
                        $dur,
                        $susp->reason,
                        $susp->internal_notes ?? '-',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportEventsCsv($events)
    {
        $filename = 'Laporan_Event_Management_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($events) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['LAPORAN EVENT MANAGEMENT - YOUNIFIRST']);
            fputcsv($file, ['Tanggal Ekspor', now()->translatedFormat('d F Y H:i') . ' WIB']);
            fputcsv($file, ['Diekspor Oleh', auth()->user()->name]);
            fputcsv($file, []);

            fputcsv($file, ['No', 'ID Event', 'Judul Event', 'Kategori', 'Penyelenggara', 'Tanggal Mulai', 'Tanggal Selesai', 'Lokasi', 'Status']);

            foreach ($events as $index => $event) {
                fputcsv($file, [
                    $index + 1,
                    $event->event_id,
                    $event->title,
                    $event->category->name_category ?? '-',
                    $event->creator->name ?? 'System',
                    $event->start_date->translatedFormat('d M Y H:i'),
                    $event->end_date->translatedFormat('d M Y H:i'),
                    $event->location,
                    ucfirst($event->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportLostfoundCsv($items)
    {
        $filename = 'Laporan_Lost_and_Found_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['LAPORAN LOST AND FOUND - YOUNIFIRST']);
            fputcsv($file, ['Tanggal Ekspor', now()->translatedFormat('d F Y H:i') . ' WIB']);
            fputcsv($file, ['Diekspor Oleh', auth()->user()->name]);
            fputcsv($file, []);

            fputcsv($file, ['No', 'Nama Barang', 'Kategori/Deskripsi', 'Lokasi', 'Status', 'Tanggal Lapor', 'Pelapor']);

            foreach ($items as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->item_name,
                    $item->description,
                    $item->location,
                    ucfirst($item->status),
                    $item->created_at->translatedFormat('d M Y H:i'),
                    $item->user->name ?? 'Anonim',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
