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
        /** @var array<string, mixed> $seo */
        $seo = isset($seo) && is_array($seo) ? $seo : [];

        $defaultTitle = 'DOPA Check';
        $defaultDescription = 'DOPA Check é o Strava dos hábitos e da mente. Um sistema de check-ins que transforma disciplina em algo visual, social e viciante - no bom sentido.';
        // WhatsApp costuma falhar com WebP. Preferimos PNG.
        $defaultImage = $appUrl.'/images/og.png';

        $ogTitle = (string) ($seo['title'] ?? $defaultTitle);
        $ogDescription = (string) ($seo['description'] ?? $defaultDescription);
        $ogImage = (string) ($seo['image'] ?? $defaultImage);
        $ogType = (string) ($seo['type'] ?? 'website');
        // Se o controller não informar dimensões/tipo, tentamos inferir a partir do $ogImage.
        // Observação: para URLs remotas, evitamos fetch aqui (custo/latência). Nesses casos, usa defaults.
        $ogImageType = $seo['image_type'] ?? null;
        $ogImageWidth = $seo['image_width'] ?? null;
        $ogImageHeight = $seo['image_height'] ?? null;

        $ogImagePath = (string) (parse_url($ogImage, PHP_URL_PATH) ?? '');

        // Nossos OG endpoints já são padronizados em 1200x630 JPEG.
        if (\Illuminate\Support\Str::startsWith($ogImagePath, '/og/')) {
            $ogImageType = $ogImageType ?: 'image/jpeg';
            $ogImageWidth = $ogImageWidth ?: '1200';
            $ogImageHeight = $ogImageHeight ?: '630';
        } else {
            // Tenta inferir apenas quando o arquivo existir localmente em /public.
            $localPath = null;
            if (\Illuminate\Support\Str::startsWith($ogImage, $appUrl)) {
                $localPath = public_path(ltrim((string) parse_url($ogImage, PHP_URL_PATH), '/'));
            } elseif (\Illuminate\Support\Str::startsWith($ogImagePath, ['/images/', '/storage/'])) {
                $localPath = public_path(ltrim($ogImagePath, '/'));
            }

            if ($localPath && is_file($localPath)) {
                $info = @getimagesize($localPath);
                if (is_array($info)) {
                    $ogImageWidth = $ogImageWidth ?: (string) ($info[0] ?? null);
                    $ogImageHeight = $ogImageHeight ?: (string) ($info[1] ?? null);
                    $ogImageType = $ogImageType ?: (string) ($info['mime'] ?? null);
                }
            }

            // Fallback do mime por extensão (quando não deu pra ler do disco).
            if (! $ogImageType) {
                $ext = strtolower(pathinfo($ogImagePath, PATHINFO_EXTENSION));
                $ogImageType = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    'gif' => 'image/gif',
                    default => 'image/png',
                };
            }

            $ogImageWidth = (string) ($ogImageWidth ?: '1200');
            $ogImageHeight = (string) ($ogImageHeight ?: '630');
        }

        $ogImageType = (string) ($ogImageType ?: 'image/png');
        $ogImageAlt = (string) ($seo['image_alt'] ?? 'DOPA Check - Tracking de hábitos e desafios');

        // JSON-LD (evita usar "@context" inline no Blade, pois o Blade interpreta "@context" como diretiva)
        $jsonLdPayload = $seo['json_ld'] ?? [
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
        ];
        $jsonLd = json_encode($jsonLdPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp

    <!-- Primary Meta Tags -->
    <title>{{ $ogTitle }}</title>
    <meta name="title" content="{{ $ogTitle }}">
    <meta name="description" content="{{ $ogDescription }}">

    <!-- Open Graph / WhatsApp -->
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="og:site_name" content="{{ $defaultTitle }}">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:type" content="{{ $ogImageType }}">
    <meta property="og:image:width" content="{{ $ogImageWidth }}">
    <meta property="og:image:height" content="{{ $ogImageHeight }}">
    <meta property="og:image:alt" content="{{ $ogImageAlt }}">

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
