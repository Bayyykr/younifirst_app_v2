<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewAnnouncement;
use Illuminate\Http\Request;

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
                ];
            });

        return view('admin.announcements', compact('announcements'));
    }
}
