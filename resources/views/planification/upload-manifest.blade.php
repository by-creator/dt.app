@php($isAuth = auth()->check())

@if($isAuth)
<x-layouts::app :title="__('Upload Manifest')">
    <div class="plani-manifest flex h-full w-full flex-1 flex-col gap-6 pb-8">
        @include('planification._manifest-form')
    </div>
</x-layouts::app>
@else
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload Manifest - Dakar Terminal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">
    <style>
        :root {
            --dt-border: #e2e8f0;
            --dt-panel-bg: #ffffff;
            --dt-panel-alt-bg: #f8fafc;
            --dt-page-text: #0f172a;
            --dt-muted-text: #64748b;
            --dt-input-border: #cbd5e1;
            --dt-input-bg: #f8fafc;
            --dt-success-bg: #f0fdf4;
            --dt-success-text: #166534;
            --dt-success-border: #bbf7d0;
            --dt-danger-bg: #fef2f2;
            --dt-danger-text: #991b1b;
            --dt-danger-border: #fecaca;
            --dt-shadow: 0 18px 38px -28px rgba(15,23,42,.3);
            --dt-ring: rgba(75,73,172,.16);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(121,120,233,.12), transparent 28%),
                linear-gradient(180deg, #f7f8ff 0%, #eef2ff 100%);
            color: var(--dt-page-text);
            font-family: 'Instrument Sans', sans-serif;
        }

        .plani-shell {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        .plani-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .plani-logo img {
            height: 68px;
            width: auto;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="plani-shell">
        <div class="plani-logo">
            <img src="{{ asset('img/image.png') }}" alt="Dakar Terminal">
        </div>
        <div class="plani-manifest">
            @include('planification._manifest-form')
        </div>
    </div>
</body>
</html>
@endif
