<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewUser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = ViewUser::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($user) => [
                'id'           => $user->user_id,
                'name'         => $user->name,
                'email'        => $user->email,
                'nim'          => $user->nim  ?? '-',
                'prodi'        => $user->prodi ?? '-',
                'joined'       => $user->created_at->format('d M Y'),
                'status'       => strtolower($user->status),
                'encoded_name' => urlencode($user->name),
            ]);

        return view('admin.users', compact('users'));
    }
}
