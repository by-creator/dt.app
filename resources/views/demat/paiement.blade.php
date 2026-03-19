<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>Paiement - Dakar Terminal</title>

    @include('partials.app-icons')

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300;400;600;700;800&display=swap" rel="stylesheet"/>

    <style>
        :root {
            color-scheme: light dark;
            --demat-page-bg: #f0f4f8;
            --demat-surface: rgba(255,255,255,.94);
            --demat-border: rgba(148,163,184,.22);
            --demat-text: #191c24;
            --demat-muted: #64748b;
            --demat-shadow: 0 4px 18px rgba(0,0,0,.08);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --demat-page-bg: #020617;
                --demat-surface: rgba(15,23,42,.88);
                --demat-border: rgba(148,163,184,.18);
                --demat-text: #e5eefb;
                --demat-muted: #94a3b8;
                --demat-shadow: 0 24px 56px rgba(2,6,23,.38);
            }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Overpass', sans-serif;
            background: var(--demat-page-bg);
            color: var(--demat-text);
            min-height: 100vh;
        }

        .page-hero {
            background: var(--demat-surface);
            border-bottom: 1px solid var(--demat-border);
            padding: 32px 24px 36px;
            text-align: center;
            margin-bottom: 48px;
            box-shadow: var(--demat-shadow);
        }

        .page-hero img {
            max-height: 44px;
            width: auto;
            margin-bottom: 20px;
        }

        .page-hero h1 {
            font-size: clamp(1.5rem, 3vw, 2.2rem);
            font-weight: 800;
            margin-bottom: 6px;
        }

        .page-hero p {
            color: var(--demat-muted);
            font-size: 15px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--demat-muted);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 20px;
            transition: color .2s;
        }

        .back-link:hover { color: #4B49AC; text-decoration: none; }

        .cards-wrapper {
            display: flex;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
            padding: 0 24px 60px;
            max-width: 900px;
            margin: 0 auto;
        }

        .pay-card {
            flex: 1;
            min-width: 260px;
            max-width: 380px;
            background: var(--demat-surface);
            border: 1px solid var(--demat-border);
            border-radius: 20px;
            box-shadow: var(--demat-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            text-decoration: none;
            transition: transform .25s ease, box-shadow .25s ease;
            cursor: pointer;
        }

        .pay-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 48px rgba(0,0,0,.15);
            text-decoration: none;
        }

        .pay-card img {
            max-width: 100%;
            max-height: 120px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        @media (max-width: 600px) {
            .cards-wrapper { flex-direction: column; align-items: center; }
            .pay-card { max-width: 100%; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="page-hero">
        <img src="{{ asset('img/image.png') }}" alt="Dakar Terminal">
        <h1>Paiement</h1>
        <p>Choisissez votre operateur de paiement</p>
    </div>

    <div style="max-width:900px;margin:0 auto;padding:0 24px 12px">
        <a href="{{ route('demat') }}" class="back-link">
            &#8592; Retour
        </a>
    </div>

    <div class="cards-wrapper">
        <a href="https://www.google.com/" class="pay-card">
            <img src="{{ asset('img/sycapay.png') }}" alt="Sycapay">
        </a>
        <a href="https://mytouchpoint.net/dakar_terminal" class="pay-card">
            <img src="{{ asset('img/intouch.png') }}" alt="Intouch">
        </a>
    </div>
</body>
</html>
