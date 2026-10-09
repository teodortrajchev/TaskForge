<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            @if ($projects->isNotEmpty())
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm text-gray-600">
                    <label for="dashboard-project">{{ __('Project') }}:</label>
                    <select id="dashboard-project" name="project" onchange="this.form.submit()"
                            class="text-sm border-gray-300 rounded-md py-1">
                        <option value="">{{ __('All projects') }}</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" @selected($selected?->id === $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($projects->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                    <h3 class="text-sm font-medium text-gray-900">{{ __('No projects yet') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Create a project to start tracking progress.') }}</p>
                    <div class="mt-6">
                        <a href="{{ route('projects.create') }}">
                            <x-primary-button>{{ __('New Project') }}</x-primary-button>
                        </a>
                    </div>
                </div>
            @else
                {{-- Summary cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm text-gray-500">{{ __('Total tasks') }}</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm text-gray-500">{{ __('Completed') }}</p>
                        <p class="mt-1 text-3xl font-semibold text-green-600">
                            {{ $stats['completed'] }}
                            <span class="text-sm font-normal text-gray-500">({{ $stats['percent'] }}%)</span>
                        </p>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm text-gray-500">{{ __('In progress') }}</p>
                        <p class="mt-1 text-3xl font-semibold text-indigo-600">{{ $stats['in_progress'] }}</p>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm text-gray-500">{{ __('Overdue') }}</p>
                        <p @class(['mt-1 text-3xl font-semibold', 'text-red-600' => $stats['overdue'] > 0, 'text-gray-900' => $stats['overdue'] === 0])>
                            {{ $stats['overdue'] }}
                        </p>
                    </div>
                </div>

                {{-- Progress charts --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">{{ __('Tasks by status') }}</h3>
                        @if ($stats['total'] > 0)
                            <div class="mx-auto max-w-[260px]"><canvas id="status-chart"></canvas></div>
                        @else
                            <p class="text-sm text-gray-500">{{ __('No tasks yet.') }}</p>
                        @endif
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">{{ __('Created vs. completed (last 8 weeks)') }}</h3>
                        <div class="relative h-64"><canvas id="trend-chart"></canvas></div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">{{ __('Project progress') }}</h3>
                    <ul class="space-y-4">
                        @foreach ($progress as $row)
                            <li>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <a href="{{ route('projects.show', $row['id']) }}" class="font-medium text-indigo-600 hover:underline truncate">
                                        {{ $row['name'] }}
                                    </a>
                                    <span class="text-gray-500 ms-4 whitespace-nowrap">
                                        {{ $row['done'] }}/{{ $row['total'] }} · {{ $row['percent'] }}%
                                    </span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100 overflow-hidden" role="progressbar"
                                     aria-valuenow="{{ $row['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="h-full bg-green-500" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Overdue tasks --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-baseline justify-between mb-4">
                            <h3 class="text-base font-semibold text-gray-900">{{ __('Overdue tasks') }}</h3>
                            @if ($stats['overdue'] > $overdue->count())
                                <span class="text-xs text-gray-500">
                                    {{ __('Showing :shown of :total', ['shown' => $overdue->count(), 'total' => $stats['overdue']]) }}
                                </span>
                            @endif
                        </div>

                        @if ($overdue->isEmpty())
                            <p class="text-sm text-gray-500">{{ __('Nothing is overdue. Nice work!') }}</p>
                        @else
                            <ul class="divide-y divide-gray-100">
                                @foreach ($overdue as $task)
                                    <li class="py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <a href="{{ route('projects.tasks.show', [$task->project_id, $task]) }}"
                                                   class="text-sm font-medium text-blue-600 hover:underline">{{ $task->name }}</a>
                                                <p class="text-xs text-gray-500 mt-0.5 truncate">
                                                    {{ $task->project->name }}
                                                    · {{ $task->assignees->pluck('name')->join(', ') ?: __('Unassigned') }}
                                                </p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                    @php($days = (int) $task->due_date->diffInDays($today)){{ trans_choice(':count day overdue|:count days overdue', $days, ['count' => $days]) }}
                                                </span>
                                                <p class="text-xs text-gray-400 mt-0.5">{{ $task->due_date->format('M j, Y') }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Workload per user --}}
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-1">{{ __('Workload per user') }}</h3>
                        <p class="text-xs text-gray-500 mb-4">{{ __('Open tasks in active projects.') }}</p>

                        @if ($workload->isEmpty() && $unassigned === 0)
                            <p class="text-sm text-gray-500">{{ __('No open tasks.') }}</p>
                        @else
                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                                <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-gray-400"></span>{{ __('To do') }}</span>
                                <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-indigo-500"></span>{{ __('In progress') }}</span>
                            </div>

                            <ul class="space-y-3">
                                @foreach ($workload as $row)
                                    <li>
                                        <div class="flex items-center justify-between text-sm mb-1">
                                            <span class="font-medium text-gray-800 truncate">{{ $row->name }}</span>
                                            <span class="text-gray-500 ms-4 whitespace-nowrap text-xs">
                                                {{ $row->open }} {{ __('open') }}
                                                @if ($row->overdue > 0)
                                                    · <span class="text-red-600">{{ $row->overdue }} {{ __('overdue') }}</span>
                                                @endif
                                                · {{ $row->done }} {{ __('done') }}
                                            </span>
                                        </div>
                                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden flex">
                                            <div class="h-full bg-gray-400" style="width: {{ $row->todo / $maxOpen * 100 }}%"></div>
                                            <div class="h-full bg-indigo-500" style="width: {{ $row->in_progress / $maxOpen * 100 }}%"></div>
                                        </div>
                                    </li>
                                @endforeach

                                @if ($unassigned > 0)
                                    <li>
                                        <div class="flex items-center justify-between text-sm mb-1">
                                            <span class="italic text-gray-500">{{ __('Unassigned') }}</span>
                                            <span class="text-gray-500 text-xs">{{ $unassigned }} {{ __('open') }}</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                            <div class="h-full bg-gray-300" style="width: {{ $unassigned / $maxOpen * 100 }}%"></div>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>

                <script id="dashboard-data" type="application/json">@json($charts)</script>
                @vite('resources/js/dashboard.js')
            @endif
        </div>
    </div>
</x-app-layout>