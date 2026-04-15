<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LostfoundItem;
use App\Models\LostfoundComment;
use App\Models\Views\ViewLostfound;
use App\Models\Views\ViewLostfoundComment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LostfoundController extends Controller
{
    /**
     * GET /api/lostfound
     */
    public function index(Request $request)
    {
        $query = ViewLostfound::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('item_name', 'like', "%$q%")
                   ->orWhere('description', 'like', "%$q%")
                   ->orWhere('location', 'like', "%$q%");
            });
        }
        if ($request->filled('status_id')) $query->where('status_id', $request->status_id);

        $perPage = min((int) $request->input('per_page', 15), 100);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    /**
     * GET /api/lostfound/{lostfound_id}
     */
    public function show(string $lostfound_id)
    {
        $item = ViewLostfound::where('lostfound_id', $lostfound_id)->firstOrFail();
        return response()->json(['data' => $item]);
    }

    /**
     * GET /api/lostfound/{lostfound_id}/comments
     */
    public function comments(string $lostfound_id, Request $request)
    {
        $perPage  = min((int) $request->input('per_page', 20), 100);
        $comments = ViewLostfoundComment::where('lostfound_id', $lostfound_id)
                        ->orderBy('created_at', 'asc')
                        ->paginate($perPage);

        return response()->json($comments);
    }

    /**
     * POST /api/lostfound
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,user_id',
            'item_name'   => 'required|string|max:50',
            'description' => 'required|string',
            'photo'       => 'nullable|string',
            'location'    => 'required|string|max:255',
            'status_id'   => 'required|exists:item_status,status_id',
        ]);

        $item = new LostfoundItem();
        // Generate custom ID: LNF + 7 random characters (total 10)
        $item->lostfound_id = 'LNF' . strtoupper(Str::random(7));
        $item->fill($validated);
        $item->created_at = \Illuminate\Support\Carbon::now();
        $item->save();

        return response()->json(['message' => 'Lost/Found item created successfully', 'data' => $item], 214);
    }

    /**
     * PUT /api/lostfound/{lostfound_id}
     */
    public function update(Request $request, string $lostfound_id)
    {
        $item = LostfoundItem::where('lostfound_id', $lostfound_id)->firstOrFail();

        $validated = $request->validate([
            'item_name'   => 'sometimes|required|string|max:50',
            'description' => 'sometimes|required|string',
            'photo'       => 'nullable|string',
            'location'    => 'sometimes|required|string|max:255',
            'status_id'   => 'sometimes|required|exists:item_status,status_id',
        ]);

        $item->fill($validated);
        $item->updated_at = now();
        $item->save();

        return response()->json(['message' => 'Lost/Found item updated successfully', 'data' => $item]);
    }

    /**
     * DELETE /api/lostfound/{lostfound_id}
     */
    public function destroy(string $lostfound_id)
    {
        $item = LostfoundItem::where('lostfound_id', $lostfound_id)->firstOrFail();
        $item->delete();

        // Manual soft delete logic if not using SoftDeletes trait
        $item->deleted_at = now();
        $item->save();

        return response()->json(['message' => 'Lost/Found item deleted successfully']);
    }

    /**
     * POST /api/lostfound/{lostfound_id}/comments
     */
    public function addComment(string $lostfound_id, Request $request)
    {
        $user = $request->user();

        // 1. Verify item exists
        $item = LostfoundItem::where('lostfound_id', $lostfound_id)->firstOrFail();

        // 2. Validate input
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        // 3. Create comment
        $comment = new LostfoundComment();
        $comment->comment_id   = 'CMT' . strtoupper(Str::random(7));
        $comment->lostfound_id = $lostfound_id;
        $comment->user_id      = $user->user_id;
        $comment->comment      = $validated['comment'];
        $comment->created_at   = \Illuminate\Support\Carbon::now();
        $comment->save();

        return response()->json([
            'message' => 'Comment added successfully',
            'data'    => $comment
        ], 201);
    }
}
