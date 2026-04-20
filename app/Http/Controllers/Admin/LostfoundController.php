<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostfoundItem;
use App\Models\Views\ViewLostfound;
use Illuminate\Http\Request;

class LostfoundController extends Controller
{
    /**
     * Display the Lost and Found dashboard for Admin.
     */
    public function index(Request $request)
    {
        // 1. Calculate Stats
        $stats = [
            'found'    => LostfoundItem::where('status_id', 2)->count(),
            'lost'     => LostfoundItem::where('status_id', 1)->count(),
            'finished' => LostfoundItem::whereIn('status_id', [3, 4])->count(),
        ];

        // 2. Fetch All Items for Client-side filtering (matches User Management pattern)
        $items = ViewLostfound::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($item) => [
                'id'            => $item->lostfound_id,
                'name'          => $item->item_name,
                'description'   => $item->description,
                'photo'         => $item->photo,
                'location'      => $item->location,
                'date'          => $item->created_at->format('d F Y'),
                'reporter_name' => $item->reporter_name,
                'reporter_nim'  => $item->reporter_nim ?? 'Mahasiswa',
                'status_id'     => $item->status_id,
                'status_label'  => match((int)$item->status_id) {
                    1 => 'Hilang',
                    2 => 'Ditemukan',
                    3 => 'Dikembalikan',
                    4 => 'Diklaim',
                    default => 'Unknown'
                },
                'status_class' => match((int)$item->status_id) {
                    1 => 'status-danger',
                    2 => 'status-success',
                    3, 4 => 'status-warning',
                    default => 'status-neutral'
                }
            ]);

        return view('admin.lostfound', compact('stats', 'items'));
    }
}
