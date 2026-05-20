<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Handle photo upload BEFORE fill() so UploadedFile never touches $user->photo
        if ($request->hasFile('photo')) {
            $oldPhoto = $user->getOriginal('photo'); // capture path before any changes
            $path = $request->file('photo')->store('profiles', 'public');
            $user->photo = $path;

            // Delete old photo after new one is successfully stored
            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        // Only fill text fields — photo is handled separately above
        $user->fill($request->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's notification settings via AJAX.
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'notify_email' => 'required|boolean',
            'notify_event' => 'required|boolean',
            'notify_team' => 'required|boolean',
            'notify_lostfound' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->update($request->only([
            'notify_email',
            'notify_event',
            'notify_team',
            'notify_lostfound'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan notifikasi berhasil diperbarui.'
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
