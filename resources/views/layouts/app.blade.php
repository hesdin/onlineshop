<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'TP-PKK Marketplace - Marketplace B2B')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @stack('head')
</head>

<body class="bg-slate-50 font-sans text-slate-900">
    <div class="min-h-screen">
        @include('partials.header')

        <main class="container mx-auto space-y-12 px-4 py-10">
            @yield('content')
        </main>

        @include('partials.footer')
    </div>
    @stack('scripts')
</body>

</html>
