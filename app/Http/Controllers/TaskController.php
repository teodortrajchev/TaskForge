<?php

namespace App\Http\Controllers;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $projectIds = $request->user()->projects()->pluck('projects.id');

        $tasks = Task::whereIn('project_id', $projectIds)
            ->latest('created_at')
            ->get();

        return view('tasks.index', compact('tasks'));
    }

    public function create(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);

        return view('tasks.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
        ] + $this->assigneeRules($project));

        $assignees = Arr::pull($data, 'assignees', []);

        $task = Task::create($data + ['project_id' => $project->id]);

        $this->syncAssignees($request, $project, $task, $assignees);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task created.');
    }

    public function show(Request $request, Project $project, Task $task)
    {
        abort_unless($project->roleFor($request->user()), 403);
        abort_unless($task->project_id === $project->id, 404);

        $task->load('assignees');

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Request $request, Project $project, Task $task)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);
        abort_unless($task->project_id === $project->id, 404);

        $task->load('assignees');

        return view('tasks.edit', compact('project', 'task'));
    }

    public function complete(Request $request, Project $project, Task $task)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);
        abort_unless($task->project_id === $project->id, 404);

        $task->update(['status' => 'completed']);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task marked as finished.');
    }

    public function update(Request $request, Project $project, Task $task)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);
        abort_unless($task->project_id === $project->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:todo,pending,in_progress,completed'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
        ] + $this->assigneeRules($project));

        $assignees = Arr::pull($data, 'assignees', []);

        $task->update($data);

        $this->syncAssignees($request, $project, $task, $assignees);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task updated.');
    }
    public function updateStatus(Request $request, Project $project, Task $task)
    {
        $role = $project->roleFor($request->user());

        if (!$role?->canContribute()) {
            return response()->json([
                'message' => 'You are not allowed to update tasks in this project.'
            ], 403);
        }

        if ($task->project_id !== $project->id) {
            return response()->json([
                'message' => 'Task does not belong to this project.'
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:todo,in_progress,completed'],
        ]);

        $task->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'status' => $task->status,
        ]);
    }

    public function updateDueDate(Request $request, Project $project, Task $task)
    {
        $role = $project->roleFor($request->user());

        if (!$role?->canContribute()) {
            return response()->json([
                'message' => 'You are not allowed to update tasks in this project.'
            ], 403);
        }

        if ($task->project_id !== $project->id) {
            return response()->json([
                'message' => 'Task does not belong to this project.'
            ], 403);
        }

        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
        ]);

        $task->update([
            'due_date' => $validated['due_date'],
        ]);

        return response()->json([
            'success' => true,
            'due_date' => $task->due_date?->format('Y-m-d'),
        ]);
    }

    public function destroy(Request $request, Project $project, Task $task)
    {
        abort_unless($project->roleFor($request->user())?->canManage(), 403);
        abort_unless($task->project_id === $project->id, 404);

        $task->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task deleted.');
    }

    /** Assignees must be non-viewer members of this project. */
    private function assigneeRules(Project $project): array
    {
        return [
            'assignees' => ['nullable', 'array'],
            'assignees.*' => [
                'integer',
                'distinct',
                Rule::exists('project_user', 'user_id')->where(
                    fn ($q) => $q->where('project_id', $project->id)
                        ->where('role', '!=', ProjectRole::Viewer->value)
                ),
            ],
        ];
    }

    public function history(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user()), 403);

        $tasks = $project->tasks()
            ->with('assignees:id,name')
            ->latest()
            ->paginate(30);

        return view('projects.history', compact('project', 'tasks'));
    }

    /** Only owners and managers can change who a task is assigned to. */
    private function syncAssignees(Request $request, Project $project, Task $task, array $ids): void
    {
        if (! $project->roleFor($request->user())?->canAssignTasks()) {
            return;
        }

        $changes = $task->assignees()->sync($ids);

        // Newly added people should still get the due reminders.
        if ($changes['attached']) {
            $task->forceFill([
                'due_soon_notified_at' => null,
                'due_today_notified_at' => null,
            ])->saveQuietly();
        }
    }
}