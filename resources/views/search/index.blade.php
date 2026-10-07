<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Search') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <form method="GET" action="{{ route('search') }}" role="search" class="flex gap-3">
                <label for="search-q" class="sr-only">{{ __('Search') }}</label>
                <x-text-input id="search-q" name="q" type="search" :value="$q" maxlength="100"
                              class="block w-full" placeholder="Search projects and tasks..." autofocus />
                <x-primary-button>{{ __('Search') }}</x-primary-button>
            </form>

            <x-input-error :messages="$errors->get('q')" />

            @if ($q === '')
                <p class="text-sm text-gray-500">{{ __('Type a project or task name to search.') }}</p>
            @else

                {{-- Projects --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-4 py-3 sm:px-6 border-b border-gray-200 text-sm font-medium text-gray-900">
                        {{ __('Projects') }}
                        <span class="ml-1 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                            {{ $projects->total() }}
                        </span>
                    </div>

                    @if ($projects->isEmpty())
                        <p class="p-6 text-sm text-gray-500 italic">{{ __('No projects match') }} “{{ $q }}”.</p>
                    @else
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach ($projects as $project)
                                <li>
                                    <a href="{{ route('projects.show', $project) }}"
                                       class="block hover:bg-gray-50 transition ease-in-out duration-150">
                                        <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-indigo-600 truncate">{{ $project->name }}</p>
                                                @if ($project->description)
                                                    <p class="mt-1 text-sm text-gray-500 truncate">{{ $project->description }}</p>
                                                @endif
                                            </div>

                                            <span @class([
                                                'ms-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                                                'bg-green-100 text-green-800' => $project->status === 'active',
                                                'bg-gray-100 text-gray-800' => $project->status !== 'active',
                                            ])>
                                                {{ $project->status }}
                                            </span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if ($projects->hasPages())
                            <div class="px-4 py-3 sm:px-6 border-t border-gray-200">{{ $projects->links() }}</div>
                        @endif
                    @endif
                </div>

                {{-- Tasks --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-4 py-3 sm:px-6 border-b border-gray-200 text-sm font-medium text-gray-900">
                        {{ __('Tasks') }}
                        <span class="ml-1 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                            {{ $tasks->total() }}
                        </span>
                    </div>

                    @if ($tasks->isEmpty())
                        <p class="p-6 text-sm text-gray-500 italic">{{ __('No tasks match') }} “{{ $q }}”.</p>
                    @else
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach ($tasks as $task)
                                <li>
                                    <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}"
                                       class="block hover:bg-gray-50 transition ease-in-out duration-150">
                                        <div class="px-4 py-4 sm:px-6 flex items-center justify-between gap-4">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-indigo-600 truncate">{{ $task->name }}</p>
                                                <p class="mt-1 text-xs text-gray-500 truncate">
                                                    {{ __('in') }} {{ $task->project->name }}
                                                    @if ($task->due_date)
                                                        &middot; {{ __('Due') }} {{ $task->due_date->format('M d, Y') }}
                                                    @endif
                                                </p>
                                            </div>

                                            <div class="flex shrink-0 items-center gap-2">
                                                <span @class([
                                                    'px-2 py-0.5 rounded text-xs',
                                                    'bg-red-100 text-red-700' => $task->priority === 'high',
                                                    'bg-yellow-100 text-yellow-700' => $task->priority === 'medium',
                                                    'bg-gray-100 text-gray-700' => ! in_array($task->priority, ['high', 'medium']),
                                                ])>
                                                    {{ str($task->priority ?? 'medium')->headline() }}
                                                </span>

                                                <span @class([
                                                    'px-2 py-0.5 rounded-full text-xs font-medium',
                                                    'bg-green-100 text-green-800' => $task->status === 'completed',
                                                    'bg-blue-100 text-blue-800' => $task->status === 'in_progress',
                                                    'bg-gray-100 text-gray-800' => ! in_array($task->status, ['completed', 'in_progress']),
                                                ])>
                                                    {{ str($task->status)->headline() }}
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if ($tasks->hasPages())
                            <div class="px-4 py-3 sm:px-6 border-t border-gray-200">{{ $tasks->links() }}</div>
                        @endif
                    @endif
                </div>

            @endif
        </div>
    </div>
</x-app-layout>