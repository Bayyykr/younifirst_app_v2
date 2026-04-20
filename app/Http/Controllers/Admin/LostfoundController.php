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
        // 1. Calculate Stats using string enum status
        $stats = [
            'found'    => LostfoundItem::where('status', 'found')->count(),
            'lost'     => LostfoundItem::where('status', 'lost')->count(),
            'claimed'  => LostfoundItem::where('status', 'claimed')->count(),
        ];

        // 2. Fetch All Items for Client-side filtering
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
                'status'        => $item->status,
                'status_label'  => match($item->status) {
                    'lost'    => 'Hilang',
                    'found'   => 'Ditemukan',
                    'claimed' => 'Diklaim',
                    default   => 'Unknown'
                },
                'status_class' => match($item->status) {
                    'lost'    => 'status-danger',
                    'found'   => 'status-success',
                    'claimed' => 'status-warning',
                    default   => 'status-neutral'
                }
            ]);

        return view('admin.lostfound', compact('stats', 'items'));
    }
}
