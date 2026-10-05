<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Throwable;

class MessageNotifier
{
    /** Never throws: a notification problem must not make posting look failed. */
    public static function send(Message $message, Project $project, ?Task $task = null): void
    {
        $key = NewMessageNotification::boardKey($project, $task);

        foreach (self::recipients($message, $project, $task) as $user) {
            try {
                // Fold earlier unread notifications for this board into one.
                $stale = self::unreadFor($user, $key);
                $count = 1 + $stale->sum(fn ($n) => (int) ($n->data['count'] ?? 1));
                $stale->each->delete();

                $user->notify(new NewMessageNotification($message, $project, $task, $count));
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    public static function markBoardRead(User $user, Project $project, ?Task $task = null): void
    {
        self::unreadFor($user, NewMessageNotification::boardKey($project, $task))
            ->each->markAsRead();
    }

    /**
     * Project board: every member except the author.
     * Task board: assignees + anyone who already posted in that thread, except the author.
     */
    public static function recipients(Message $message, Project $project, ?Task $task = null): Collection
    {
        $people = $project->members;

        if ($task) {
            $involved = $task->assignees()->pluck('users.id')
                ->merge(
                    Message::where('task_id', $task->id)
                        ->whereNotNull('user_id')
                        ->distinct()
                        ->pluck('user_id')
                )
                ->unique()
                ->all();

            $people = $people->whereIn('id', $involved);
        }

        return $people->reject(fn (User $u) => $u->id === $message->user_id)->values();
    }

    private static function unreadFor(User $user, string $key): Collection
    {
        return $user->unreadNotifications()
            ->where('type', NewMessageNotification::class)
            ->get()
            ->filter(fn ($n) => ($n->data['board_key'] ?? null) === $key);
    }
}