<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostfoundItem;
use App\Models\Views\ViewLostfound;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
            'lost'     => LostfoundItem::where('status', 'lost',)->count(),
            'claimed'  => LostfoundItem::where('status', 'claimed')->count(),
        ];

        // 2. Fetch All Items for Client-side filtering
        $items = ViewLostfound::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($item) => [
                'id'            => $item->lostfound_id,
                'name'          => $item->item_name,
                'description'   => $item->description,
                'photo'         => $item->photo_url,
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

    /**
     * Store a new item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'   => 'required|string|max:50',
            'description' => 'required|string',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'location'    => 'required|string|max:255',
            'status'      => 'required|in:lost,found,claimed',
        ]);

        $item = new LostfoundItem();
        $item->lostfound_id = 'LNF' . strtoupper(Str::random(7));
        $item->user_id      = Auth::id(); // Admin who posts it
        $item->item_name    = $validated['item_name'];
        $item->description  = $validated['description'];
        $item->location     = $validated['location'];
        $item->status       = $validated['status'];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('lostfound', 'public');
            $item->photo = $path;
        }

        $item->created_at = now();
        $item->save();

        // Fetch the item from view for consistent structure
        $savedItem = ViewLostfound::where('lostfound_id', $item->lostfound_id)->first();
        
        return response()->json([
            'message' => 'Barang berhasil diposting!',
            'data'    => [
                'id'            => $savedItem->lostfound_id,
                'name'          => $savedItem->item_name,
                'description'   => $savedItem->description,
                'photo'         => $savedItem->photo_url,
                'location'      => $savedItem->location,
                'date'          => $savedItem->created_at->format('d F Y'),
                'reporter_name' => $savedItem->reporter_name,
                'reporter_nim'  => $savedItem->reporter_nim ?? 'Mahasiswa',
                'status'        => $savedItem->status,
                'status_label'  => match($savedItem->status) {
                    'lost'    => 'Hilang',
                    'found'   => 'Ditemukan',
                    'claimed' => 'Diklaim',
                    default   => 'Unknown'
                },
                'status_class' => match($savedItem->status) {
                    'lost'    => 'status-danger',
                    'found'   => 'status-success',
                    'claimed' => 'status-warning',
                    default   => 'status-neutral'
                }
            ]
        ], 201);
    }

    /**
     * Resolve an item (mark as claimed/finished).
     */
    public function resolve(string $id)
    {
        $item = LostfoundItem::where('lostfound_id', $id)->firstOrFail();
        $item->status = 'claimed';
        $item->updated_at = now();
        $item->save();

        return response()->json([
            'message' => 'Status barang berhasil diperbarui menjadi selesai!',
            'status' => 'claimed'
        ]);
    }

    /**
     * Delete an item.
     */
    public function destroy(string $id)
    {
        $item = LostfoundItem::where('lostfound_id', $id)->firstOrFail();
        $item->delete();

        return response()->json([
            'message' => 'Data barang berhasil dihapus!'
        ]);
    }
}
