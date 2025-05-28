<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <script src="{{ asset('bundle.js') }}" defer></script>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('components.header')

        <div class="flex">
            @include('components.sidebar')

            <main class="flex-1 p-6">
                @include('components.alert')
                @yield('content')
            </main>
        </div>

        @include('components.footer')
    </div>

    @include('partials.scripts')
</body>
</html>
