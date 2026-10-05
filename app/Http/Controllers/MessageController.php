<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Notifications\MessageNotifier;

class MessageController extends Controller
{
    private const PAGE_SIZE = 50;

    // $task is null for the project board, set for a task board.

    public function index(Request $request, Project $project, ?Task $task = null): JsonResponse
    {
        abort_unless($project->roleFor($request->user()), 403);
        abort_if($task && $task->project_id !== $project->id, 404);
        MessageNotifier::markBoardRead($request->user(), $project, $task);

        $messages = $project->messages()
            ->when(
                $task,
                fn ($q) => $q->where('task_id', $task->id),
                fn ($q) => $q->whereNull('task_id'),
            )
            ->with('user:id,name')
            ->latest('id')
            ->limit(self::PAGE_SIZE)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (Message $m) => $this->present($m, $project, $request->user()));

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, Project $project, ?Task $task = null): JsonResponse
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);
        abort_if($task && $task->project_id !== $project->id, 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $project->messages()->create([
            'task_id' => $task?->id,
            'user_id' => $request->user()->id,
            'body' => trim($data['body']),
        ])->load('user:id,name');
        MessageNotifier::send($message, $project, $task);
        return response()->json($this->present($message, $project, $request->user()), 201);
    }

    public function destroy(Request $request, Project $project, Message $message, ?Task $task = null): JsonResponse
    {
        abort_unless($message->project_id === $project->id, 404);
        abort_unless($message->task_id === $task?->id, 404);
        abort_unless($this->canDelete($message, $project, $request->user()), 403);

        $message->delete();

        return response()->json(['deleted' => true]);
    }

    private function canDelete(Message $message, Project $project, User $user): bool
    {
        $role = $project->roleFor($user);

        if (! $role) {
            return false;
        }

        return $message->user_id === $user->id || $role->canManage();
    }

    private function present(Message $message, Project $project, User $viewer): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'author' => $message->user?->name ?? 'Deleted user',
            'mine' => $message->user_id === $viewer->id,
            'created_at' => $message->created_at->toIso8601String(),
            'can_delete' => $this->canDelete($message, $project, $viewer),
        ];
    }
}