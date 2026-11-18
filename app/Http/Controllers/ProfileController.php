<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the signed-in user's profile.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the signed-in user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $attributes = $request->safe()->only(['name', 'email', 'phone']);

        if ($request->hasFile('avatar')) {
            $previousAvatar = $user->getRawOriginal('avatar_path');

            if ($previousAvatar) {
                Storage::disk('public')->delete($previousAvatar);
            }

            $attributes['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($attributes);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Update the signed-in user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()->route('profile.edit')->with('status', 'Kata sandi berhasil diubah.');
    }
}
