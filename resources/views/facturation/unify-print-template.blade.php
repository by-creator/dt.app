<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $type }}</title>
    <style>
        body{font-family:Arial,sans-serif;margin:30px;color:#111}
        h1{font-size:24px;margin-bottom:18px}
        .meta{margin-bottom:18px;color:#555}
        table{width:100%;border-collapse:collapse}
        td,th{border:1px solid #ddd;padding:10px;vertical-align:top;text-align:left}
        th{background:#f5f7ff;width:35%}
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $type }}</h1>
    <div class="meta">
        @if ($dateActiviteFormatted)
            Date : {{ $dateActiviteFormatted }}
        @endif
    </div>
    <table>
        <tbody>
            @foreach ($data as $key => $value)
                @continue(in_array($key, ['_token'], true))
                <tr>
                    <th>{{ $key }}</th>
                    <td>{{ $value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
