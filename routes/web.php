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
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;

Route::get('/', [PageController::class,'landing'])->name('landing');

// Guest auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class,'showLogin'])->name('login');
    Route::post('/login', [AuthController::class,'login']);
    Route::get('/register', [AuthController::class,'showRegister'])->name('register');
    Route::post('/register', [AuthController::class,'register']);
    Route::get('/forgot-password', [AuthController::class,'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class,'forgot']);
});

Route::post('/logout', [AuthController::class,'logout'])->name('logout')->middleware('auth');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');

    Route::get('/diagnosis', [DiagnosisController::class,'index'])->name('diagnosis.index');
    Route::post('/diagnosis', [DiagnosisController::class,'analyze'])->name('diagnosis.analyze');
    Route::get('/diagnosis/{diagnosis}', [DiagnosisController::class,'show'])->name('diagnosis.show');

    Route::get('/chat', [AiChatController::class,'index'])->name('chat.index');
    Route::post('/chat', [AiChatController::class,'send'])->name('chat.send');
    Route::post('/chat/clear', [AiChatController::class,'clear'])->name('chat.clear');

    Route::get('/weather', [WeatherController::class,'index'])->name('weather.index');
    Route::get('/market', [MarketController::class,'index'])->name('market.index');
    Route::get('/cultivation', [CultivationController::class,'index'])->name('cultivation.index');

    Route::get('/records', [RecordController::class,'index'])->name('records.index');
    Route::post('/records', [RecordController::class,'store'])->name('records.store');
    Route::delete('/records/{record}', [RecordController::class,'destroy'])->name('records.destroy');

    Route::get('/community', [CommunityController::class,'index'])->name('community.index');
    Route::post('/community', [CommunityController::class,'store'])->name('community.store');
    Route::post('/community/{post}/comment', [CommunityController::class,'comment'])->name('community.comment');
    Route::post('/community/{post}/like', [CommunityController::class,'like'])->name('community.like');

    Route::get('/recommendations', [RecommendationController::class,'index'])->name('recommendations.index');
    Route::get('/notifications', [NotificationController::class,'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class,'markRead'])->name('notifications.read');

    Route::get('/profile', [ProfileController::class,'show'])->name('profile.show');
    Route::get('/settings', [ProfileController::class,'settings'])->name('profile.settings');
    Route::patch('/profile', [ProfileController::class,'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class,'updatePassword'])->name('profile.password');
});

// Admin
Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class,'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class,'users'])->name('users');
    Route::post('/users/{user}/toggle', [AdminController::class,'toggleRole'])->name('users.toggle');
    Route::delete('/users/{user}', [AdminController::class,'destroyUser'])->name('users.destroy');
    Route::get('/ai-analytics', [AdminController::class,'aiAnalytics'])->name('ai');
    Route::get('/diagnosis-logs', [AdminController::class,'diagnosisLogs'])->name('logs');
    Route::get('/moderation', [AdminController::class,'moderation'])->name('moderation');
    Route::delete('/community/{post}', [AdminController::class,'deletePost'])->name('community.destroy');
});
