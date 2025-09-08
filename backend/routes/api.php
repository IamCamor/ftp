<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\OAuthController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\CatchController;
use App\Http\Controllers\Api\V1\CatchLikeController;
use App\Http\Controllers\Api\V1\CatchCommentController;
use App\Http\Controllers\Api\V1\PointsController;
use App\Http\Controllers\Api\V1\WeatherFavController;
use App\Http\Controllers\Api\V1\RatingsController;
use App\Http\Controllers\Api\V1\BonusesController;
use App\Http\Controllers\Api\V1\NotificationsController;
use App\Http\Controllers\Api\V1\BannersController;
use App\Http\Controllers\Api\V1\GroupsController;
use App\Http\Controllers\Api\V1\EventsController;
use App\Http\Controllers\Api\V1\ChatsController;
use App\Http\Controllers\Api\V1\LiveSessionsController;
use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Admin\UserManagementController;
use App\Http\Controllers\Api\V1\Admin\CatchManagementController;
use App\Http\Controllers\Api\V1\Admin\PointManagementController;
use App\Http\Controllers\Api\V1\Admin\ReportManagementController;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/profile/me', [ProfileController::class, 'me'])->middleware('auth:api');

    // OAuth
    Route::get('/auth/{provider}/redirect', [OAuthController::class, 'redirect']);
    Route::get('/auth/{provider}/callback', [OAuthController::class, 'callback']);

    // Feed/Catch
    Route::get('/feed', [FeedController::class, 'index']);
    Route::get('/catch/{id}', [CatchController::class, 'show']);
    Route::post('/catch', [CatchController::class, 'store'])->middleware('auth:api');
    Route::post('/catch/{id}/like', [CatchLikeController::class, 'toggle'])->middleware('auth:api');
    Route::post('/catch/{id}/comments', [CatchCommentController::class, 'store'])->middleware('auth:api');

    // Map/Points
    Route::get('/map/points', [PointsController::class, 'index']);
    Route::get('/points/{id}', [PointsController::class, 'show']);
    Route::post('/points', [PointsController::class, 'store'])->middleware('auth:api');
    Route::get('/points/{id}/media', [PointsController::class, 'media']);

    // Weather favs
    Route::get('/weather/favs', [WeatherFavController::class, 'index'])->middleware('auth:api');
    Route::post('/weather/favs', [WeatherFavController::class, 'store'])->middleware('auth:api');

    // Ratings/Bonuses
    Route::post('/ratings', [RatingsController::class, 'store'])->middleware('auth:api');
    Route::get('/bonuses', [BonusesController::class, 'index'])->middleware('auth:api');

    // Notifications
    Route::get('/notifications', [NotificationsController::class, 'index'])->middleware('auth:api');
    Route::post('/notifications/{id}/read', [NotificationsController::class, 'read'])->middleware('auth:api');

  // Banners
  Route::get('/banners', [BannersController::class, 'index']);

  // Groups
  Route::get('/groups', [GroupsController::class, 'index']);
  Route::get('/groups/{id}', [GroupsController::class, 'show']);
  Route::post('/groups', [GroupsController::class, 'store'])->middleware('auth:api');
  Route::post('/groups/{id}/join', [GroupsController::class, 'join'])->middleware('auth:api');
  Route::post('/groups/{id}/leave', [GroupsController::class, 'leave'])->middleware('auth:api');

  // Events
  Route::get('/events', [EventsController::class, 'index']);
  Route::get('/events/{id}', [EventsController::class, 'show']);
  Route::post('/events', [EventsController::class, 'store'])->middleware('auth:api');
  Route::post('/events/{id}/join', [EventsController::class, 'join'])->middleware('auth:api');
  Route::post('/events/{id}/leave', [EventsController::class, 'leave'])->middleware('auth:api');

  // Chats
  Route::get('/chats', [ChatsController::class, 'index'])->middleware('auth:api');
  Route::get('/chats/{id}', [ChatsController::class, 'show'])->middleware('auth:api');
  Route::get('/chats/{id}/messages', [ChatsController::class, 'messages'])->middleware('auth:api');
  Route::post('/chats/{id}/messages', [ChatsController::class, 'sendMessage'])->middleware('auth:api');
  Route::post('/chats/{id}/read', [ChatsController::class, 'markAsRead'])->middleware('auth:api');

  // Live Sessions
  Route::get('/live-sessions', [LiveSessionsController::class, 'index']);
  Route::get('/live-sessions/{id}', [LiveSessionsController::class, 'show']);
  Route::post('/live-sessions', [LiveSessionsController::class, 'store'])->middleware('auth:api');
  Route::post('/live-sessions/{id}/start', [LiveSessionsController::class, 'start'])->middleware('auth:api');
  Route::post('/live-sessions/{id}/end', [LiveSessionsController::class, 'end'])->middleware('auth:api');
  Route::post('/live-sessions/{id}/join', [LiveSessionsController::class, 'join'])->middleware('auth:api');
  Route::post('/live-sessions/{id}/leave', [LiveSessionsController::class, 'leave'])->middleware('auth:api');

  // Admin routes
  Route::prefix('admin')->middleware(['auth:api', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/recent-activity', [AdminController::class, 'recentActivity']);

    // User Management
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::get('/users/{user}', [UserManagementController::class, 'show']);
    Route::put('/users/{user}', [UserManagementController::class, 'update']);
    Route::post('/users/{user}/toggle-block', [UserManagementController::class, 'toggleBlock']);
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);

    // Catch Management
    Route::get('/catches', [CatchManagementController::class, 'index']);
    Route::get('/catches/{catch}', [CatchManagementController::class, 'show']);
    Route::put('/catches/{catch}', [CatchManagementController::class, 'update']);
    Route::post('/catches/{catch}/toggle-block', [CatchManagementController::class, 'toggleBlock']);
    Route::delete('/catches/{catch}', [CatchManagementController::class, 'destroy']);

    // Point Management
    Route::get('/points', [PointManagementController::class, 'index']);
    Route::get('/points/{point}', [PointManagementController::class, 'show']);
    Route::put('/points/{point}', [PointManagementController::class, 'update']);
    Route::post('/points/{point}/toggle-block', [PointManagementController::class, 'toggleBlock']);
    Route::delete('/points/{point}', [PointManagementController::class, 'destroy']);

    // Report Management
    Route::get('/reports', [ReportManagementController::class, 'index']);
    Route::get('/reports/{report}', [ReportManagementController::class, 'show']);
    Route::post('/reports/{report}/review', [ReportManagementController::class, 'review']);
    Route::post('/reports/bulk-review', [ReportManagementController::class, 'bulkReview']);
    Route::get('/reports/statistics', [ReportManagementController::class, 'statistics']);
  });
});
