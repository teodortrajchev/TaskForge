@props(['title' => null, 'subtitle' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="tf-auth">
            <a href="/" class="tf-auth-logo">
                <x-application-logo class="w-16 h-16 fill-current" />
            </a>

            <div class="tf-auth-card">
                @if ($title)
                    <div class="tf-auth-head">
                        <h1 class="tf-auth-title">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="tf-auth-sub">{{ $subtitle }}</p>
                        @endif
                    </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </body>
</html>