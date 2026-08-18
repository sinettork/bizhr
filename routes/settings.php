<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', [SettingsController::class, 'profile'])->name('profile.edit');
    Route::put('settings/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::get('settings/profile/avatar', [SettingsController::class, 'avatar'])->name('profile.avatar');
});

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('settings/appearance', [SettingsController::class, 'profile'])->name('appearance.edit');
    Route::get('settings/security', [SettingsController::class, 'security'])->middleware('password.confirm')->name('security.edit');
    Route::put('settings/security/password', [SettingsController::class, 'updatePassword'])->middleware('password.confirm')->name('security.password.update');
});

Route::get('.well-known/passkey-endpoints', fn () => response()->json([
    'enroll' => route('security.edit'),
    'manage' => route('security.edit'),
]))->name('well-known.passkeys');
