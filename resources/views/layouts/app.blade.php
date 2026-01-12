<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('head')
    <title>@yield('title', 'Simulasi TKA')</title>

    @include('layouts.styles')
    @stack('styles')
</head>
<body class="@yield('bodyClass')">
    <div class="@yield('wrapperClass', 'dashboard-container')">
        @include('layouts.sidebar')

        <main class="main-content">
            @include('layouts.header', [
                'pageTitle' => $pageTitle ?? null,
                'breadcrumb' => $breadcrumb ?? null,
                'showSearch' => $showSearch ?? false,
                'showAvatar' => $showAvatar ?? false,
                'avatarInitials' => $avatarInitials ?? null,
            ])

            @yield('content')
        </main>
    </div>

    @include('layouts.scripts')
    @stack('scripts')
</body>
</html>
