<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Message $message,
        public Project $project,
        public ?Task $task = null,
        public int $count = 1,
    ) {}

    /** Identifies the board a message belongs to; used to fold and to mark as read. */
    public static function boardKey(Project $project, ?Task $task = null): string
    {
        return $task ? "task:{$task->id}" : "project:{$project->id}";
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $author = $this->message->user?->name ?? 'Someone';
        $place = $this->task
            ? "{$this->task->name} ({$this->project->name})"
            : $this->project->name;
        $preview = Str::limit(Str::squish($this->message->body), 100);

        $url = $this->task
            ? route('projects.tasks.show', [$this->project, $this->task], false)
            : route('projects.show', $this->project, false);

        return [
            'board_key' => self::boardKey($this->project, $this->task),
            'project_id' => $this->project->id,
            'task_id' => $this->task?->id,
            'message_id' => $this->message->id,
            'count' => $this->count,
            'title' => $this->count > 1
                ? "{$this->count} new messages in {$place}"
                : "New message in {$place}",
            'body' => $this->count > 1
                ? "Latest from {$author}: {$preview}"
                : "{$author}: {$preview}",
            'url' => $url . '#message-board',
        ];
    }
}