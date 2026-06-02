<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Production App') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/responsive-erp.css') }}">
</head>

<body class="font-sans antialiased">
    <div class="erp-app-shell">

        @include('layouts.navigation')

        <div class="erp-main-wrapper">
            <div class="erp-top-mobile-bar">
                <button type="button" class="erp-mobile-menu-button" onclick="openMobileSidebar()">
                    ☰
                </button>

                <div>
                    <div class="erp-mobile-title">Production App</div>
                    <div class="erp-mobile-subtitle">Industrial Production ERP</div>
                </div>
            </div>

            @if (isset($header))
                <header class="erp-page-header">
                    <div class="erp-page-header-inner">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="erp-page-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="erp-mobile-overlay" class="erp-mobile-overlay" onclick="closeMobileSidebar()"></div>

    <script src="{{ asset('js/responsive-erp.js') }}"></script>
</body>
</html>