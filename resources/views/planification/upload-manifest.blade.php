@php($isAuth = auth()->check())

@if($isAuth)
<x-layouts::app :title="__('Upload Manifest')">
    <div class="flex h-full w-full flex-1 items-start justify-center px-6 py-8">
        <div class="w-full max-w-3xl rounded-2xl border border-slate-200/70 bg-white shadow-[0_20px_48px_-32px_rgba(15,23,42,0.18)]">
            <div class="plani-manifest p-6">
                @include('planification._manifest-form')
            </div>
        </div>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .plani-card-outer {
            width: 100%;
            max-width: 860px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 24px 56px -32px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(148, 163, 184, 0.18);
            padding: 36px 36px 32px;
        }

        .plani-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 28px;
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
        <div class="plani-card-outer">
            <div class="plani-logo">
                <img src="{{ asset('img/image.png') }}" alt="Dakar Terminal">
            </div>
            <div class="plani-manifest">
                @include('planification._manifest-form')
            </div>
        </div>
    </div>
</body>
</html>
@endif
