<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewUser;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use App\Models\User;
use App\Models\UserSuspension;
use App\Services\FirebaseService;
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

    public function index()
    {
        $users = ViewUser::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($user) => [
                'id' => $user->user_id,
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'nim' => $user->nim ?? '-',
                'prodi' => $user->prodi ?? '-',
                'joined' => $user->created_at->format('d M Y'),
                'status' => strtolower($user->status),
                'role' => $user->role,
                'encoded_name' => urlencode($user->name),
            ]);

        return view('admin.users', compact('users'));
    }

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

        $firebaseUid = $this->firebase->createUser(
            $validated['email'],
            $validated['password'],
            $validated['name']
        );

        if (!$firebaseUid) {
            return response()->json(['message' => 'Gagal membuat user di Firebase.'], 500);
        }

        try {
            $user = new User();
            $user->user_id = 'USR' . strtoupper(Str::random(7));
            $user->fill($validated);
            $user->password = Hash::make($validated['password']);
            $user->firebase_uid = $firebaseUid;
            $user->created_at = now();
            $user->save();

            return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
        } catch (\Exception $e) {
            Log::error('MySQL User Creation Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan user ke database.'], 500);
        }
    }

    public function update(Request $request, string $user_id)
    {
        $user = User::where('user_id', $user_id)->firstOrFail();

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|unique:users,email,' . $user->user_id . ',user_id',
            'role'     => 'sometimes|required|in:admin,user',
            'nim'      => 'nullable|string|max:15',
            'prodi'    => 'nullable|string|max:50',
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

            UserSuspension::create([
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

    public function destroy(string $user_id)
    {
        $user = User::where('user_id', $user_id)->firstOrFail();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function exportPdf(Request $request)
    {
        $query = ViewUser::orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(nim) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->has('status') && $request->status != 'Semua Status' && $request->status != '') {
            $query->where('status', strtolower($request->status));
        }

        $users = $query->get()->map(fn($user) => [
            'name' => $user->name,
            'email' => $user->email,
            'nim' => $user->nim ?? '-',
            'prodi' => $user->prodi ?? '-',
            'joined' => $user->created_at->format('d M Y'),
            'status' => strtolower($user->status),
        ]);

        $html = view('admin.exports.users-pdf', compact('users'))->render();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="users_export_' . date('Y-m-d') . '.pdf"',
        ]);
    }
}
