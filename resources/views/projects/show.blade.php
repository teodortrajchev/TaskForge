<x-app-layout>
@vite('resources/css/project-calendar.css')

<x-slot name="header">

    <div class="flex items-center justify-between">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->name }}
        </h2>

        <div class="flex items-center gap-4">
            <a href="{{ route('projects.history', $project) }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Task history') }}
            </a>
            <a
                href="{{ route('projects.index') }}"
                class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('Back to Projects') }}
            </a>

            @if ($project->roleFor(auth()->user())?->canManage())

                <form
                    method="POST"
                    action="{{ route('projects.status.update', $project) }}"
                >
                    @csrf
                    @method('PATCH')

                    @if ($project->status === 'completed')

                        <input type="hidden" name="status" value="active">

                        <button
                            type="submit"
                            class="text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            {{ __('Reopen project') }}
                        </button>

                    @else

                        <input type="hidden" name="status" value="completed">

                        <button
                            type="submit"
                            class="text-sm text-green-600 hover:text-green-800"
                        >
                            {{ __('Mark as finished') }}
                        </button>

                    @endif

                </form>

            @endif

            @if ($project->roleFor(auth()->user())?->canDeleteProject())

                <x-confirm-delete
                    :action="route('projects.destroy', $project)"
                    title="Delete this project?"
                    :message="'“' . $project->name . '” and all of its tasks, messages and members will be permanently deleted. This cannot be undone.'"
                    confirm="Delete project"
                >
                    {{ __('Delete project') }}
                </x-confirm-delete>

            @endif

        </div>

    </div>

</x-slot>

<div class="py-12">

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))

            <div class="px-4 py-3 rounded-md bg-green-50 border border-green-200 text-sm text-green-700">
                {{ session('status') }}
            </div>

        @endif

        {{-- Project information --}}

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

            <div class="p-6 text-gray-900">

                <div class="flex items-center gap-3">

                    <span @class([
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                        'bg-green-100 text-green-800' => $project->status === 'active',
                        'bg-gray-100 text-gray-800' => $project->status !== 'active',
                    ])>
                        {{ $project->status }}
                    </span>

                    @if ($project->due_date)

                        <span class="text-sm text-gray-500">
                            {{ __('Due') }}
                            {{ $project->due_date->format('M d, Y') }}
                        </span>

                    @endif

                </div>

                @if ($project->description)

                    <p class="mt-4 text-sm text-gray-700 whitespace-pre-line">
                        {{ $project->description }}
                    </p>

                @else

                    <p class="mt-4 text-sm text-gray-400 italic">
                        {{ __('No description provided.') }}
                    </p>

                @endif
                @if ($project->github_url)
                    <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer"
                    class="mt-3 inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
                        {{ __('View on GitHub') }} &rarr;
                    </a>
                @endif
            </div>

        </div>

        {{-- Weekly calendar --}}

        @php

            $canContribute = $project->roleFor(auth()->user())?->canContribute();

            $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY);

            $weekEnd = $weekStart->copy()->addDays(6);

            $days = [];

            for ($i = 0; $i < 7; $i++) {
                $days[] = $weekStart->copy()->addDays($i);
            }

            $scheduledTasks = $project->tasks->filter(function ($task) {
                return $task->due_date !== null;
            });

            $unscheduledTasks = $project->tasks->filter(function ($task) {
                return $task->due_date === null;
            });

        @endphp

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

            <div class="p-6 text-gray-900">

                {{-- Calendar header --}}

                <div class="flex items-center justify-between mb-6">

                    <div>

                        <h2 class="text-lg font-semibold">
                            Tasks
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            {{ $weekStart->format('M j') }}
                            -
                            {{ $weekEnd->format('M j, Y') }}
                        </p>

                    </div>

                    <a
                        href="{{ route('projects.tasks.create', $project) }}"
                        class="text-sm text-blue-600 hover:text-blue-800"
                    >
                        + New task
                    </a>

                </div>

                {{-- Calendar --}}

                <div class="calendar-wrapper">

                    <div class="calendar-grid">

                        @foreach ($days as $day)

                            @php

                                $dayTasks = $scheduledTasks->filter(function ($task) use ($day) {
                                    return $task->due_date->isSameDay($day);
                                });

                                $isToday = $day->isToday();

                            @endphp

                            <div
                                class="calendar-day {{ $isToday ? 'calendar-day-today' : '' }}"
                                data-date="{{ $day->format('Y-m-d') }}"
                            >

                                {{-- Day header --}}

                                <div class="calendar-day-header">

                                    <div>

                                        <p class="calendar-day-name">
                                            {{ $day->format('l') }}
                                        </p>

                                        <p class="calendar-day-date">
                                            {{ $day->format('M j') }}
                                        </p>

                                    </div>

                                    @if ($isToday)

                                        <span class="today-badge">
                                            Today
                                        </span>

                                    @endif

                                </div>

                                {{-- Tasks --}}

                                <div class="calendar-tasks">

                                    @forelse ($dayTasks as $task)

                                        <div
                                            class="calendar-task"
                                            draggable="{{ $canContribute ? 'true' : 'false' }}"
                                            data-task-id="{{ $task->id }}"
                                        >

                                            <div class="calendar-task-header">

                                                <a
                                                    href="{{ route('projects.tasks.show', [$project, $task]) }}"
                                                    class="calendar-task-title"
                                                >
                                                    {{ $task->name }}
                                                </a>

                                                @if ($canContribute)

                                                    <a
                                                        href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                                                        class="calendar-task-edit"
                                                    >
                                                        Edit
                                                    </a>

                                                @endif

                                            </div>

                                            @if ($task->description)

                                                <p class="calendar-task-description">
                                                    {{ $task->description }}
                                                </p>

                                            @endif

                                            <div class="calendar-task-meta">

                                                <span @class([
                                                    'calendar-priority',
                                                    'priority-high' => $task->priority === 'high',
                                                    'priority-medium' => $task->priority === 'medium',
                                                    'priority-low' => $task->priority === 'low',
                                                ])>
                                                    {{ str($task->priority ?? 'medium')->headline() }}
                                                </span>

                                                <span @class([
                                                    'calendar-status',
                                                    'status-todo' => $task->status === 'todo',
                                                    'status-in-progress' => $task->status === 'in_progress',
                                                    'status-completed' => $task->status === 'completed',
                                                    'status-pending' => $task->status === 'pending',
                                                ])>
                                                    {{ str($task->status)->headline() }}
                                                </span>

                                            </div>

                                            @if ($task->assignees->count())

                                                <div class="calendar-task-assignees">

                                                    <span class="font-medium">
                                                        Assigned:
                                                    </span>

                                                    {{ $task->assignees->pluck('name')->join(', ') }}

                                                </div>

                                            @endif

                                        </div>

                                    @empty

                                        <div class="calendar-empty">
                                            No tasks
                                        </div>

                                    @endforelse

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

                {{-- Unscheduled tasks --}}

                @if ($unscheduledTasks->count())

                    <div class="mt-6 border-t border-gray-200 pt-6">

                        <div class="flex items-center justify-between mb-4">

                            <div>

                                <h3 class="text-sm font-semibold text-gray-900">
                                    Unscheduled tasks
                                </h3>

                                <p class="text-xs text-gray-500 mt-1">
                                    These tasks do not have a due date.
                                </p>

                            </div>

                            <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                {{ $unscheduledTasks->count() }}
                            </span>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                            @foreach ($unscheduledTasks as $task)

                                <div
                                    class="calendar-task"
                                    draggable="{{ $canContribute ? 'true' : 'false' }}"
                                    data-task-id="{{ $task->id }}"
                                >

                                    <div class="calendar-task-header">

                                        <a
                                            href="{{ route('projects.tasks.show', [$project, $task]) }}"
                                            class="calendar-task-title"
                                        >
                                            {{ $task->name }}
                                        </a>

                                        @if ($canContribute)

                                            <a
                                                href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                                                class="calendar-task-edit"
                                            >
                                                Edit
                                            </a>

                                        @endif

                                    </div>

                                    @if ($task->description)

                                        <p class="calendar-task-description">
                                            {{ $task->description }}
                                        </p>

                                    @endif

                                    <div class="calendar-task-meta">

                                        <span @class([
                                            'calendar-priority',
                                            'priority-high' => $task->priority === 'high',
                                            'priority-medium' => $task->priority === 'medium',
                                            'priority-low' => $task->priority === 'low',
                                        ])>
                                            {{ str($task->priority ?? 'medium')->headline() }}
                                        </span>

                                        <span @class([
                                            'calendar-status',
                                            'status-todo' => $task->status === 'todo',
                                            'status-in-progress' => $task->status === 'in_progress',
                                            'status-completed' => $task->status === 'completed',
                                            'status-pending' => $task->status === 'pending',
                                        ])>
                                            {{ str($task->status)->headline() }}
                                        </span>

                                    </div>

                                    @if ($task->assignees->count())

                                        <div class="calendar-task-assignees">

                                            <span class="font-medium">
                                                Assigned:
                                            </span>

                                            {{ $task->assignees->pluck('name')->join(', ') }}

                                        </div>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    </div>

                @endif

            </div>

        </div>
        {{-- GitHub commits --}}
        @include('github.commits', ['commits' => $commits])
        {{-- Messages --}}

        @include('messages._board', ['project' => $project, 'task' => null])

        @php

            $actorRole = $project->roleFor(auth()->user());

            $canManage = $actorRole?->canManage() ?? false;

            $isOwner = $actorRole === \App\Enums\ProjectRole::Owner;

            $canChangeRoles = $actorRole?->canChangeRoles() ?? false;

            $roleOptions = [
                'owner' => 'Owner',
                'manager' => 'Manager',
                'member' => 'Member',
                'viewer' => 'Viewer'
            ];

            $invitations = $invitations ?? collect();

            $inviteHasErrors = $errors->has('email') || $errors->has('role');

        @endphp

        {{-- Members --}}

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

            <div
                class="p-6"
                x-data="{ open: {{ $inviteHasErrors ? 'true' : 'false' }} }"
            >

                <div class="flex items-center justify-between mb-4">

                    <h3 class="text-sm font-medium text-gray-900">
                        {{ __('Members') }}
                    </h3>

                    @if ($canManage)

                        <button
                            type="button"
                            @click="open = !open"
                            class="inline-flex items-center px-3 py-1.5 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150"
                        >

                            <span x-show="!open">
                                + {{ __('Invite collaborator') }}
                            </span>

                            <span x-show="open" x-cloak>
                                {{ __('Cancel') }}
                            </span>

                        </button>

                    @endif

                </div>

                @if ($canManage)

                    <form
                        x-show="open"
                        x-cloak
                        x-transition
                        method="POST"
                        action="{{ route('projects.invitations.store', $project) }}"
                        class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200"
                    >

                        @csrf

                        <div class="flex flex-col sm:flex-row gap-3 sm:items-end">

                            <div class="flex-1">

                                <label
                                    for="invite-email"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    {{ __('Email address') }}
                                </label>

                                <input
                                    type="email"
                                    id="invite-email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="name@example.com"
                                    required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('email')

                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                            <div>

                                <label
                                    for="invite-role"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    {{ __('Role') }}
                                </label>

                                <select
                                    id="invite-role"
                                    name="role"
                                    class="mt-1 block border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                    @foreach ($roleOptions as $value => $label)

                                        @if ($value !== 'owner' || $isOwner)

                                            <option
                                                value="{{ $value }}"
                                                @selected(old('role', 'member') === $value)
                                            >
                                                {{ $label }}
                                            </option>

                                        @endif

                                    @endforeach

                                </select>

                                @error('role')

                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                            <x-primary-button>
                                {{ __('Send invite') }}
                            </x-primary-button>

                        </div>

                        <p class="mt-3 text-xs text-gray-500">
                            {{ __('They will get an email with a link that expires in 7 days.') }}
                        </p>

                    </form>

                @endif

                @if ($errors->roleUpdate->any())

                    <div class="mb-4 px-4 py-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-700">
                        {{ $errors->roleUpdate->first() }}
                    </div>

                @endif

                <ul role="list" class="divide-y divide-gray-200">

                    @foreach ($project->members as $member)

                        <li class="py-3 flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-gray-900">
                                    {{ $member->name }}
                                </p>

                                <p class="text-sm text-gray-500">
                                    {{ $member->email }}
                                </p>

                            </div>

                            @if ($canChangeRoles && $member->id !== auth()->id())

                                <form
                                    method="POST"
                                    action="{{ route('projects.members.update', [$project, $member]) }}"
                                >

                                    @csrf
                                    @method('PUT')

                                    <label
                                        for="role-{{ $member->id }}"
                                        class="sr-only"
                                    >
                                        {{ __('Role for :name', ['name' => $member->name]) }}
                                    </label>

                                    <select
                                        id="role-{{ $member->id }}"
                                        name="role"
                                        onchange="this.form.submit()"
                                        class="border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >

                                        @foreach ($roleOptions as $value => $label)

                                            <option
                                                value="{{ $value }}"
                                                @selected($member->pivot->role === $value)
                                            >
                                                {{ $label }}
                                            </option>

                                        @endforeach

                                    </select>

                                    <noscript>

                                        <button
                                            type="submit"
                                            class="text-sm text-indigo-600"
                                        >
                                            {{ __('Save') }}
                                        </button>

                                    </noscript>

                                </form>

                            @else

                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize">
                                    {{ $member->pivot->role }}
                                </span>

                            @endif

                        </li>

                    @endforeach

                </ul>

                @if ($canManage && $invitations->isNotEmpty())

                    <h4 class="text-sm font-medium text-gray-900 mt-8 mb-2">
                        {{ __('Pending invitations') }}
                    </h4>

                    <ul role="list" class="divide-y divide-gray-200">

                        @foreach ($invitations as $invitation)

                            <li class="py-3 flex items-center justify-between">

                                <div>

                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $invitation->email }}
                                    </p>

                                    <p class="text-sm text-gray-500">

                                        {{ __('Expires') }}

                                        {{ $invitation->expires_at->format('M j, Y') }}

                                    </p>

                                </div>

                                <div class="flex items-center gap-4">

                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 capitalize">
                                        {{ $invitation->role }}
                                    </span>

                                    <form
                                        method="POST"
                                        action="{{ route('projects.invitations.destroy', [$project, $invitation]) }}"
                                        onsubmit="return confirm('Revoke this invitation?')"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="text-sm text-red-600 hover:text-red-800"
                                        >
                                            {{ __('Revoke') }}
                                        </button>

                                    </form>

                                </div>

                            </li>

                        @endforeach

                    </ul>

                @endif

            </div>

        </div>

    </div>

</div>

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const calendar = document.querySelector('.calendar-grid');

        if (!calendar) {
            return;
        }

        

        let draggedTask = null;
        let originalParent = null;

        const tasks = document.querySelectorAll(
            '.calendar-task[draggable="true"]'
        );

        const days = calendar.querySelectorAll('.calendar-day');

        tasks.forEach(task => {

            task.addEventListener('dragstart', function (event) {

                draggedTask = task;
                originalParent = task.parentElement;

                task.classList.add('calendar-task-dragging');

                event.dataTransfer.effectAllowed = 'move';

                event.dataTransfer.setData(
                    'text/plain',
                    task.dataset.taskId
                );

            });

            task.addEventListener('dragend', function () {

                task.classList.remove('calendar-task-dragging');

                days.forEach(day => {
                    day.classList.remove('calendar-day-drag-over');
                });

                draggedTask = null;
                originalParent = null;

            });

        });

        days.forEach(day => {

            day.addEventListener('dragover', function (event) {

                event.preventDefault();

                if (!draggedTask) {
                    return;
                }

                day.classList.add('calendar-day-drag-over');

                event.dataTransfer.dropEffect = 'move';

            });

            day.addEventListener('dragleave', function (event) {

                if (!day.contains(event.relatedTarget)) {
                    day.classList.remove('calendar-day-drag-over');
                }

            });

            day.addEventListener('drop', async function (event) {

                event.preventDefault();

                day.classList.remove('calendar-day-drag-over');

                if (!draggedTask) {
                    return;
                }

                const taskId = draggedTask.dataset.taskId;
                const newDate = day.dataset.date;
                const previousParent = originalParent;

                const newTasksContainer =
                    day.querySelector('.calendar-tasks');

                const emptyMessage =
                    newTasksContainer.querySelector('.calendar-empty');

                if (emptyMessage) {
                    emptyMessage.remove();
                }

                newTasksContainer.appendChild(draggedTask);

                const url =
                    "{{ route('projects.tasks.due-date', [$project, '__TASK_ID__']) }}"
                    .replace('__TASK_ID__', taskId);

                try {

                    const response = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                        },
                        body: JSON.stringify({
                            due_date: newDate
                        }),
                    });

                    if (!response.ok) {
                        throw new Error(
                            'Failed to update task due date.'
                        );
                    }

                    updateEmptyDays();

                } catch (error) {

                    console.error(error);

                    previousParent.appendChild(draggedTask);

                    updateEmptyDays();

                    alert(
                        'The task due date could not be updated.'
                    );

                }

            });

        });

        function updateEmptyDays() {

            days.forEach(day => {

                const tasksContainer =
                    day.querySelector('.calendar-tasks');

                const taskCount =
                    tasksContainer.querySelectorAll('.calendar-task').length;

                const empty =
                    tasksContainer.querySelector('.calendar-empty');

                if (taskCount === 0 && !empty) {
                    addEmptyMessage(day);
                }

                if (taskCount > 0 && empty) {
                    empty.remove();
                }

            });

        }

        function addEmptyMessage(day) {

            const tasksContainer =
                day.querySelector('.calendar-tasks');

            if (tasksContainer.querySelector('.calendar-empty')) {
                return;
            }

            const empty =
                document.createElement('div');

            empty.className = 'calendar-empty';
            empty.textContent = 'No tasks';

            tasksContainer.appendChild(empty);

        }

        function getCsrfToken() {

            const meta =
                document.querySelector(
                    'meta[name="csrf-token"]'
                );

            if (meta) {
                return meta.getAttribute('content');
            }

            const input =
                document.querySelector(
                    'input[name="_token"]'
                );

            if (input) {
                return input.value;
            }

            return '';

        }

    });

</script>

</x-app-layout>
