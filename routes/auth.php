<?php

use App\Http\Controllers\AtlassianController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/atlassian/redirect', [AtlassianController::class, 'atlassianRedirect'])->name('auth-redirect');
Route::get('/auth/atlassian/callback', [AtlassianController::class, 'atlassianUserRegister'])->name('auth-callback');
