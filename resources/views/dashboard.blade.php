<x-layouts::app :title="__('Tableau de bord')">
    <div class="dt-dashboard flex h-full w-full flex-1 flex-col gap-6">
        <style>
            .dt-dashboard {
                --dt-primary: #4b49ac;
                --dt-secondary: #7978e9;
                --dt-success: #0ac282;
                --dt-warning: #fe5f75;
                --dt-info: #11998e;
                --dt-surface: rgba(255, 255, 255, 0.92);
                --dt-border: rgba(148, 163, 184, 0.18);
                --dt-shadow: 0 20px 45px -28px rgba(15, 23, 42, 0.45);
            }

            .dark .dt-dashboard {
                --dt-surface: rgba(24, 31, 42, 0.94);
                --dt-border: rgba(71, 85, 105, 0.45);
                --dt-shadow: 0 24px 50px -30px rgba(2, 6, 23, 0.85);
            }

            .dt-shell {
                position: relative;
                overflow: hidden;
                border-radius: 1.75rem;
                border: 1px solid var(--dt-border);
                background:
                    radial-gradient(circle at top left, rgba(121, 120, 233, 0.16), transparent 28%),
                    radial-gradient(circle at top right, rgba(17, 153, 142, 0.12), transparent 24%),
                    linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(241, 245, 249, 0.9));
                box-shadow: var(--dt-shadow);
            }

            .dark .dt-shell {
                background:
                    radial-gradient(circle at top left, rgba(121, 120, 233, 0.2), transparent 28%),
                    radial-gradient(circle at top right, rgba(17, 153, 142, 0.16), transparent 24%),
                    linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.88));
            }

            .dt-stat-card {
                position: relative;
                overflow: hidden;
                border-radius: 1.25rem;
                color: #fff;
                padding: 1.4rem;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
            }

            .dt-stat-card::after {
                content: '';
                position: absolute;
                right: -2rem;
                top: -2rem;
                width: 7rem;
                height: 7rem;
                border-radius: 9999px;
                background: rgba(255, 255, 255, 0.11);
            }

            .dt-stat-primary { background: linear-gradient(135deg, #4b49ac, #7978e9); }
            .dt-stat-warning { background: linear-gradient(135deg, #fe5f75, #fc9842); }
            .dt-stat-success { background: linear-gradient(135deg, #0ac282, #0df3a3); }
            .dt-stat-info { background: linear-gradient(135deg, #11998e, #38ef7d); }

            .dt-panel {
                border: 1px solid var(--dt-border);
                background: var(--dt-surface);
                box-shadow: var(--dt-shadow);
            }

            .dt-badge {
                display: inline-flex;
                align-items: center;
                border-radius: 9999px;
                padding: 0.28rem 0.65rem;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.02em;
            }

            .dt-badge-admin {
                background: rgba(79, 70, 229, 0.12);
                color: #4338ca;
            }

            .dt-badge-user {
                background: rgba(15, 118, 110, 0.12);
                color: #0f766e;
            }

            .dark .dt-badge-admin {
                background: rgba(129, 140, 248, 0.16);
                color: #c7d2fe;
            }

            .dark .dt-badge-user {
                background: rgba(45, 212, 191, 0.16);
                color: #99f6e4;
            }

            .dt-table-row:hover {
                background: rgba(75, 73, 172, 0.04);
            }

            .dark .dt-table-row:hover {
                background: rgba(121, 120, 233, 0.08);
            }
        </style>

        <section class="dt-shell p-5 md:p-7">
            <div class="mb-6 flex flex-col gap-3 border-b border-slate-200/70 pb-6 dark:border-slate-700/70 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200">
                        <span class="inline-block h-2 w-2 rounded-full bg-indigo-500"></span>
                        Administration
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Tableau de bord</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                        Vue d'ensemble de la plateforme Dakar Terminal, avec vos indicateurs principaux et les comptes les plus récents.
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-300">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Aujourd'hui</p>
                        <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $todayLabel }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="dt-stat-card dt-stat-primary">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm/5 font-medium text-white/80">Utilisateurs actifs</p>
                            <p class="mt-4 text-4xl font-bold">{{ number_format($stats['total_users']) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/15 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m18 0v-2a4 4 0 0 0-3-3.87M16 3.13A4 4 0 0 1 16 11m-5 0A4 4 0 1 0 11 3a4 4 0 0 0 0 8Z" />
                            </svg>
                        </div>
                    </div>
                </article>

                <article class="dt-stat-card dt-stat-warning">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm/5 font-medium text-white/80">Rôles définis</p>
                            <p class="mt-4 text-4xl font-bold">{{ number_format($stats['total_roles']) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/15 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 4v5c0 5-3.5 8.74-7 9-3.5-.26-7-4-7-9V7l7-4Z" />
                            </svg>
                        </div>
                    </div>
                </article>

                <article class="dt-stat-card dt-stat-success">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm/5 font-medium text-white/80">Administrateurs</p>
                            <p class="mt-4 text-4xl font-bold">{{ number_format($stats['admin_users']) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/15 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5Zm0 0 6.16-3.422A12.083 12.083 0 0 1 18 14.576c0 3.314-2.686 5.424-6 6.424-3.314-1-6-3.11-6-6.424 0-1.27.105-2.6.84-3.998L12 14Z" />
                            </svg>
                        </div>
                    </div>
                </article>

                <article class="dt-stat-card dt-stat-info">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm/5 font-medium text-white/80">Créés aujourd'hui</p>
                            <p class="mt-4 text-4xl font-bold">{{ number_format($stats['users_today']) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/15 p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3v18h18M7 14l3-3 3 2 4-5" />
                            </svg>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.95fr)]">
            <section class="dt-panel overflow-hidden rounded-[1.5rem]">
                <div class="flex flex-col gap-3 border-b border-slate-200/70 px-5 py-4 dark:border-slate-700/70 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Derniers utilisateurs</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            En attendant un module d'audit, cette section affiche les comptes créés récemment.
                        </p>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200 dark:hover:bg-indigo-500/15">
                        Voir mon profil
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="bg-slate-50/85 dark:bg-slate-900/65">
                            <tr class="text-xs uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">
                                <th class="px-5 py-4 font-semibold">Date</th>
                                <th class="px-5 py-4 font-semibold">Utilisateur</th>
                                <th class="px-5 py-4 font-semibold">Rôle</th>
                                <th class="px-5 py-4 font-semibold">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                            @forelse ($recentUsers as $recentUser)
                                <tr class="dt-table-row transition">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500 dark:text-slate-400">
                                        {{ $recentUser->created_at?->locale('fr')->translatedFormat('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-900 text-sm font-bold text-white dark:bg-white dark:text-slate-900">
                                                {{ $recentUser->initials() }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-900 dark:text-white">{{ $recentUser->name }}</p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $recentUser->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @php($roleName = $recentUser->role?->name ?? 'USER')
                                        <span class="dt-badge {{ $roleName === 'ADMIN' ? 'dt-badge-admin' : 'dt-badge-user' }}">
                                            {{ $roleName }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                        Compte actif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Aucun utilisateur disponible pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="flex flex-col gap-6">
                <section class="dt-panel rounded-[1.5rem] p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-2xl bg-indigo-100 p-3 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Actions rapides</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Accès direct aux écrans les plus utiles.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center justify-between rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            <span>Mettre à jour mon profil</span>
                            <span>&rarr;</span>
                        </a>
                        <a href="{{ route('security.edit') }}"
                           class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-indigo-500/30 dark:hover:bg-indigo-500/10">
                            <span>Paramètres de sécurité</span>
                            <span>&rarr;</span>
                        </a>
                        <a href="{{ route('demat') }}"
                           class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-emerald-500/30 dark:hover:bg-emerald-500/10">
                            <span>Accéder à Demat</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </section>

                <section class="dt-panel rounded-[1.5rem] p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-2xl bg-emerald-100 p-3 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">À propos du système</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Résumé technique de l’environnement actuel.</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between border-b border-slate-200/70 pb-3 dark:border-slate-700/70">
                            <span class="text-slate-500 dark:text-slate-400">Application</span>
                            <strong class="text-slate-800 dark:text-slate-100">Dakar Terminal v1.0</strong>
                        </div>
                        <div class="flex items-center justify-between border-b border-slate-200/70 pb-3 dark:border-slate-700/70">
                            <span class="text-slate-500 dark:text-slate-400">Stack</span>
                            <strong class="text-slate-800 dark:text-slate-100">Laravel 13 · Livewire 4 · MySQL</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Sécurité</span>
                            <strong class="text-slate-800 dark:text-slate-100">Fortify + sessions + mot de passe haché</strong>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-layouts::app>
