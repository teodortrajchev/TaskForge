<?php

namespace App\Http\Controllers;

use App\Enums\ProjectRole;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()->projects()->latest('projects.created_at')->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
            'github_url' => ['nullable', 'url:https', 'max:255', 'starts_with:https://github.com/'],
        ]);

        $user = $request->user();

        $project = DB::transaction(function () use ($data, $user) {
            $project = Project::create($data + ['owner_id' => $user->id]);
            $project->members()->attach($user->id, ['role' => ProjectRole::Owner->value]);

            return $project;
        });

        return redirect()->route('projects.show', $project)->with('status', 'Project created.');
    }

    public function list_commits(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:255'],
        ]);

        try {
            return response()->json([
                'commits' => $this->fetchCommits($validated['url']),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    private function fetchCommits(string $url): array
    {
        $url = trim($url);
        $pattern = '#^https?://(?:www\.)?github\.com/([A-Za-z0-9_-]+)/([A-Za-z0-9_.-]+?)(?:\.git)?/?$#i';

        if (!preg_match($pattern, $url, $matches)) {
            throw new InvalidArgumentException(
                'Enter a GitHub repository URL, like https://github.com/owner/repo.'
            );
        }

        $http = Http::acceptJson()
            ->withUserAgent(config('app.name', 'Laravel'))
            ->timeout(8);

        if ($token = config('services.github.token')) {
            $http = $http->withToken($token);
        }

        try {
            $response = $http->get(
                "https://api.github.com/repos/{$matches[1]}/{$matches[2]}/commits",
                ['per_page' => 100]
            );
        } catch (Throwable $e) {
            report($e);
            throw new RuntimeException('Could not reach GitHub. Try again in a moment.', 0, $e);
        }

        if ($response->failed()) {
            throw new RuntimeException(
                "GitHub returned an error (HTTP {$response->status()})."
            );
        }

        return collect($response->json())->map(fn ($commit) => [
            'sha' => $commit['sha'],
            'short_sha' => substr($commit['sha'], 0, 7),
            'message' => explode("\n", data_get($commit, 'commit.message', ''))[0],
            'author' => data_get($commit, 'author.login') ?? data_get($commit, 'commit.author.name'),
            'avatar_url' => data_get($commit, 'author.avatar_url'),
            'date' => data_get($commit, 'commit.author.date'),
            'url' => $commit['html_url'],
        ])->values()->all();
    }

    public function show(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user()), 403);

        $project->load('members', 'tasks.assignees', 'files.user:id,name');
        $invitations = $project->roleFor($request->user())->canManage()
            ? $project->invitations()->pending()->latest()->get()
            : collect();
        $commits = [];
        $commitError = null;

        if ($project->github_url) {
            try {
                $commits = $this->fetchCommits($project->github_url);
            } catch (RuntimeException $e) {
                $commitError = $e->getMessage();
            }
        }

        return view('projects.show', compact('project', 'invitations', 'commits', 'commitError'));
    }

        public function updateStatus(Request $request, Project $project)
        {
            abort_unless($project->roleFor($request->user())?->canManage(), 403);

            $data = $request->validate([
                'status' => ['required', 'string', 'in:active,completed'],
            ]);

            $project->update($data);

            return redirect()
                ->route('projects.show', $project)
                ->with('status', $data['status'] === 'completed'
                    ? 'Project marked as finished.'
                    : 'Project reopened.');
        }




    public function githubRepo(Request $request)
    {
        $request->validate([
            'url' => ['required', 'string', 'max:255'],
        ]);

        $url = trim($request->input('url'));

        $pattern = '#^https?://(?:www\.)?github\.com/([A-Za-z0-9_-]+)/([A-Za-z0-9_.-]+?)(?:\.git)?/?$#i';

        if (!preg_match($pattern, $url, $m)) {
            return response()->json([
                'message' => 'Enter a GitHub repository URL, like https://github.com/owner/repo.',
            ], 422);
        }

        $owner = $m[1];
        $repo = $m[2];

        $http = Http::acceptJson()
            ->withUserAgent(config('app.name', 'Laravel'))
            ->timeout(8);

        if ($token = config('services.github.token')) {
            $http = $http->withToken($token);
        }

        try {
            $response = $http->get(
                "https://api.github.com/repos/{$owner}/{$repo}"
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not reach GitHub. Try again in a moment.',], 502);
        }

        if ($response->status() === 404) {
            return response()->json([ 'message' => 'Repository not found (or it is private).',], 404);
        }

        if (in_array($response->status(), [403, 429])) {
            return response()->json([  'message' => 'GitHub rate limit reached. Try again later.',], 429);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'GitHub returned an error.',
                'github_status' => $response->status(),
                'github_response' => $response->json(),], 502);
        }

        return response()->json([
            'name' => $response->json('name'),
            'description' => $response->json('description') ?? '',
            'url' => $response->json('html_url'),
        ]);
    }



    public function destroy(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user())?->canDeleteProject(), 403);

        $name = $project->name;

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', "Project \"{$name}\" was deleted.");
    }


    
}
?>