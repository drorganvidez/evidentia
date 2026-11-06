<?php

namespace App\Providers;

use App\Models\Attendee;
use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\DefaultList;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\SignatureSheet;
use App\Models\User;
use App\Services\Activity\ActivityRecorder;
use App\Services\Deadlines;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Short and stable names for the polymorphic columns (activity_log.subject_type, message_threads.about_type,
     * notifications.notifiable_type). Not enforced: a subject left out keeps its class name instead of failing.
     */
    public const MORPH_MAP = [
        'evidence' => Evidence::class,
        'meeting_request' => MeetingRequest::class,
        'signature_sheet' => SignatureSheet::class,
        'meeting_minutes' => MeetingMinutes::class,
        'meeting' => Meeting::class,
        'bonus' => Bonus::class,
        'default_list' => DefaultList::class,
        'committee' => Committee::class,
        'user' => User::class,
        'event' => Event::class,
        'attendee' => Attendee::class,
        'configuration' => Configuration::class,
        'message_thread' => MessageThread::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // one per request (and per job of a worker); the request id and the configuration hang from the request itself
        $this->app->scoped(ActivityRecorder::class);
        $this->app->scoped(Deadlines::class);
    }

    /**
     * Bootstrap any application services.
     * The https scheme behind the reverse proxies comes from the trusted proxies (bootstrap/app.php).
     */
    public function boot(): void
    {
        Relation::morphMap(self::MORPH_MAP);
    }
}
