<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="dt-app-shell min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="dt-sidebar border-e">
            <flux:sidebar.header class="px-4 pt-4">
                <div class="dt-brand w-full">
                    <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                </div>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="px-3 pb-2 pt-3">
                <flux:sidebar.group :heading="__('Platform')" class="grid gap-2">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="book-open-text" :href="route('facturation.dashboard')" :current="request()->routeIs('facturation.dashboard')" wire:navigate>
                        {{ __('Facturation') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if (request()->routeIs('facturation.*'))
                    <flux:sidebar.group :heading="__('Facturation')" class="mt-4 grid gap-2">
                        <flux:sidebar.item icon="computer-desktop" :href="route('facturation.guichet-gfa.public')" target="_blank" rel="noopener noreferrer">
                            {{ __('Guichet GFA') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="check-badge" :href="route('facturation.validations')" :current="request()->routeIs('facturation.validations')" wire:navigate>
                            {{ __('Gestion des validations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="percent-badge" :href="route('facturation.remises')" :current="request()->routeIs('facturation.remises')" wire:navigate>
                            {{ __('Gestion de remises') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('facturation.unify')" :current="request()->routeIs('facturation.unify')" wire:navigate>
                            {{ __('Gestion Unify') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('facturation.ies')" :current="request()->routeIs('facturation.ies')" wire:navigate>
                            {{ __('Gestion IES') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Administration')" class="mt-4 grid gap-2">
                        <flux:sidebar.item icon="server-stack" :href="route('facturation.gfa-admin')" :current="request()->routeIs('facturation.gfa-admin')" wire:navigate>
                            {{ __('Gfa Admin') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav class="px-3 pb-4">
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="dt-user-panel hidden px-3 pb-4 lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="dt-topbar lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end" class="dt-user-menu">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
