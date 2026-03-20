<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

@include('partials.app-icons')

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert2 theme helper — adapte automatiquement les couleurs du theme DT
    function dtSwalTheme() {
        const style = getComputedStyle(document.documentElement);
        return {
            background: style.getPropertyValue('--dt-panel-bg').trim() || '#ffffff',
            color: style.getPropertyValue('--dt-page-text').trim() || '#1e293b',
            cancelButtonColor: '#64748b',
        };
    }

    // Surcharge globale de window.alert et window.confirm
    window.alert = function (message) {
        Swal.fire({ ...dtSwalTheme(), icon: 'info', title: message, confirmButtonColor: '#4B49AC' });
    };

    window.confirm = function (message) {
        // confirm() synchrone ne peut pas etre remplace par Swal (asynchrone)
        // Utiliser dtConfirm() pour les confirmations dans ce projet
        return true;
    };

    // Helper asynchrone global pour les confirmations
    window.dtConfirm = async function (options = {}) {
        const result = await Swal.fire({
            ...dtSwalTheme(),
            icon: options.icon || 'question',
            title: options.title || 'Confirmer ?',
            text: options.text || '',
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'Confirmer',
            cancelButtonText: options.cancelText || 'Annuler',
            confirmButtonColor: options.confirmColor || '#4B49AC',
        });
        return result.isConfirmed;
    };

    // Helper global pour les notifications toast
    window.dtToast = function (message, type = 'success') {
        Swal.fire({
            ...dtSwalTheme(),
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    };
</script>
