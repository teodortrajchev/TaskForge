<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'TaskForge') }}</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="tf-welcome">
        <main class="tf-welcome-panel">
            <h1>TaskForge</h1>
            <p>Plan projects, assign tasks, and track progress with your team.</p>

            <div class="tf-welcome-actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="tf-button tf-button-primary">Go to dashboard</a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="tf-button tf-button-primary">Log in</a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="tf-button tf-button-secondary">Create an account</a>
                    @endif
                @endauth
            </div>
        </main>
    </body>
</html>