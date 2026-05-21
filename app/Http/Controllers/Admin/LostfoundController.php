<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostfoundItem;
use App\Models\Views\ViewLostfound;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LostfoundController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }
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
        $items = ViewLostfound::select('view_lostfound.*', 'u.photo AS reporter_photo_path')
            ->leftJoin('users AS u', 'view_lostfound.reporter_id', '=', 'u.user_id')
            ->orderBy('view_lostfound.created_at', 'desc')
            ->get()
            ->map(function($item) {
                $reporterPhoto = $item->reporter_photo_path 
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->reporter_photo_path)
                    : null;

                return [
                    'id'             => $item->lostfound_id,
                    'name'           => $item->item_name,
                    'description'    => $item->description,
                    'photo'          => $item->photo_url,
                    'location'       => $item->location,
                    'date'           => $item->created_at->format('d F Y'),
                    'time_ago'       => $item->created_at->diffForHumans(),
                    'reporter_name'  => $item->reporter_name,
                    'reporter_nim'   => $item->reporter_nim ?? 'Mahasiswa',
                    'reporter_photo' => $reporterPhoto,
                    'comments_count' => $item->total_comments ?? 0,
                    'status'         => $item->status,
                    'status_label'   => match($item->status) {
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
                ];
            });

        $firebaseConfig = [
            'apiKey'            => env('FIREBASE_API_KEY'),
            'authDomain'        => env('FIREBASE_PROJECT_ID') . '.firebaseapp.com',
            'databaseURL'       => env('FIREBASE_DATABASE_URL', 'https://' . env('FIREBASE_PROJECT_ID') . '-default-rtdb.asia-southeast1.firebasedatabase.app'),
            'projectId'         => env('FIREBASE_PROJECT_ID'),
            'storageBucket'     => env('FIREBASE_PROJECT_ID') . '.appspot.com',
        ];

        return view('admin.lostfound', compact('stats', 'items', 'firebaseConfig'));
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
                'id'             => $savedItem->lostfound_id,
                'name'           => $savedItem->item_name,
                'description'    => $savedItem->description,
                'photo'          => $savedItem->photo_url,
                'location'       => $savedItem->location,
                'date'           => $savedItem->created_at->format('d F Y'),
                'time_ago'       => $savedItem->created_at->diffForHumans(),
                'reporter_name'  => $savedItem->reporter_name,
                'reporter_nim'   => $savedItem->reporter_nim ?? 'Mahasiswa',
                'comments_count' => 0,
                'status'         => $savedItem->status,
                'status_label'   => match($savedItem->status) {
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

        // Push Notification to Item Owner
        try {
            $owner = $item->user;
            if ($owner && $owner->fcm_token) {
                $this->firebase->sendNotification(
                    $owner->fcm_token,
                    "Update Status Barang",
                    "Barang '{$item->item_name}' Anda telah ditandai sebagai Selesai/Diklaim.",
                    [
                        'lostfound_id' => (string) $item->lostfound_id,
                        'status'       => $item->status,
                        'type'         => 'lostfound_status_update'
                    ]
                );
                Log::info("FCM: LostFound notification sent to user {$owner->user_id}");
            }
        } catch (\Throwable $e) {
            Log::warning("FCM: LostFound notification failed for item {$id}: " . $e->getMessage());
        }

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
