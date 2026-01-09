<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="canonical" href="{{ url()->current() }}">

    @php
        $appUrl = rtrim((string) config('app.url', 'https://dopacheck.com.br'), '/');
        $currentUrl = url()->current();
        $ogTitle = 'DOPA Check';
        $ogDescription = 'DOPA Check é uma plataforma de tracking de hábitos e desafios. Faça check-ins (com ou sem foto) e acompanhe seu progresso em um dashboard simples e mobile-first.';
        // WhatsApp costuma falhar com WebP. Preferimos PNG.
        $ogImage = $appUrl.'/images/og.png';

        // JSON-LD (evita usar "@context" inline no Blade, pois o Blade interpreta "@context" como diretiva)
        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $ogTitle,
            'url' => $appUrl.'/',
            'image' => $ogImage,
            'description' => $ogDescription,
            'applicationCategory' => 'LifestyleApplication',
            'operatingSystem' => 'All',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'BRL',
                'category' => 'Freemium',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp

    <!-- Primary Meta Tags -->
    <title>{{ $ogTitle }}</title>
    <meta name="title" content="{{ $ogTitle }}">
    <meta name="description" content="{{ $ogDescription }}">

    <!-- Open Graph / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="og:site_name" content="{{ $ogTitle }}">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DOPA Check - Tracking de hábitos e desafios">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- Favicon & App Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    @if (app()->environment('production'))
        <link rel="manifest" href="/site.webmanifest">
    @endif

    <!-- Structured Data (JSON-LD / Schema.org) -->
    <script type="application/ld+json">{!! $jsonLd !!}</script>

    <!-- PWA Meta Tags -->
    @if (app()->environment('production'))
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="DOPA Check">
    @endif

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead

    <!-- Google Analytics (GA4) via gtag.js -->
    @if (filled(env('VITE_GA_MEASUREMENT_ID')))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('VITE_GA_MEASUREMENT_ID') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            // SPA (Inertia): o page_view será disparado no frontend em cada navegação
            gtag('config', '{{ env('VITE_GA_MEASUREMENT_ID') }}', { send_page_view: false });
        </script>
    @endif

    <!-- Service Worker Registration -->
    @if (!app()->environment('production'))
        <script>
            // Em dev (qualquer ambiente != production), o Service Worker costuma atrapalhar (cache + reloads).
            // Garantimos que ele fique desativado e limpamos caches.
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations()
                    .then((registrations) => Promise.all(registrations.map((r) => r.unregister())))
                    .catch(() => {});
            }
            if ('caches' in window) {
                caches.keys()
                    .then((keys) => Promise.all(keys.map((k) => caches.delete(k))))
                    .catch(() => {});
            }
        </script>
    @else
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((registration) => {
                            console.log('Service Worker registrado com sucesso:', registration.scope);
                        })
                        .catch((error) => {
                            console.log('Falha ao registrar Service Worker:', error);
                        });
                });
            }
        </script>
    @endif
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
