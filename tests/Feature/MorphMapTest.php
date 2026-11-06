<?php

namespace Tests\Feature;

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
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\TestCase;

/**
 * Every model that can be the subject of an annotation, the object of a thread or the receiver of a notice is stored
 * under a short alias, never under its class name.
 */
class MorphMapTest extends TestCase
{
    private const ALIASES = [
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

    public function test_every_subject_has_a_short_alias(): void
    {
        foreach (self::ALIASES as $alias => $class) {
            $this->assertSame($class, Relation::getMorphedModel($alias));
            $this->assertSame($alias, (new $class)->getMorphClass());
        }
    }

    public function test_the_aliases_fit_the_polymorphic_columns(): void
    {
        foreach (array_keys(Relation::morphMap()) as $alias) {
            $this->assertLessThanOrEqual(32, strlen($alias));
        }
    }

    public function test_the_map_is_not_enforced(): void
    {
        $this->assertFalse(Relation::requiresMorphMap());
    }
}
