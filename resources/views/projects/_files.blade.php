@php
    $filesRole = $project->roleFor(auth()->user());
    $canUpload = $filesRole?->canContribute() ?? false;

    $fileErrors = collect($errors->get('file'));
@endphp

<div id="files" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-900">
                {{ __('Files') }}
                @if ($project->files->isNotEmpty())
                    <span class="ml-1 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                        {{ $project->files->count() }}
                    </span>
                @endif
            </h3>

            <span class="text-xs text-gray-400">{{ __('Visible to all project members') }}</span>
        </div>

        @if ($canUpload)
            <form method="POST"
                  action="{{ route('projects.files.store', $project) }}"
                  enctype="multipart/form-data"
                  class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200"
                  x-data="{ busy: false }"
                  @submit="busy = true">
                @csrf

                <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    <label for="project-file" class="sr-only">{{ __('Choose a file') }}</label>

                    <input type="file"
                           id="project-file"
                           name="file"
                           required
                           class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:uppercase file:tracking-widest file:bg-white file:text-gray-700 file:shadow-sm file:ring-1 file:ring-gray-300 hover:file:bg-gray-50">

                    <button type="submit"
                            :disabled="busy"
                            class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50 transition ease-in-out duration-150">
                        <span x-show="!busy">{{ __('Upload') }}</span>
                        <span x-show="busy" x-cloak>{{ __('Uploading...') }}</span>
                    </button>
                </div>

                <p class="mt-2 text-xs text-gray-500">
                    {{ __('One file at a time, up to :size MB.', ['size' => \App\Models\ProjectFile::MAX_KILOBYTES / 1024]) }}
                </p>

                @foreach ($fileErrors as $message)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @endforeach
            </form>
        @endif

        @if ($project->files->isEmpty())
            <p class="text-sm text-gray-400 italic">{{ __('No files uploaded yet.') }}</p>
        @else
            <ul role="list" class="divide-y divide-gray-200">
                @foreach ($project->files as $file)
                    <li class="py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('projects.files.download', [$project, $file]) }}"
                               class="block truncate text-sm font-medium text-indigo-600 hover:underline"
                               title="{{ $file->file_name }}">
                                {{ $file->file_name }}
                            </a>

                            <p class="text-xs text-gray-500">
                                {{ $file->file_size >= 1048576
                                    ? round($file->file_size / 1048576, 1) . ' MB'
                                    : max(1, round($file->file_size / 1024)) . ' KB' }}
                                &middot;
                                {{ $file->user?->name ?? __('Deleted user') }}
                                &middot;
                                {{ $file->created_at->format('M j, Y') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4 shrink-0">
                            <a href="{{ route('projects.files.download', [$project, $file]) }}"
                               class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Download') }}
                            </a>

                            @if ($file->deletableBy($filesRole, auth()->user()))
                                <x-confirm-delete
                                    :action="route('projects.files.destroy', [$project, $file])"
                                    title="Delete this file?"
                                    :message="'“' . $file->file_name . '” will be permanently deleted. This cannot be undone.'"
                                    confirm="Delete file"
                                >
                                    {{ __('Delete') }}
                                </x-confirm-delete>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

    </div>
</div>