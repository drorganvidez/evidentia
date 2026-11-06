<?php

namespace App\Services\Messaging;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Evidence;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use App\Services\Activity\SubjectInfo;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Opens, answers, resolves and reopens the threads. Every change goes with its annotation inside one transaction,
 * and the notices come out of the annotation once it is committed.
 */
final class Messaging
{
    public function open(User $author, Audience $to, string $subject, string $body, ?Model $about): MessageThread
    {
        $role = self::roleFor($author, $to, $about);
        $aboutId = $about === null ? null : SubjectInfo::rootId($about);

        return DB::transaction(function () use ($author, $to, $subject, $body, $about, $aboutId, $role): MessageThread {
            $now = now();
            $thread = MessageThread::create([
                'subject' => $subject,
                'author_id' => $author->id,
                'author_name' => SubjectInfo::personName($author),
                'author_role' => $role,
                'recipient_role' => $to->role,
                'committee_id' => $to->committeeId,
                'about_type' => $about?->getMorphClass(),
                'about_id' => $aboutId,
                'about_title' => $about === null ? null : mb_substr(SubjectInfo::title($about), 0, 255),
                'state' => 'open',
                'last_message_at' => $now,
                'last_message_by' => $author->id,
            ]);
            $this->write($thread, $author, $role, $body);
            $this->markRead($thread, $author);

            Activity::record(
                ActivityAction::MessageSent,
                $thread,
                details: [
                    'audience' => $to->key(),
                    'about' => $about === null ? null : ['type' => $about->getMorphClass(), 'id' => $aboutId, 'title' => $thread->about_title],
                    'length' => mb_strlen($body),
                ],
                targets: $to->members()->reject(fn (User $user) => $user->is($author)),
                actingRole: $role,
                committeeId: false,
            );

            return $thread;
        });
    }

    public function reply(MessageThread $thread, User $author, string $body): Message
    {
        $role = self::roleFor($author, $thread, null);

        return DB::transaction(function () use ($thread, $author, $body, $role): Message {
            $message = $this->write($thread, $author, $role, $body);
            $thread->update(['last_message_at' => $message->created_at, 'last_message_by' => $author->id]);
            $this->markRead($thread, $author);

            Activity::record(
                ActivityAction::MessageReplied,
                $thread,
                details: ['length' => mb_strlen($body), 'message_id' => $message->id],
                targets: $thread->participants()->reject(fn (User $user) => $user->is($author)),
                actingRole: $role,
                committeeId: false,
            );

            return $message;
        });
    }

    public function resolve(MessageThread $thread, User $by): MessageThread
    {
        return $this->changeState($thread, $by, 'resolved');
    }

    public function reopen(MessageThread $thread, User $by): MessageThread
    {
        return $this->changeState($thread, $by, 'open');
    }

    /** The reader is up to date with the thread; no annotation. */
    public function markRead(MessageThread $thread, User $reader): void
    {
        DB::table('message_thread_reads')->updateOrInsert(
            ['message_thread_id' => $thread->id, 'user_id' => $reader->id],
            ['read_at' => now()],
        );
    }

    /**
     * The role someone writes with: the one of the audience they belong to, a lecturer stepping in, or, for the
     * author, student when they are one or the thread is about their own evidence and their highest role otherwise.
     */
    public static function roleFor(User $writer, MessageThread|Audience $context, ?Model $about): string
    {
        $audience = $context instanceof MessageThread ? $context->audience() : $context;
        $isAuthor = $context instanceof Audience || (int) $context->author_id === (int) $writer->id;

        if (! $isAuthor && $audience->includes($writer)) {
            return $audience->role === 'PRESIDENT' && ! $writer->hasRole('PRESIDENT') ? 'LECTURE' : $audience->role;
        }
        if (! $isAuthor && $writer->hasRole('LECTURE')) {
            return 'LECTURE';
        }

        $about ??= $context instanceof MessageThread && $context->about_type === 'evidence' ? Evidence::find($context->about_id) : null;
        if ($writer->hasRole('STUDENT') || ($about instanceof Evidence && (int) $about->user_id === (int) $writer->id)) {
            return 'STUDENT';
        }

        return Roles::highest($writer) ?? 'STUDENT';
    }

    private function changeState(MessageThread $thread, User $by, string $state): MessageThread
    {
        return DB::transaction(function () use ($thread, $by, $state): MessageThread {
            $thread->update($state === 'resolved'
                ? ['state' => 'resolved', 'resolved_at' => now(), 'resolved_by' => $by->id]
                : ['state' => 'open', 'resolved_at' => null, 'resolved_by' => null]);

            Activity::record(
                $state === 'resolved' ? ActivityAction::MessageResolved : ActivityAction::MessageReopened,
                $thread,
                $thread->author?->is($by) ? null : $thread->author,
                actingRole: self::roleFor($by, $thread, null),
                committeeId: false,
            );

            return $thread;
        });
    }

    private function write(MessageThread $thread, User $author, string $role, string $body): Message
    {
        return Message::create([
            'message_thread_id' => $thread->id,
            'user_id' => $author->id,
            'user_name' => SubjectInfo::personName($author),
            'role' => $role,
            'body' => $body,
        ]);
    }
}
