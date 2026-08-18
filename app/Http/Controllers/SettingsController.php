<?php

namespace App\Http\Controllers;

use App\Services\UploadedFileSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    public function profile(Request $request): View
    {
        return view('settings.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=120,min_height=120,max_width=3000,max_height=3000'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);
        if ($data['email'] !== $user->email) {
            $user->forceFill(['email_verified_at' => null]);
        }
        unset($data['avatar'], $data['remove_avatar']);

        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            $this->deleteAvatar($user->avatar_path);
            $data['avatar_path'] = null;
        }

        if ($request->hasFile('avatar')) {
            $fileSecurity->assertSafe($request->file('avatar'), 'avatar');
            if ($user->avatar_path) {
                $this->deleteAvatar($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store("private/users/{$user->id}/avatars", 'local');
        }

        $user->fill($data)->save();

        return back()->with('status', 'Profile saved.');
    }

    public function avatar(Request $request): StreamedResponse
    {
        $path = $request->user()->avatar_path;
        abort_unless($path !== null, 404);
        $disk = str_starts_with($path, 'private/') ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path, null, [
            'Cache-Control' => 'private, max-age=300, no-transform',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function security(): View
    {
        return view('settings.security', [
            'passkeys' => request()->user()->passkeys()->latest()->get(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'confirmed', 'min:12', 'max:255']]);
        $request->user()->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('status', 'Password updated.');
    }

    private function deleteAvatar(string $path): void
    {
        Storage::disk(str_starts_with($path, 'private/') ? 'local' : 'public')->delete($path);
    }
}
