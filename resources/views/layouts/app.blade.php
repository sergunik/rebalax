<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta
        name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" href="favicon.ico">
    <link href="style.css" rel="stylesheet">
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
