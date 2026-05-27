<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\CultivationController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;

Route::get('/', [PageController::class, 'landing'])->name('landing');

// ── Auth (guest only) ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',            [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',           [AuthController::class, 'login']);
    Route::get('/register',         [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',        [AuthController::class, 'register']);
    Route::get('/forgot-password',  [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgot']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated user routes ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Diagnosis
    Route::get('/diagnosis',                [DiagnosisController::class, 'index'])->name('diagnosis.index');
    Route::post('/diagnosis',               [DiagnosisController::class, 'analyze'])->name('diagnosis.analyze');
    Route::get('/diagnosis/{diagnosis}',    [DiagnosisController::class, 'show'])->name('diagnosis.show');
    Route::delete('/diagnosis/{diagnosis}', [DiagnosisController::class, 'destroy'])->name('diagnosis.destroy');

    // Chat
    Route::get('/chat',              [AiChatController::class, 'index'])->name('chat.index');
    Route::post('/chat',             [AiChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/clear',       [AiChatController::class, 'clear'])->name('chat.clear');
    Route::get('/chat/crop',         [AiChatController::class, 'withCrop'])->name('chat.crop');

    // Weather
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

    // Market
    Route::get('/market', [MarketController::class, 'index'])->name('market.index');

    // Cultivation
    Route::get('/cultivation', [CultivationController::class, 'index'])->name('cultivation.index');

    // Records
    Route::get('/records',             [RecordController::class, 'index'])->name('records.index');
    Route::post('/records',            [RecordController::class, 'store'])->name('records.store');
    Route::delete('/records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');

    // Community
    Route::get('/community',                       [CommunityController::class, 'index'])->name('community.index');
    Route::post('/community',                      [CommunityController::class, 'store'])->name('community.store');
    Route::patch('/community/{post}',              [CommunityController::class, 'update'])->name('community.update');
    Route::delete('/community/{post}',             [CommunityController::class, 'destroy'])->name('community.destroy');
    Route::post('/community/{post}/comment',       [CommunityController::class, 'comment'])->name('community.comment');
    Route::post('/community/{post}/like',          [CommunityController::class, 'like'])->name('community.like');
    Route::post('/community/{post}/report',        [CommunityController::class, 'report'])->name('community.report');

    // Notifications
    Route::get('/notifications',       [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Profile
    Route::get('/profile',            [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/settings',           [ProfileController::class, 'settings'])->name('profile.settings');
    Route::patch('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// ── Admin routes ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                        [AdminController::class, 'dashboard'])->name('dashboard');

    // Users
    Route::get('/users',                   [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle',    [AdminController::class, 'toggleRole'])->name('users.toggle');
    Route::delete('/users/{user}',         [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/users/{user}/ban',       [AdminController::class, 'banUser'])->name('users.ban');

    // Moderation
    Route::get('/moderation',              [AdminController::class, 'moderation'])->name('moderation');
    Route::delete('/community/{post}',     [AdminController::class, 'deletePost'])->name('community.destroy');
    Route::post('/community/{post}/warn',  [AdminController::class, 'warnPost'])->name('community.warn');

    // AI & Logs
    Route::get('/ai-analytics',            [AdminController::class, 'aiAnalytics'])->name('ai');
    Route::get('/diagnosis-logs',          [AdminController::class, 'diagnosisLogs'])->name('logs');

    // Admin profile (uses admin layout — NOT user sidebar)
    Route::get('/profile',                 [AdminController::class, 'profilePage'])->name('profile');
    Route::get('/settings',                [AdminController::class, 'settingsPage'])->name('settings');
    Route::patch('/settings',              [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::patch('/settings/password',     [AdminController::class, 'updatePassword'])->name('settings.password');
});
