<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicon & App Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Structured Data (Example: JSON-LD Schema.org) -->
    @verbatim
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "DOPA Check",
        "url": "{{ rtrim(config('app.url', 'https://dopacheck.com.br'), '/') }}/",
        "image": "{{ rtrim(config('app.url', 'https://dopacheck.com.br'), '/') }}/images/og.webp",
        "description": "DOPA Check é uma plataforma de tracking de hábitos e desafios. Faça check-ins (com ou sem foto) e acompanhe seu progresso em um dashboard simples e mobile-first.",
        "applicationCategory": "LifestyleApplication",
        "operatingSystem": "All",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "BRL",
            "category": "Freemium"
        }
    }
    </script>
    @endverbatim

    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DOPA Check">

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
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
