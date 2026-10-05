<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }} &mdash; {{ __('Task history') }}
            </h2>

            <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Back to') }} {{ $project->name }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase text-gray-500 border-b border-gray-200">
                                    <th class="py-2 pe-4">{{ __('Task') }}</th>
                                    <th class="py-2 pe-4">{{ __('Status') }}</th>
                                    <th class="py-2 pe-4">{{ __('Priority') }}</th>
                                    <th class="py-2 pe-4">{{ __('Due date') }}</th>
                                    <th class="py-2 pe-4">{{ __('Assigned') }}</th>
                                    <th class="py-2">{{ __('Created') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($tasks as $task)
                                    <tr>
                                        <td class="py-3 pe-4">
                                            <a href="{{ route('projects.tasks.show', [$project, $task]) }}"
                                               class="font-medium text-indigo-600 hover:underline">
                                                {{ $task->name }}
                                            </a>
                                        </td>
                                        <td class="py-3 pe-4">{{ str($task->status)->headline() }}</td>
                                        <td class="py-3 pe-4">{{ str($task->priority ?? 'medium')->headline() }}</td>
                                        <td class="py-3 pe-4">{{ $task->due_date?->format('M j, Y') ?? '—' }}</td>
                                        <td class="py-3 pe-4">{{ $task->assignees->pluck('name')->join(', ') ?: '—' }}</td>
                                        <td class="py-3 text-gray-500">{{ $task->created_at->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-gray-400">
                                            {{ __('This project has no tasks yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $tasks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>