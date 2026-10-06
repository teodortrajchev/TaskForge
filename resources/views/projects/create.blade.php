<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('projects.store') }}" class="p-6 space-y-6">
                    @csrf
                    <div x-data="githubImport({
        endpoint: @js(route('github.repository')),
        url: @js(old('github_url', '')),
     })">
    <x-input-label for="github_url" :value="__('GitHub repository (optional)')" />

    <div class="mt-1 flex gap-2">
        <x-text-input id="github_url" name="github_url" type="text" class="block w-full"
            placeholder="https://github.com/owner/repo"
            x-model="url"
            x-on:keydown.enter.prevent="importRepo()" />

        <button type="button" @click="importRepo()"
                :disabled="loading || !url.trim()"
                class="shrink-0 px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50">
            <span x-text="loading ? '{{ __('Importing...') }}' : '{{ __('Import') }}'"></span>
        </button>
    </div>

    <p x-show="message" x-cloak x-text="message"
       :class="ok ? 'text-green-600' : 'text-red-600'" class="mt-2 text-sm"></p>
    <x-input-error :messages="$errors->get('github_url')" class="mt-2" />
</div>
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="due_date" :value="__('Due Date')" />
                        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                            :value="old('due_date')" />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>{{ __('Create Project') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('githubImport', (cfg) => ({
            url: cfg.url || '',
            loading: false,
            message: '',
            ok: false,

            async importRepo() {
                if (!this.url.trim()) return;
                this.loading = true;
                this.message = '';

                try {
                    const { data } = await window.axios.get(cfg.endpoint, { params: { url: this.url.trim() } });
                    document.getElementById('name').value = data.name;
                    document.getElementById('description').value = data.description || '';
                    this.url = data.url; // canonical https://github.com/owner/repo
                    this.ok = true;
                    this.message = 'Imported name and description from GitHub.';
                } catch (e) {
                    this.ok = false;
                    this.message = e.response?.data?.message || 'Could not reach GitHub.';
                } finally {
                    this.loading = false;
                }
            },
        }));
    });
</script>
</x-app-layout>