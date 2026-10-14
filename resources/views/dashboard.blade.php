<x-app-layout>
    <div class="tf-dashboard">
        <div class="tf-shell">
            <div class="tf-header">
                <div>
                    <p class="tf-eyebrow">WORKSPACE OVERVIEW</p>
                    <h2 class="tf-page-title">{{ __('Dashboard') }}</h2>
                    <p class="tf-subtitle">{{ __("Here's what's happening with your projects today.") }}</p>
                </div>

                <div class="tf-header-actions">
                    @if ($projects->isNotEmpty())
                        <form method="GET" action="{{ route('dashboard') }}" class="tf-filter">
                            <label for="dashboard-project">{{ __('Project') }}</label>
                            <select id="dashboard-project" name="project" onchange="this.form.submit()">
                                <option value="">{{ __('All projects') }}</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p->id }}" @selected($selected?->id === $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <a class="tf-button tf-button-primary" href="{{ route('projects.create') }}">
                        <span aria-hidden="true">＋</span> {{ __('New Project') }}
                    </a>
                </div>
            </div>

            @if ($projects->isEmpty())
                <section class="tf-panel tf-empty">
                    <div class="tf-empty-icon">✦</div>
                    <p class="tf-eyebrow">YOUR WORKSPACE STARTS HERE</p>
                    <h3>{{ __('No projects yet') }}</h3>
                    <p>{{ __('Create a project to start tracking progress.') }}</p>
                    <a href="{{ route('projects.create') }}" class="tf-button tf-button-primary">{{ __('Create your first project') }}</a>
                </section>
            @else
                <section class="tf-quick-grid">
                    <a href="{{ route('projects.create') }}" class="tf-quick-card"><span class="tf-quick-icon tf-quick-blue">＋</span><span><strong>{{ __('Create a project') }}</strong><small>{{ __('Start a new project and invite your team.') }}</small></span><b>&#8599;&#xFE0E;</b></a>
                    <a href="{{ route('projects.index') }}" class="tf-quick-card"><span class="tf-quick-icon tf-quick-green">✓</span><span><strong>{{ __('Browse projects') }}</strong><small>{{ __('Review projects and keep work moving.') }}</small></span><b>&#8599;&#xFE0E;</b></a>
                </section>

                <section class="tf-stats-grid">
                    <article class="tf-stat tf-stat-blue">
                        <div class="tf-stat-top"><span class="tf-stat-icon">▣</span><span class="tf-stat-tag">{{ __('ALL TASKS') }}</span></div>
                        <p class="tf-stat-label">{{ __('Total tasks') }}</p>
                        <div class="tf-stat-bottom"><strong>{{ $stats['total'] }}</strong><span class="tf-stat-note">&#8599;&#xFE0E; {{ __('Workspace') }}</span></div>
                    </article>
                    <article class="tf-stat tf-stat-green">
                        <div class="tf-stat-top"><span class="tf-stat-icon">✓</span><span class="tf-stat-tag">{{ __('FINISHED') }}</span></div>
                        <p class="tf-stat-label">{{ __('Completed') }}</p>
                        <div class="tf-stat-bottom"><strong>{{ $stats['completed'] }}</strong><span class="tf-stat-note">{{ $stats['percent'] }}% {{ __('complete') }}</span></div>
                    </article>
                    <article class="tf-stat tf-stat-purple">
                        <div class="tf-stat-top"><span class="tf-stat-icon">◷</span><span class="tf-stat-tag">{{ __('ACTIVE') }}</span></div>
                        <p class="tf-stat-label">{{ __('In progress') }}</p>
                        <div class="tf-stat-bottom"><strong>{{ $stats['in_progress'] }}</strong><span class="tf-stat-note">{{ __('Keep going') }} →</span></div>
                    </article>
                    <article class="tf-stat tf-stat-red">
                        <div class="tf-stat-top"><span class="tf-stat-icon">!</span><span class="tf-stat-tag">{{ __('NEEDS ATTENTION') }}</span></div>
                        <p class="tf-stat-label">{{ __('Overdue') }}</p>
                        <div class="tf-stat-bottom"><strong>{{ $stats['overdue'] }}</strong><span class="tf-stat-note">{{ __('Past due date') }}</span></div>
                    </article>
                </section>

                <section class="tf-chart-grid">
                    <article class="tf-panel">
                        <div class="tf-panel-heading">
                            <div><p class="tf-eyebrow">{{ __('TASK BREAKDOWN') }}</p><h3>{{ __('Tasks by status') }}</h3></div>
                            <span class="tf-heading-icon">◔</span>
                        </div>
                        @if ($stats['total'] > 0)
                            <div class="tf-donut-wrap"><canvas id="status-chart"></canvas></div>
                        @else
                            <p class="tf-muted">{{ __('No tasks yet.') }}</p>
                        @endif
                    </article>

                    <article class="tf-panel tf-trend-panel">
                        <div class="tf-panel-heading">
                            <div><p class="tf-eyebrow">{{ __('YOUR MOMENTUM') }}</p><h3>{{ __('Created vs. completed') }}</h3><p class="tf-muted">{{ __('Last 8 weeks') }}</p></div>
                            <span class="tf-heading-icon">&#8599;&#xFE0E;</span>
                        </div>
                        <div class="tf-trend-chart"><canvas id="trend-chart"></canvas></div>
                    </article>
                </section>

                <section class="tf-main-grid">
                    <article class="tf-panel">
                        <div class="tf-panel-heading">
                            <div><p class="tf-eyebrow">{{ __('BIG PICTURE') }}</p><h3>{{ __('Project progress') }}</h3></div>
                            <span class="tf-heading-icon">▤</span>
                        </div>
                        @if (count($progress))
                            <ul class="tf-project-list">
                                @foreach ($progress as $row)
                                    <li class="tf-project-item">
                                        <div class="tf-project-line">
                                            <a href="{{ route('projects.show', $row['id']) }}" class="tf-project-name">{{ $row['name'] }}</a>
                                            <span class="tf-project-count">{{ $row['done'] }}/{{ $row['total'] }}</span>
                                        </div>
                                        <div class="tf-progress-track" role="progressbar" aria-valuenow="{{ $row['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="tf-progress-fill" style="width: {{ $row['percent'] }}%"></div>
                                        </div>
                                        <div class="tf-project-meta"><span>{{ $row['percent'] }}% {{ __('complete') }}</span><span>{{ __('Project progress') }}</span></div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="tf-muted">{{ __('No project progress to show yet.') }}</p>
                        @endif
                        <a href="{{ route('projects.index') }}" class="tf-text-link">{{ __('View all projects') }} <span>→</span></a>
                    </article>

                    <article class="tf-panel">
                        <div class="tf-panel-heading">
                            <div><p class="tf-eyebrow">{{ __('DEADLINES') }}</p><h3>{{ __('Overdue tasks') }}</h3></div>
                            <span class="tf-heading-icon tf-heading-alert">!</span>
                        </div>
                        @if ($overdue->isEmpty())
                            <div class="tf-success-state"><span>✓</span><div><strong>{{ __('All caught up!') }}</strong><p>{{ __('Nothing is overdue. Nice work!') }}</p></div></div>
                        @else
                            <ul class="tf-task-list">
                                @foreach ($overdue as $task)
                                    <li class="tf-task-item">
                                        <div class="tf-task-marker"></div>
                                        <div class="tf-task-content">
                                            <a href="{{ route('projects.tasks.show', [$task->project_id, $task]) }}" class="tf-task-name">{{ $task->name }}</a>
                                            <p class="tf-task-meta">{{ $task->project->name }}</p>
                                            <p class="tf-task-meta">{{ $task->assignees->pluck('name')->join(', ') ?: __('Unassigned') }}</p>
                                        </div>
                                        <div class="tf-task-due">
                                            @php($days = (int) $task->due_date->diffInDays($today))
                                            <span>{{ trans_choice(':count day overdue|:count days overdue', $days, ['count' => $days]) }}</span>
                                            <small>{{ $task->due_date->format('M j, Y') }}</small>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($stats['overdue'] > $overdue->count())
                                <p class="tf-muted tf-showing">{{ __('Showing :shown of :total', ['shown' => $overdue->count(), 'total' => $stats['overdue']]) }}</p>
                            @endif
                        @endif
                    </article>
                </section>

                <section class="tf-panel">
                    <div class="tf-panel-heading">
                        <div><p class="tf-eyebrow">{{ __('TEAM OVERVIEW') }}</p><h3>{{ __('Workload per user') }}</h3><p class="tf-muted">{{ __('Open tasks in active projects.') }}</p></div>
                        <span class="tf-heading-icon">◎</span>
                    </div>
                    @if ($workload->isEmpty() && $unassigned === 0)
                        <p class="tf-muted">{{ __('No open tasks.') }}</p>
                    @else
                        <div class="tf-legend"><span><i class="tf-legend-todo"></i>{{ __('To do') }}</span><span><i class="tf-legend-progress"></i>{{ __('In progress') }}</span></div>
                        <ul class="tf-workload-list">
                            @foreach ($workload as $row)
                                <li>
                                    <div class="tf-workload-heading"><strong>{{ $row->name }}</strong><span>{{ $row->open }} {{ __('open') }} · {{ $row->done }} {{ __('done') }} @if ($row->overdue > 0)<em>· {{ $row->overdue }} {{ __('overdue') }}</em>@endif</span></div>
                                    <div class="tf-progress-track tf-workload-track">
                                        <div class="tf-workload-todo" style="width: {{ $row->todo / $maxOpen * 100 }}%"></div>
                                        <div class="tf-workload-progress" style="width: {{ $row->in_progress / $maxOpen * 100 }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                            @if ($unassigned > 0)
                                <li>
                                    <div class="tf-workload-heading"><strong class="tf-unassigned">{{ __('Unassigned') }}</strong><span>{{ $unassigned }} {{ __('open') }}</span></div>
                                    <div class="tf-progress-track"><div class="tf-unassigned-fill" style="width: {{ $unassigned / $maxOpen * 100 }}%"></div></div>
                                </li>
                            @endif
                        </ul>
                    @endif
                </section>

                <script id="dashboard-data" type="application/json">@json($charts)</script>
                @vite('resources/js/dashboard.js')
            @endif
        </div>
    </div>
</x-app-layout>