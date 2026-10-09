<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const WEEKS = 8;

    public function index(Request $request)
    {
        $user = $request->user();

        $projects = $user->projects()->orderBy('projects.name')->get(['projects.id', 'projects.name', 'projects.status']);

        // single-project filter
        $selected = $projects->firstWhere('id', $request->integer('project'));
        $scope = $selected ? collect([$selected]) : $projects;
        $projectIds = $scope->pluck('id');

        $today = now()->toDateString();
        $tasks = fn () => Task::whereIn('tasks.project_id', $projectIds);

        $activeIds = $scope->where('status', 'active')->pluck('id');
        $openActive = fn () => Task::whereIn('tasks.project_id', $activeIds)->where('tasks.status', '!=', 'completed');

        $byStatus = $tasks()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $completed = (int) ($byStatus['completed'] ?? 0);
        $inProgress = (int) ($byStatus['in_progress'] ?? 0);
        $total = (int) $byStatus->sum();
        $todo = $total - $completed - $inProgress;

        $projectProgress = $tasks()
            ->selectRaw("project_id, count(*) as total, sum(case when status = 'completed' then 1 else 0 end) as done")
            ->groupBy('project_id')
            ->get()
            ->keyBy('project_id');

        $progress = $scope->map(function ($p) use ($projectProgress) {
            $row = $projectProgress->get($p->id);
            $t = (int) ($row->total ?? 0);
            $d = (int) ($row->done ?? 0);

            return [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'total' => $t,
                'done' => $d,
                'percent' => $t ? (int) round($d / $t * 100) : 0,
            ];
        })->values();

        $overdueQuery = fn () => $openActive()->whereDate('tasks.due_date', '<', $today);
        $overdueCount = $overdueQuery()->count();
        $overdue = $overdueQuery()
            ->with(['project:id,name', 'assignees:id,name'])
            ->orderBy('tasks.due_date')
            ->limit(10)
            ->get();

        $workload = DB::table('task_user')
            ->join('tasks', 'tasks.id', '=', 'task_user.task_id')
            ->join('users', 'users.id', '=', 'task_user.user_id')
            ->whereIn('tasks.project_id', $activeIds)
            ->groupBy('users.id', 'users.name')
            ->selectRaw(
                "users.id, users.name,
                 sum(case when tasks.status = 'in_progress' then 1 else 0 end) as in_progress,
                 sum(case when tasks.status not in ('in_progress', 'completed') then 1 else 0 end) as todo,
                 sum(case when tasks.status != 'completed' and tasks.due_date < ? then 1 else 0 end) as overdue,
                 sum(case when tasks.status = 'completed' then 1 else 0 end) as done",
                [$today]
            )
            ->get()
            ->map(fn ($r) => (object) [
                'id' => $r->id,
                'name' => $r->name,
                'todo' => (int) $r->todo,
                'in_progress' => (int) $r->in_progress,
                'overdue' => (int) $r->overdue,
                'done' => (int) $r->done,
                'open' => (int) $r->todo + (int) $r->in_progress,
            ])
            ->sortByDesc('open')
            ->values();

        $unassigned = $openActive()->doesntHave('assignees')->count();
        $maxOpen = max(1, $workload->max('open') ?? 0, $unassigned);

        // Weekly trend 
        $firstWeek = now()->startOfWeek()->subWeeks(self::WEEKS - 1);
        $weeks = collect(range(0, self::WEEKS - 1))->map(fn ($i) => $firstWeek->copy()->addWeeks($i));
        $bucket = fn ($dates) => $weeks->map(
            fn ($w) => $dates->filter(fn ($d) => $d >= $w && $d < $w->copy()->addWeek())->count()
        )->all();

        $createdAt = $tasks()->where('tasks.created_at', '>=', $firstWeek)->pluck('created_at');
        $completedAt = $tasks()->whereNotNull('completed_at')->where('completed_at', '>=', $firstWeek)->pluck('completed_at');

        $charts = [
            'status' => [
                'labels' => ['To do', 'In progress', 'Completed'],
                'data' => [$todo, $inProgress, $completed],
            ],
            'trend' => [
                'labels' => $weeks->map(fn ($w) => $w->format('M j'))->all(),
                'created' => $bucket($createdAt),
                'completed' => $bucket($completedAt),
            ],
        ];

        return view('dashboard', [
            'projects' => $projects,
            'selected' => $selected,
            'stats' => [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'todo' => $todo,
                'overdue' => $overdueCount,
                'percent' => $total ? (int) round($completed / $total * 100) : 0,
            ],
            'progress' => $progress,
            'overdue' => $overdue,
            'workload' => $workload,
            'unassigned' => $unassigned,
            'maxOpen' => $maxOpen,
            'charts' => $charts,
            'today' => now()->startOfDay(),
        ]);
    }
}