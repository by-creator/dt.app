<x-layouts::auth :title="__('Connexion')">
    <style>
        .dt-auth-shell { width: min(100%, 38rem); margin: 0 auto; }
        .dt-auth-card { background: rgba(255,255,255,.96); border: 1px solid rgba(108,117,255,.12); border-radius: 1.75rem; box-shadow: 0 28px 80px rgba(54,63,136,.16); overflow: hidden; backdrop-filter: blur(14px); }
        .dt-auth-body { padding: 3rem 3rem 2.75rem; }
        .dt-auth-logo { display: inline-flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; margin-bottom: 1.5rem; border-radius: 1.25rem; background: linear-gradient(135deg, rgba(75,73,172,.12), rgba(121,120,233,.18)); }
        .dt-auth-logo img { max-width: 2.5rem; max-height: 2.5rem; }
        .dt-auth-title { margin: 0; font-size: clamp(1.9rem, 3vw, 2.4rem); line-height: 1.05; color: #1c2240; font-weight: 800; }
        .dt-auth-subtitle { margin: .75rem 0 2rem; color: #68708f; font-size: 1rem; }
        .dt-auth-alert { padding: .95rem 1rem; border-radius: .95rem; margin-bottom: 1rem; font-size: .92rem; }
        .dt-auth-alert-danger { background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; }
        .dt-auth-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
        .dt-auth-form { display: grid; gap: 1.15rem; }
        .dt-auth-field { display: grid; gap: .45rem; }
        .dt-auth-field label { font-size: .82rem; font-weight: 700; color: #4d5577; text-transform: uppercase; letter-spacing: .08em; }
        .dt-auth-input { width: 100%; border: 1px solid #d7dcf4; border-radius: .95rem; padding: 1rem 1.05rem; font-size: 1rem; color: #1f2547; background: #fff; transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
        .dt-auth-input::placeholder { color: #b0b6cc; }
        .dt-auth-input:focus { outline: none; border-color: #6965df; box-shadow: 0 0 0 4px rgba(105,101,223,.12); transform: translateY(-1px); }
        .dt-auth-submit { margin-top: .35rem; width: 100%; border: 0; border-radius: 999px; padding: 1rem 1.2rem; font-size: 1rem; font-weight: 800; letter-spacing: .03em; color: #fff; background: linear-gradient(135deg, #4b49ac, #6965df); cursor: pointer; transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease; box-shadow: 0 18px 34px rgba(75,73,172,.24); }
        .dt-auth-submit:hover { opacity: .96; transform: translateY(-1px); }
        .dt-auth-meta { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: .25rem; flex-wrap: wrap; }
        .dt-auth-check { display: inline-flex; align-items: center; gap: .65rem; color: #57607f; font-size: .95rem; }
        .dt-auth-check input { width: 1rem; height: 1rem; accent-color: #4b49ac; }
        .dt-auth-link { color: #252b53; font-weight: 600; text-decoration: underline; text-underline-offset: .18em; }
        .dt-auth-footer { margin-top: 2rem; text-align: center; color: #6a7292; font-size: .95rem; }
        .dt-auth-footer a { color: #5c58d4; font-weight: 700; text-decoration: none; }
        .dt-auth-footer a:hover { text-decoration: underline; }
        @media (max-width: 640px) { .dt-auth-body { padding: 2rem 1.4rem 2.1rem; } }
    </style>

    <div class="dt-auth-shell">
        <div class="dt-auth-card">
            <div class="dt-auth-body">
                <div class="dt-auth-logo">
                    <img src="{{ asset('img/image.png') }}" alt="Dakar Terminal">
                </div>

                <h1 class="dt-auth-title">{{ __('Bonjour, commençons') }}</h1>
                <p class="dt-auth-subtitle">{{ __('Connectez-vous pour accéder à la plateforme Dakar Terminal.') }}</p>

                @if ($errors->any())
                    <div class="dt-auth-alert dt-auth-alert-danger">{{ $errors->first() }}</div>
                @endif

                @if (session('status'))
                    <div class="dt-auth-alert dt-auth-alert-info">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="dt-auth-form">
                    @csrf

                    <div class="dt-auth-field">
                        <label for="email">{{ __('Adresse email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="admin@dakarterminal.sn" class="dt-auth-input">
                    </div>

                    <div class="dt-auth-field">
                        <label for="password">{{ __('Mot de passe') }}</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Votre mot de passe" class="dt-auth-input">
                    </div>

                    <button type="submit" class="dt-auth-submit" data-test="login-button">{{ __('SE CONNECTER') }}</button>

                    <div class="dt-auth-meta">
                        <label class="dt-auth-check" for="remember">
                            <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                            <span>{{ __('Se souvenir de moi') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="dt-auth-link" wire:navigate>{{ __('Mot de passe oublié ?') }}</a>
                        @endif
                    </div>
                </form>

                @if (Route::has('register'))
                    <div class="dt-auth-footer">
                        {{ __('Vous n’avez pas de compte ?') }}
                        <a href="{{ route('register') }}" wire:navigate>{{ __('Créer un compte') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::auth>
