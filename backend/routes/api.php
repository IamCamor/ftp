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
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReferenceController;
use App\Http\Controllers\Api\V1\FishSpeciesController;
use App\Http\Controllers\Api\V1\LandingPageController;
use App\Http\Controllers\Api\V1\PushNotificationController;
use App\Http\Controllers\Api\V1\FollowController;
use App\Http\Controllers\Api\V1\OnlineStatusController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\BonusController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\ModerationController;
use App\Http\Controllers\Api\V1\WatchController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\EventSubscriptionController;
use App\Http\Controllers\Api\V1\EventNewsController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\TelegramController;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/profile/me', [ProfileController::class, 'me'])->middleware('auth:api');

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);

    // OAuth
    Route::get('/auth/{provider}/redirect', [OAuthController::class, 'redirect']);
    Route::get('/auth/{provider}/callback', [OAuthController::class, 'callback']);

  // Feed/Catch
  Route::get('/feed', [FeedController::class, 'index']);
  Route::get('/feed/personal', [FeedController::class, 'personal']);
  Route::get('/feed/nearby', [FeedController::class, 'nearby']);
  Route::get('/feed/following', [FeedController::class, 'following']);
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
    
    // Landing pages management
    Route::post('/landing-pages', [LandingPageController::class, 'store']);
    Route::put('/landing-pages/{landingPage}', [LandingPageController::class, 'update']);
    Route::delete('/landing-pages/{landingPage}', [LandingPageController::class, 'destroy']);
    
    // Push notifications management
    Route::post('/notifications/send', [PushNotificationController::class, 'send']);
    Route::get('/notifications/statistics', [PushNotificationController::class, 'statistics']);
  });

  // Subscription routes
  Route::middleware('auth:api')->group(function () {
    Route::get('/subscriptions/plans', [SubscriptionController::class, 'plans']);
    Route::get('/subscriptions/status', [SubscriptionController::class, 'status']);
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show']);
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/{subscription}/extend', [SubscriptionController::class, 'extend']);
  });

  // Payment routes
  Route::middleware('auth:api')->group(function () {
    Route::get('/payments/methods', [PaymentController::class, 'methods']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    Route::post('/payments/process', [PaymentController::class, 'process']);
    Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel']);
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund']);
  });

  // Reference routes
  Route::get('/references', [ReferenceController::class, 'index']);
  Route::get('/references/search', [ReferenceController::class, 'search']);
  
  // Fish species routes
  Route::get('/fish-species', [FishSpeciesController::class, 'index']);
  Route::get('/fish-species/popular', [FishSpeciesController::class, 'popular']);
  Route::get('/fish-species/category/{category}', [FishSpeciesController::class, 'byCategory']);
  Route::get('/fish-species/protected', [FishSpeciesController::class, 'protected']);
  Route::get('/fish-species/search', [FishSpeciesController::class, 'search']);
  Route::get('/fish-species/{fishSpecies}', [FishSpeciesController::class, 'show']);

  // Landing pages routes
  Route::get('/landing-pages', [LandingPageController::class, 'index']);
  Route::get('/landing-pages/featured', [LandingPageController::class, 'featured']);
  Route::get('/landing-pages/search', [LandingPageController::class, 'search']);
  Route::get('/landing-pages/{landingPage}', [LandingPageController::class, 'show']);

  // Language routes
  Route::get('/languages', [LanguageController::class, 'index']);
  Route::get('/languages/by-region', [LanguageController::class, 'byRegion']);
  Route::get('/languages/switcher', [LanguageController::class, 'switcher']);
  Route::get('/languages/current', [LanguageController::class, 'current']);
  Route::get('/languages/detect', [LanguageController::class, 'detect']);
  Route::get('/languages/rtl', [LanguageController::class, 'rtl']);
  Route::get('/languages/config', [LanguageController::class, 'config']);

  // Push notification routes
  Route::middleware('auth:api')->group(function () {
    Route::get('/notifications', [PushNotificationController::class, 'index']);
    Route::post('/notifications/register-token', [PushNotificationController::class, 'registerToken']);
    Route::post('/notifications/unregister-token', [PushNotificationController::class, 'unregisterToken']);
  });

  // Follow routes
  Route::middleware('auth:api')->group(function () {
    Route::post('/follow', [FollowController::class, 'follow']);
    Route::post('/unfollow', [FollowController::class, 'unfollow']);
    Route::post('/follow/toggle', [FollowController::class, 'toggle']);
    Route::get('/follow/suggestions', [FollowController::class, 'suggestions']);
    Route::get('/users/{user}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{user}/following', [FollowController::class, 'following']);
    Route::get('/users/{user}/is-following', [FollowController::class, 'isFollowing']);
    Route::get('/users/{user}/mutual', [FollowController::class, 'mutual']);
  });

  // Online status routes
  Route::middleware('auth:api')->group(function () {
    Route::post('/online/update', [OnlineStatusController::class, 'update']);
    Route::post('/online/offline', [OnlineStatusController::class, 'offline']);
    Route::get('/online/status', [OnlineStatusController::class, 'status']);
    Route::get('/online/users', [OnlineStatusController::class, 'online']);
    Route::get('/online/recently-active', [OnlineStatusController::class, 'recentlyActive']);
    Route::get('/online/count', [OnlineStatusController::class, 'count']);

    // Bonus system
    Route::prefix('bonus')->group(function () {
      Route::get('/', [BonusController::class, 'index']);
      Route::get('/transactions', [BonusController::class, 'transactions']);
      Route::get('/amounts', [BonusController::class, 'amounts']);
      Route::get('/statistics', [BonusController::class, 'statistics']);
      Route::get('/leaderboard', [BonusController::class, 'leaderboard']);
      Route::post('/spend', [BonusController::class, 'spend']);
    });

    // Language management (authenticated users)
    Route::post('/languages/set', [LanguageController::class, 'set']);
    Route::get('/languages/user-preference', [LanguageController::class, 'userPreference']);

    // Moderation (authenticated users)
    Route::post('/moderation/moderate-text', [ModerationController::class, 'moderateText']);
    Route::post('/moderation/moderate-image', [ModerationController::class, 'moderateImage']);
    Route::post('/moderation/request', [ModerationController::class, 'requestModeration']);

    // Smart Watch API (authenticated users)
    Route::post('/watch/start-session', [WatchController::class, 'startSession']);
    Route::post('/watch/record-biometric', [WatchController::class, 'recordBiometricData']);
    Route::post('/watch/record-catch', [WatchController::class, 'recordCatch']);
    Route::get('/watch/session-status', [WatchController::class, 'getSessionStatus']);
    Route::get('/watch/biometric-stats', [WatchController::class, 'getUserBiometricStats']);

    // Events API (authenticated users)
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/nearby', [EventController::class, 'nearby']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);

    // Event Subscriptions API (authenticated users)
    Route::get('/events/subscriptions/my', [EventSubscriptionController::class, 'mySubscriptions']);
    Route::post('/events/{eventId}/subscribe', [EventSubscriptionController::class, 'subscribe']);
    Route::post('/events/{eventId}/unsubscribe', [EventSubscriptionController::class, 'unsubscribe']);
    Route::post('/events/{eventId}/hide', [EventSubscriptionController::class, 'hide']);
    Route::post('/events/{eventId}/unhide', [EventSubscriptionController::class, 'unhide']);
    Route::put('/events/{eventId}/subscription/settings', [EventSubscriptionController::class, 'updateSettings']);
    Route::post('/events/{eventId}/attendance/confirm', [EventSubscriptionController::class, 'confirmAttendance']);
    Route::post('/events/{eventId}/attendance/cancel', [EventSubscriptionController::class, 'cancelAttendance']);

    // Event News API (authenticated users)
    Route::get('/events/{eventId}/news', [EventNewsController::class, 'index']);
    Route::get('/events/{eventId}/news/{newsId}', [EventNewsController::class, 'show']);
    Route::post('/events/{eventId}/news', [EventNewsController::class, 'store']);
    Route::put('/events/{eventId}/news/{newsId}', [EventNewsController::class, 'update']);
    Route::delete('/events/{eventId}/news/{newsId}', [EventNewsController::class, 'destroy']);
    Route::post('/events/{eventId}/news/{newsId}/pin', [EventNewsController::class, 'pin']);
    Route::post('/events/{eventId}/news/{newsId}/unpin', [EventNewsController::class, 'unpin']);
  });
});

// Webhook routes (no authentication required)
Route::post('/webhook/github', [WebhookController::class, 'github']);
Route::get('/health', [WebhookController::class, 'health']);

// Admin routes
Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
  // Admin bonus management
  Route::get('/bonus/global-stats', [BonusController::class, 'globalStats']);
  Route::post('/bonus/award', [BonusController::class, 'award']);

  // Admin language management
  Route::get('/languages/statistics', [LanguageController::class, 'statistics']);
  Route::post('/languages/clear-cache', [LanguageController::class, 'clearCache']);

  // Admin moderation management
  Route::get('/moderation/statistics', [ModerationController::class, 'statistics']);
  Route::get('/moderation/pending', [ModerationController::class, 'pending']);
  Route::post('/moderation/approve', [ModerationController::class, 'approve']);
  Route::post('/moderation/reject', [ModerationController::class, 'reject']);
  Route::post('/moderation/clear-cache', [ModerationController::class, 'clearCache']);
  Route::get('/moderation/config', [ModerationController::class, 'config']);
  Route::post('/moderation/test-provider', [ModerationController::class, 'testProvider']);

  // Admin events management
  Route::get('/events/statistics', [EventController::class, 'adminStatistics']);
  Route::get('/events/pending', [EventController::class, 'adminPending']);
  Route::post('/events/{id}/approve', [EventController::class, 'adminApprove']);
  Route::post('/events/{id}/reject', [EventController::class, 'adminReject']);
  Route::get('/events/{id}/subscriptions', [EventController::class, 'adminSubscriptions']);
  Route::get('/events/{id}/news', [EventController::class, 'adminNews']);

  // Admin event news management
  Route::get('/events/{eventId}/news/pending', [EventNewsController::class, 'adminPending']);
  Route::post('/events/{eventId}/news/{newsId}/approve', [EventNewsController::class, 'adminApprove']);
  Route::post('/events/{eventId}/news/{newsId}/reject', [EventNewsController::class, 'adminReject']);
});

// Telegram routes
Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
Route::post('/telegram/set-webhook', [TelegramController::class, 'setWebhook']);
Route::get('/telegram/webhook-info', [TelegramController::class, 'getWebhookInfo']);
Route::delete('/telegram/webhook', [TelegramController::class, 'deleteWebhook']);
Route::post('/telegram/test', [TelegramController::class, 'testNotification']);
