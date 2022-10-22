<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

Route::get('/auth/redirect', function () {
    return Socialite::driver('atlassian')->redirect();
});

Route::get('/auth/callback', function () {
    $user = Socialite::driver('atlassian')->user();
    // @todo
});
