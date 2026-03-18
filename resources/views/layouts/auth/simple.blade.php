<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased">
        <div class="min-h-svh bg-[radial-gradient(circle_at_top_left,_rgba(121,120,233,0.12),_transparent_28%),linear-gradient(180deg,_#f7f8ff_0%,_#eef2ff_100%)] px-4 py-8 md:px-8 md:py-12">
            <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-3xl items-center justify-center">
                <div class="w-full">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
