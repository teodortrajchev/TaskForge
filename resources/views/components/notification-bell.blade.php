<div class="relative me-1 sm:me-0"
     x-data="notificationBell({
         indexUrl: @js(route('notifications.index')),
         readAllUrl: @js(route('notifications.read-all')),
     })"
     @click.outside="open = false"
     @keydown.escape.window="open = false">

    <button type="button"
            @click="toggle()"
            class="relative p-2 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition ease-in-out duration-150"
            aria-label="{{ __('Notifications') }}"
            :aria-expanded="open.toString()">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        <span x-show="unread > 0" x-cloak
              x-text="unread > 99 ? '99+' : unread"
              class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold leading-none flex items-center justify-center"></span>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-1.5rem)] origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">

        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
            <h3 class="text-sm font-medium text-gray-900">{{ __('Notifications') }}</h3>
            <button type="button" x-show="unread > 0" x-cloak @click="markAllRead()"
                    class="text-xs text-indigo-600 hover:underline">
                {{ __('Mark all as read') }}
            </button>
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
            <p x-show="!loading && items.length === 0" x-cloak
               class="px-4 py-6 text-center text-sm text-gray-400">
                {{ __("You're all caught up.") }}
            </p>

            <template x-for="n in items" :key="n.id">
                <a :href="n.url"
                   class="flex gap-3 px-4 py-3 hover:bg-gray-50 transition"
                   :class="n.read ? '' : 'bg-indigo-50/60'">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                          :class="n.read ? 'bg-transparent' : 'bg-indigo-500'"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm text-gray-900"
                              :class="n.read ? '' : 'font-medium'" x-text="n.title"></span>
                        <span class="block text-xs text-gray-500 line-clamp-2 break-words" x-text="n.body"></span>
                        <span class="block mt-0.5 text-[11px] text-gray-400" x-text="timeAgo(n.created_at)"></span>
                    </span>
                </a>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationBell', (cfg) => ({
            open: false,
            loading: true,
            unread: 0,
            items: [],

            init() {
                this.load();
                setInterval(() => { if (!document.hidden) this.load(); }, 15000);
                document.addEventListener('visibilitychange', () => { if (!document.hidden) this.load(); });
                window.addEventListener('notifications-refresh', () => this.load());
            },

            async load() {
                try {
                    const { data } = await window.axios.get(cfg.indexUrl);
                    this.unread = data.unread_count;
                    this.items = data.notifications;
                } catch (e) {
                    // silent; next poll retries
                } finally {
                    this.loading = false;
                }
            },

            toggle() {
                this.open = !this.open;
                if (this.open) this.load();
            },

            async markAllRead() {
                try {
                    await window.axios.post(cfg.readAllUrl);
                    this.unread = 0;
                    this.items = this.items.map(n => ({ ...n, read: true }));
                } catch (e) {
                    // silent
                }
            },

            timeAgo(iso) {
                const secs = Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 1000));
                if (secs < 60) return 'just now';
                const mins = Math.floor(secs / 60);
                if (mins < 60) return mins + 'm ago';
                const hrs = Math.floor(mins / 60);
                if (hrs < 24) return hrs + 'h ago';
                const days = Math.floor(hrs / 24);
                if (days < 7) return days + 'd ago';
                return new Date(iso).toLocaleDateString([], { month: 'short', day: 'numeric' });
            },
        }));
    });
</script>