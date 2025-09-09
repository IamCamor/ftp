<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\UserRegistered;
use App\Events\CatchRecorded;
use App\Events\PaymentCompleted;
use App\Events\PointCreated;
use App\Events\CommentAdded;
use App\Events\LikeGiven;
use App\Events\FriendAdded;
use App\Events\BonusAwarded;
use App\Events\ContentModerationRequested;
use App\Events\ContentModerationCompleted;
use App\Listeners\SendTelegramNotification;
use App\Listeners\AwardCatchBonus;
use App\Listeners\AwardPointBonus;
use App\Listeners\AwardCommentBonus;
use App\Listeners\AwardLikeBonus;
use App\Listeners\AwardFriendBonus;
use App\Listeners\SendBonusNotification;
use App\Listeners\ProcessContentModeration;
use App\Listeners\HandleModerationResult;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserRegistered::class => [
            SendTelegramNotification::class . '@handleUserRegistered',
        ],
        CatchRecorded::class => [
            SendTelegramNotification::class . '@handleCatchRecorded',
        ],
        PaymentCompleted::class => [
            SendTelegramNotification::class . '@handlePaymentCompleted',
        ],
        PointCreated::class => [
            SendTelegramNotification::class . '@handlePointCreated',
            AwardPointBonus::class,
        ],
        CommentAdded::class => [
            AwardCommentBonus::class,
        ],
        LikeGiven::class => [
            AwardLikeBonus::class,
        ],
        FriendAdded::class => [
            AwardFriendBonus::class,
        ],
        CatchRecorded::class => [
            SendTelegramNotification::class . '@handleCatchRecorded',
            AwardCatchBonus::class,
        ],
        BonusAwarded::class => [
            SendBonusNotification::class,
        ],
        ContentModerationRequested::class => [
            ProcessContentModeration::class,
        ],
        ContentModerationCompleted::class => [
            HandleModerationResult::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
