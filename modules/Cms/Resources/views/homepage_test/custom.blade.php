<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle ?? ('Preview — ' . ($item->title ?? 'Homepage Test')) }}</title>
    @if(! empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @endif
    @if(! empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    @if(! empty($robotsMeta))
        <meta name="robots" content="{{ $robotsMeta }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    @php
        $locale = app()->getLocale();
        $displayName = data_get($item->name, $locale)
            ?? data_get($item->name, 'en')
            ?? (is_string($item->name) ? $item->name : collect($item->name ?? [])->filter()->first())
            ?? 'Homepage';
    @endphp

    <div class="container py-5 min-vh-100 d-flex flex-column">
        <header class="mb-4">
            <span class="text-muted small">Önizleme</span>
        </header>

        <main class="flex-grow-1 d-flex align-items-center">
            <div class="row justify-content-center w-100">
                <div class="col-11 col-sm-9 col-md-7 col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5 text-center">
                            <h1 class="h2 fw-bold mb-3">{{ $displayName }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="text-center text-muted small pt-4 mt-auto">
            © {{ date('Y') }}
        </footer>
    </div>
</body>
</html>
