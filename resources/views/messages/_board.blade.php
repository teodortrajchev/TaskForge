@php
    $task = $task ?? null;
    $canPost = $project->roleFor(auth()->user())?->canContribute() ?? false;

    $indexUrl = $task
        ? route('projects.tasks.messages.index', [$project, $task])
        : route('projects.messages.index', $project);
    $storeUrl = $task
        ? route('projects.tasks.messages.store', [$project, $task])
        : route('projects.messages.store', $project);
    $deleteUrl = $task
        ? route('projects.tasks.messages.destroy', [$project, $task, '__ID__'])
        : route('projects.messages.destroy', [$project, '__ID__']);
@endphp

<div id="message-board" class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
     x-data="messageBoard({
         indexUrl: @js($indexUrl),
         storeUrl: @js($storeUrl),
         deleteUrl: @js($deleteUrl),
     })">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-900">
                {{ $task ? __('Task discussion') : __('Project discussion') }}
            </h3>
            <span class="text-xs text-gray-400">{{ __('Visible to all project members') }}</span>
        </div>

        <div x-ref="list"
             class="h-80 overflow-y-auto space-y-3 p-4 bg-gray-50 rounded-md border border-gray-200">
            <p x-show="loading" class="text-sm text-gray-400">{{ __('Loading messages...') }}</p>
            <p x-show="!loading && messages.length === 0" x-cloak class="text-sm text-gray-400 italic">
                {{ __('No messages yet. Start the conversation!') }}
            </p>

            <template x-for="m in messages" :key="m.id">
                <div class="flex" :class="m.mine ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[80%] rounded-lg px-3 py-2 text-sm shadow-sm"
                         :class="m.mine ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-900'">
                        <div class="flex items-center gap-2 text-xs mb-0.5"
                             :class="m.mine ? 'text-indigo-200' : 'text-gray-500'">
                            <span class="font-medium" x-text="m.mine ? 'You' : m.author"></span>
                            <span x-text="formatTime(m.created_at)"></span>
                            <button type="button" x-show="m.can_delete" x-cloak @click="remove(m)"
                                    class="ml-auto hover:underline"
                                    :class="m.mine ? 'text-indigo-100' : 'text-red-500'">
                                {{ __('Delete') }}
                            </button>
                        </div>
                        <p class="whitespace-pre-wrap break-words" x-text="m.body"></p>
                    </div>
                </div>
            </template>
        </div>

        <p x-show="error" x-cloak x-text="error" class="mt-2 text-sm text-red-600"></p>

        @if ($canPost)
            <div class="mt-3 flex items-end gap-2">
                <label for="message-body" class="sr-only">{{ __('Message') }}</label>
                <textarea id="message-body"
                          x-model="draft"
                          @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); send(); }"
                          rows="2"
                          maxlength="2000"
                          placeholder="{{ __('Write a message... (Enter to send, Shift+Enter for a new line)') }}"
                          class="flex-1 border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                <button type="button"
                        @click="send()"
                        :disabled="sending || !draft.trim()"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50 transition ease-in-out duration-150">
                    {{ __('Send') }}
                </button>
            </div>
        @else
            <p class="mt-3 text-xs text-gray-500 italic">{{ __('Viewers can read the board but not post.') }}</p>
        @endif
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('messageBoard', (cfg) => ({
            messages: [],
            draft: '',
            loading: true,
            sending: false,
            error: '',
            timer: null,

            init() {
                this.load(true);
                this.timer = setInterval(() => {
                    if (!document.hidden) this.load(false);
                }, 5000);
            },

            nearBottom() {
                const el = this.$refs.list;
                return el.scrollHeight - el.scrollTop - el.clientHeight < 80;
            },

            scrollToBottom() {
                this.$nextTick(() => { this.$refs.list.scrollTop = this.$refs.list.scrollHeight; });
            },

            async load(initial) {
                try {
                    const { data } = await window.axios.get(cfg.indexUrl);
                    const stick = initial || this.nearBottom();
                    const changed = JSON.stringify(data.messages) !== JSON.stringify(this.messages);
                    if (changed) {
                        this.messages = data.messages;
                        if (stick) this.scrollToBottom();
                    }
                    if (initial) window.dispatchEvent(new CustomEvent('notifications-refresh'));
                } catch (e) {
                    // silent; next poll retries
                } finally {
                    this.loading = false;
                }
            },

            async send() {
                const body = this.draft.trim();
                if (!body || this.sending) return;

                this.sending = true;
                this.error = '';
                try {
                    const { data } = await window.axios.post(cfg.storeUrl, { body });
                    this.messages.push(data);
                    this.draft = '';
                    this.scrollToBottom();
                } catch (e) {
                    this.error = e.response?.data?.errors?.body?.[0]
                        ?? e.response?.data?.message
                        ?? 'Could not send your message. Please try again.';
                } finally {
                    this.sending = false;
                }
            },

            async remove(message) {
                if (!confirm('Delete this message?')) return;
                try {
                    await window.axios.delete(cfg.deleteUrl.replace('__ID__', message.id));
                    this.messages = this.messages.filter(m => m.id !== message.id);
                } catch (e) {
                    this.error = 'Could not delete the message.';
                }
            },

            formatTime(iso) {
                const d = new Date(iso);
                const sameDay = d.toDateString() === new Date().toDateString();
                return sameDay
                    ? d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    : d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' +
                      d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },
        }));
    });
</script>