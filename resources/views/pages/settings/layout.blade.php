<style>
    .settings-shell {
        display: flex;
        align-items: flex-start;
        gap: 28px;
    }

    .settings-sidebar {
        width: 100%;
        max-width: 240px;
        flex-shrink: 0;
    }

    .settings-sidebar-card,
    .settings-content-card {
        background: #fff;
        border: 1px solid #ececf5;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    }

    .settings-sidebar-card {
        padding: 14px;
    }

    .settings-content {
        flex: 1;
        min-width: 0;
    }

    .settings-content-card {
        padding: 28px;
    }

    .settings-title {
        font-size: 28px;
        font-weight: 700;
        color: #191c24;
        margin: 0;
    }

    .settings-subtitle {
        margin: 8px 0 0;
        color: #6c757d;
        font-size: 14px;
    }

    .settings-card-head {
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid #eef0f6;
    }

    .settings-form-wrap {
        width: 100%;
        max-width: 760px;
    }

    .settings-content-card form + form,
    .settings-content-card form + div,
    .settings-content-card div + form {
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid #eef0f6;
    }

    .settings-content-card [data-flux-field],
    .settings-content-card .space-y-6,
    .settings-content-card .space-y-8 {
        width: 100%;
    }

    @media (max-width: 960px) {
        .settings-shell {
            flex-direction: column;
        }

        .settings-sidebar {
            max-width: none;
        }

        .settings-content-card {
            padding: 22px;
        }
    }
</style>

<div class="settings-shell">
    <div class="settings-sidebar">
        <div class="settings-sidebar-card">
            <flux:navlist aria-label="{{ __('Settings') }}">
                <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
                <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
                <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            </flux:navlist>
        </div>
    </div>

    <div class="settings-content">
        <div class="settings-content-card">
            <div class="settings-card-head">
                <h2 class="settings-title">{{ $heading ?? '' }}</h2>
                <p class="settings-subtitle">{{ $subheading ?? '' }}</p>
            </div>

            <div class="settings-form-wrap">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
