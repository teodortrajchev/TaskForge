<nav x-data="{ open: false }" class="tf-nav sticky top-0 z-40 order-first">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-6">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="tf-logo">
                        <x-application-logo class="block h-9 w-auto fill-current" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <a href="{{ route('dashboard') }}"
                       class="tf-link {{ request()->routeIs('dashboard') ? 'tf-link-active' : '' }}"
                       @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                        {{ __('Dashboard') }}
                    </a>
                    <a href="{{ route('projects.index') }}"
                       class="tf-link {{ request()->routeIs('projects.*') ? 'tf-link-active' : '' }}"
                       @if (request()->routeIs('projects.*')) aria-current="page" @endif>
                        {{ __('Projects') }}
                    </a>
                </div>
            </div>

            <div class="flex items-center">
                <form method="GET" action="{{ route('search') }}" role="search" class="hidden sm:block me-4">
                    <label for="nav-search" class="sr-only">{{ __('Search') }}</label>
                    <div class="relative">
                        <svg class="tf-search-icon pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                        <input id="nav-search" type="search" name="q" value="{{ request('q') }}" maxlength="100"
                               placeholder="Search projects & tasks..."
                               class="tf-search w-56 lg:w-72 ps-9 py-1.5 text-sm rounded-md">
                    </div>
                </form>

                <div class="tf-bell">
                    <x-notification-bell />
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48" contentClasses="tf-dropdown py-1">
                        <x-slot name="trigger">
                            <button class="tf-user-btn inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="tf-burger inline-flex items-center justify-center p-2 rounded-md focus:outline-none transition duration-150 ease-in-out" aria-label="{{ __('Menu') }}">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="tf-mobile hidden sm:hidden">
        <form method="GET" action="{{ route('search') }}" role="search" class="px-4 pt-3">
            <label for="nav-search-mobile" class="sr-only">{{ __('Search') }}</label>
            <input id="nav-search-mobile" type="search" name="q" value="{{ request('q') }}" maxlength="100"
                   placeholder="Search projects & tasks..."
                   class="tf-search w-full text-sm rounded-md">
        </form>

        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="tf-mobile-link {{ request()->routeIs('dashboard') ? 'tf-mobile-link-active' : '' }}"
               @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                {{ __('Dashboard') }}
            </a>
            <a href="{{ route('projects.index') }}"
               class="tf-mobile-link {{ request()->routeIs('projects.*') ? 'tf-mobile-link-active' : '' }}"
               @if (request()->routeIs('projects.*')) aria-current="page" @endif>
                {{ __('Projects') }}
            </a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="tf-mobile-account pt-4 pb-1">
            <div class="px-4">
                <div class="tf-mobile-name font-medium text-base">{{ Auth::user()->name }}</div>
                <div class="tf-mobile-email font-medium text-sm">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="tf-mobile-link">
                    {{ __('Profile') }}
                </a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <a href="{{ route('logout') }}" class="tf-mobile-link"
                       onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</nav>