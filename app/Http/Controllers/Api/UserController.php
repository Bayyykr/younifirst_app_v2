<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Views\ViewUser;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * GET /api/users
     */
    public function index(Request $request)
    {
        $query = ViewUser::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                   ->orWhere('email', 'like', "%$q%")
                   ->orWhere('nim', 'like', "%$q%");
            });
        }
        if ($request->filled('role'))   $query->where('role', $request->role);
        if ($request->filled('status')) $query->where('status', $request->status);

        $perPage = min((int) $request->get('per_page', 15), 100);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    /**
     * GET /api/users/{user_id}
     */
    public function show(string $user_id)
    {
        $user = ViewUser::where('user_id', $user_id)->firstOrFail();
        return response()->json(['data' => $user]);
    }

    /**
     * POST /api/users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,user',
            'nim'      => 'nullable|string|max:15',
            'prodi'    => 'nullable|string|max:50',
            'status'   => 'nullable|in:active,inactive,suspended,blocked',
        ]);

        // 1. Create user in Firebase
        $firebaseUid = $this->firebase->createUser(
            $validated['email'],
            $validated['password'],
            $validated['name']
        );

        if (!$firebaseUid) {
            return response()->json([
                'message' => 'Gagal membuat user di Firebase. Silakan periksa log server atau kredensial Firebase.'
            ], 500);
        }

        try {
            // 2. Create user in MySQL
            $user = new User();
            // Generate custom ID: USR + 7 random characters (total 10)
            $user->user_id = 'USR' . strtoupper(Str::random(7));
            $user->fill($validated);
            $user->password = Hash::make($validated['password']);
            $user->firebase_uid = $firebaseUid;
            $user->created_at = now();
            $user->save();

            return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
        } catch (\Exception $e) {
            Log::error('MySQL User Creation Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menyimpan user ke database. Silakan periksa log server.'
            ], 500);
        }
    }

    /**
     * PUT /api/users/{user_id}
     */
    public function update(Request $request, string $user_id)
    {
        $user = User::where('user_id', $user_id)->firstOrFail();

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|unique:users,email,' . $user->user_id . ',user_id',
            'role'     => 'sometimes|required|in:admin,user',
            'nim'      => 'nullable|string|max:15',
            'prodi'    => 'nullable|string|max:50',
            // Suspension fields
            'status'   => 'sometimes|string|in:active,suspended,blocked',
            'duration' => 'required_if:status,suspended|string',
            'reason'   => 'required_if:status,suspended|string',
            'notes'    => 'nullable|string',
        ]);

        if ($request->status === 'suspended') {
            $endsAt = null;
            if ($request->duration !== 'custom' && is_numeric($request->duration)) {
                $endsAt = now()->addDays((int) $request->duration);
            }

            \App\Models\UserSuspension::create([
                'user_id' => $user->user_id,
                'duration' => $request->duration,
                'reason'   => $request->reason,
                'internal_notes' => $request->notes,
                'ends_at'  => $endsAt,
            ]);
        }

        $user->update($validated);

        return response()->json(['message' => 'User updated successfully', 'data' => $user]);
    }

}
