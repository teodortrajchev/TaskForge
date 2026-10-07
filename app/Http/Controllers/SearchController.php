<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim($data['q'] ?? '');
        $projects = null;
        $tasks = null;

        if ($q !== '') {
            // Escape LIKE wildcards so "100%" or "a_b" are searched literally.
            $like = '%' . addcslashes($q, '\\%_') . '%';

            // Only search inside projects the user is a member of.
            $projectIds = $request->user()->projects()->pluck('projects.id');

            $projects = Project::whereIn('id', $projectIds)
                ->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like))
                ->orderBy('name')
                ->paginate(10, ['*'], 'projects_page')
                ->withQueryString();

            $tasks = Task::whereIn('project_id', $projectIds)
                ->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like))
                ->with('project:id,name')
                ->orderBy('name')
                ->paginate(10, ['*'], 'tasks_page')
                ->withQueryString();
        }

        return view('search.index', compact('q', 'projects', 'tasks'));
    }
}