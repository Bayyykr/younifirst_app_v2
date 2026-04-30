<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewUser;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
class UserController extends Controller
{
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
