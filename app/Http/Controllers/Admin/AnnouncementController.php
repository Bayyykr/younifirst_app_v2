<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Views\ViewAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class AnnouncementController extends Controller
{
    public function index()
    {
        $hariMap = [
            'Sunday'    => 'Minggu', 'Monday'  => 'Senin',  'Tuesday'  => 'Selasa',
            'Wednesday' => 'Rabu',   'Thursday'=> 'Kamis',  'Friday'   => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $bulanMap = [
            'January'   => 'Januari',   'February' => 'Februari', 'March'    => 'Maret',
            'April'     => 'April',     'May'      => 'Mei',      'June'     => 'Juni',
            'July'      => 'Juli',      'August'   => 'Agustus',  'September'=> 'September',
            'October'   => 'Oktober',   'November' => 'November', 'December' => 'Desember',
        ];

        $announcements = ViewAnnouncement::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($announcement) use ($hariMap, $bulanMap) {
                $date          = $announcement->created_at;
                $formattedDate = $hariMap[$date->format('l')]
                               . ', '
                               . $date->format('d')
                               . ' '
                               . $bulanMap[$date->format('F')]
                               . ' '
                               . $date->format('Y');

                return [
                    'id'           => $announcement->announcement_id,
                    'title'        => $announcement->title,
                    'content'      => $announcement->content,
                    'creator_name' => $announcement->creator_name ?? 'Sistem',
                    'date'         => $formattedDate,
                    'file_url'     => $announcement->file_url,
                ];
            });

        return view('admin.announcements', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $announcement = new Announcement();
        $announcement->announcement_id = 'ANN' . strtoupper(Str::random(7));
        $announcement->title           = $request->title;
        $announcement->content         = $request->input('content');
        $announcement->created_by      = auth()->id();
        $announcement->created_at      = Carbon::now();

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('announcements', 'public');
            $announcement->file = $path;
        }

        $announcement->save();

        return back()->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, $announcement_id)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $announcement = Announcement::where('announcement_id', $announcement_id)->firstOrFail();
        $announcement->title   = $request->title;
        $announcement->content = $request->input('content');

        if ($request->hasFile('file')) {
            if ($announcement->file) {
                Storage::disk('public')->delete($announcement->file);
            }
            $path = $request->file('file')->store('announcements', 'public');
            $announcement->file = $path;
        }

        $announcement->save();

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy($announcement_id)
    {
        $announcement = Announcement::where('announcement_id', $announcement_id)->firstOrFail();
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
