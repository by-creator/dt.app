<x-layouts::app :title="__('Administration')">
    <div class="admin-page flex h-full w-full flex-1 flex-col gap-6 pb-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
            .admin-page { overflow: visible; min-height: max-content; }
            .admin-page .page-header { text-align:center; margin-bottom:20px; }
            .admin-page .page-header h1 { margin:0; display:flex; align-items:center; justify-content:center; gap:10px; }
            .admin-page .module-tabs { display:flex; justify-content:center; gap:6px; flex-wrap:nowrap; overflow-x:auto; overflow-y:hidden; border-bottom:2px solid #e8e8f0; margin-bottom:22px; background:#fff; padding:0 8px 6px; scrollbar-width:thin; }
            .admin-page .module-tab { flex:0 0 auto; border:none; background:transparent; color:#6c757d; font-size:14px; font-weight:600; padding:12px 16px; border-bottom:3px solid transparent; margin-bottom:-2px; display:inline-flex; align-items:center; gap:8px; cursor:pointer; transition:color .2s; }
            .admin-page .module-tab:hover { color:#4B49AC; }
            .admin-page .module-tab.active { color:#4B49AC; border-bottom-color:#4B49AC; }
            .admin-page .module-pane { display:none; opacity:0; transform:translateY(10px); }
            .admin-page .module-pane.active { display:block; animation:tabPaneFade .25s ease forwards; }
            @keyframes tabPaneFade { from { opacity:0; transform:translateY(10px);} to { opacity:1; transform:translateY(0);} }
            .admin-page .simple-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.06); padding:24px; max-width:1100px; margin:0 auto; }
            .admin-page .unify-section-title { font-size:20px; font-weight:700; color:#191C24; display:flex; align-items:center; gap:10px; margin-bottom:12px; }
            .admin-page .muted { color:#8d8d8d; }
            .admin-page .status-box { margin:12px auto; padding:10px 14px; border-radius:8px; max-width:1100px; }
            .admin-page .status-success { background:#e8f7ef; color:#146c43; }
            .admin-page .status-error { background:#fdecea; color:#842029; }
            .admin-page .status-warning { background:#fff7e6; color:#9a6700; }
            .admin-page .admin-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
            .admin-page .admin-card { border:1px solid #ececf5; border-radius:10px; padding:16px; background:#fff; }
            .admin-page .split-grid { display:grid; grid-template-columns:minmax(320px, 0.9fr) minmax(0, 1.35fr); gap:16px; align-items:start; }
            .admin-page .form-grid-layout { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px 24px; align-items:start; }
            .admin-page .form-group-custom label { font-size:13px; font-weight:600; color:#444; display:block; margin-bottom:8px; }
            .admin-page .form-control-custom { width:100%; border:1px solid #dee2e6; border-radius:8px; padding:9px 13px; font-size:13px; min-height:42px; box-sizing:border-box; }
            .admin-page .form-control-custom:focus { border-color:#4B49AC; outline:none; }
            .admin-page .btn-gfa { border:none; border-radius:7px; padding:10px 16px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
            .admin-page .btn-primary-gfa { background:#4B49AC; color:#fff; }
            .admin-page .btn-light-gfa { background:#f2f2f7; color:#555; }
            .admin-page .btn-danger-gfa { background:#dc3545; color:#fff; }
            .admin-page .table-unify { width:100%; border-collapse:collapse; font-size:13px; }
            .admin-page .table-unify th, .admin-page .table-unify td { padding:10px 12px; border-bottom:1px solid #ececf5; text-align:left; vertical-align:top; }
            .admin-page .table-unify th { color:#4B49AC; background:#f8f9ff; }
            .admin-page .actions { display:flex; flex-wrap:wrap; gap:8px; }
            .admin-page .stack { display:grid; gap:10px; }
            .admin-page .list-card { min-height:100%; }
            .admin-page .list-scroll { overflow-x:auto; }
            .admin-page .search-toolbar { margin:18px 0 14px; }
            .admin-page .inline-user-form { display:grid; grid-template-columns:minmax(170px,1.1fr) minmax(200px,1.4fr) minmax(150px,.9fr) minmax(240px,1.5fr) auto; gap:10px; align-items:start; }
            .admin-page .inline-user-form .password-stack { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
            .admin-page .inline-user-actions { display:flex; gap:8px; flex-wrap:wrap; }

            @media (max-width: 768px) {
                .admin-page .admin-grid,
                .admin-page .form-grid-layout,
                .admin-page .split-grid { grid-template-columns:1fr; }
                .admin-page .actions { flex-direction:column; }
                .admin-page .inline-user-form { grid-template-columns:1fr; }
                .admin-page .inline-user-form .password-stack { grid-template-columns:1fr; }
                .admin-page .inline-user-actions { flex-direction:column; }
            }
        </style>

        <div class="page-header">
            <h1><i class="fas fa-user-shield" style="color:#4B49AC"></i>Administration</h1>
        </div>

        <div class="module-tabs">
            <button type="button" class="module-tab {{ ($activeTab ?? 'admin-roles') === 'admin-roles' ? 'active' : '' }}" data-target="admin-roles"><i class="fas fa-shield-alt"></i> Roles</button>
            <button type="button" class="module-tab {{ ($activeTab ?? 'admin-roles') === 'admin-users' ? 'active' : '' }}" data-target="admin-users"><i class="fas fa-users-cog"></i> Utilisateurs</button>
        </div>

        <div id="admin-roles" class="module-pane {{ ($activeTab ?? 'admin-roles') === 'admin-roles' ? 'active' : '' }}">
            <div class="split-grid">
                <div class="simple-card">
                    <h3 class="unify-section-title"><i class="fas fa-plus-circle" style="color:#4B49AC"></i> Enregistrement role</h3>
                    <p class="muted" style="margin-bottom:16px;">Ajoutez un nouveau role dans le meme esprit que les ecrans Unify.</p>

                    <div class="admin-card">
                        <h5 style="margin-bottom:14px;">Ajouter un role</h5>
                        <form method="POST" action="{{ route('administration.roles.store') }}" class="stack">
                            @csrf
                            <input type="hidden" name="tab" value="admin-roles">
                            <div class="form-group-custom">
                                <label for="role_name">Nom du role *</label>
                                <input id="role_name" name="name" class="form-control-custom" placeholder="Ex: FACTURATION" required>
                            </div>
                            <button type="submit" class="btn-gfa btn-primary-gfa">Ajouter</button>
                        </form>
                    </div>
                </div>

                <div class="simple-card list-card">
                    <h3 class="unify-section-title"><i class="fas fa-list" style="color:#4B49AC"></i> Liste des roles</h3>
                    <p class="muted" style="margin-bottom:16px;">Consultez et mettez a jour les roles existants.</p>

                    <form method="GET" action="{{ route('administration.index') }}" class="search-toolbar">
                        <input type="hidden" name="tab" value="admin-roles">
                        <input type="search" name="role_search" value="{{ request('role_search') }}" class="form-control-custom" placeholder="Rechercher un role...">
                    </form>

                    <div class="list-scroll" style="margin-top:18px;">
                        <table class="table-unify">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Utilisateurs</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>
                                            <form method="POST" action="{{ route('administration.roles.update', $role) }}" class="stack">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="tab" value="admin-roles">
                                                <input name="name" value="{{ $role->name }}" class="form-control-custom" required>
                                        </td>
                                        <td>{{ $role->users_count }}</td>
                                        <td>
                                                <div class="actions">
                                                    <button type="submit" class="btn-gfa btn-primary-gfa">Enregistrer</button>
                                            </form>
                                                    <form method="POST" action="{{ route('administration.roles.destroy', $role) }}" onsubmit="return confirm('Supprimer ce role ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="tab" value="admin-roles">
                                                        <button type="submit" class="btn-gfa btn-danger-gfa">Supprimer</button>
                                                    </form>
                                                </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="muted">Aucun role disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top:18px;">
                        {{ $roles->appends(['tab' => 'admin-roles', 'role_search' => request('role_search')])->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div id="admin-users" class="module-pane {{ ($activeTab ?? 'admin-roles') === 'admin-users' ? 'active' : '' }}">
            <div class="split-grid">
                <div class="simple-card">
                    <h3 class="unify-section-title"><i class="fas fa-user-plus" style="color:#4B49AC"></i> Enregistrement utilisateur</h3>
                    <p class="muted" style="margin-bottom:16px;">Associez un compte a un role des sa creation.</p>

                    <div class="admin-card">
                        <h5 style="margin-bottom:14px;">Ajouter un utilisateur</h5>
                        <form method="POST" action="{{ route('administration.users.store') }}">
                            @csrf
                            <input type="hidden" name="tab" value="admin-users">
                            <div class="form-grid-layout">
                                <div class="form-group-custom">
                                    <label for="user_name">Nom *</label>
                                    <input id="user_name" name="name" class="form-control-custom" required>
                                </div>
                                <div class="form-group-custom">
                                    <label for="user_email">Email *</label>
                                    <input id="user_email" name="email" type="email" class="form-control-custom" required>
                                </div>
                                <div class="form-group-custom">
                                    <label for="user_role">Role *</label>
                                    <select id="user_role" name="role_id" class="form-control-custom" required>
                                        @foreach ($rolesForSelect as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group-custom">
                                    <label for="user_password">Mot de passe *</label>
                                    <input id="user_password" name="password" type="password" class="form-control-custom" required>
                                </div>
                                <div class="form-group-custom">
                                    <label for="user_password_confirmation">Confirmation *</label>
                                    <input id="user_password_confirmation" name="password_confirmation" type="password" class="form-control-custom" required>
                                </div>
                            </div>
                            <div style="margin-top:14px;">
                                <button type="submit" class="btn-gfa btn-primary-gfa">Creer l'utilisateur</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="simple-card list-card">
                    <h3 class="unify-section-title"><i class="fas fa-users-cog" style="color:#4B49AC"></i> Liste des utilisateurs</h3>
                    <p class="muted" style="margin-bottom:16px;">Mettez a jour les comptes existants et leurs permissions.</p>

                    <form method="GET" action="{{ route('administration.index') }}" class="search-toolbar">
                        <input type="hidden" name="tab" value="admin-users">
                        <input type="search" name="user_search" value="{{ request('user_search') }}" class="form-control-custom" placeholder="Rechercher un utilisateur, email ou role...">
                    </form>

                    <div class="list-scroll">
                        <table class="table-unify">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Mot de passe</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>
                                            <input form="user-update-{{ $user->id }}" name="name" value="{{ $user->name }}" class="form-control-custom" required>
                                        </td>
                                        <td>
                                            <input form="user-update-{{ $user->id }}" name="email" type="email" value="{{ $user->email }}" class="form-control-custom" required>
                                        </td>
                                        <td>
                                            <select form="user-update-{{ $user->id }}" name="role_id" class="form-control-custom" required>
                                                @foreach ($rolesForSelect as $role)
                                                    <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="password-stack">
                                                <input form="user-update-{{ $user->id }}" name="password" type="password" class="form-control-custom" placeholder="Laisser vide pour conserver">
                                                <input form="user-update-{{ $user->id }}" name="password_confirmation" type="password" class="form-control-custom" placeholder="Confirmation">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="inline-user-actions">
                                                <form id="user-update-{{ $user->id }}" method="POST" action="{{ route('administration.users.update', $user) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="tab" value="admin-users">
                                                    <button type="submit" class="btn-gfa btn-primary-gfa">Mettre a jour</button>
                                                </form>
                                                <form method="POST" action="{{ route('administration.users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="tab" value="admin-users">
                                                    <button type="submit" class="btn-gfa btn-danger-gfa">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="muted">Aucun utilisateur disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top:18px;">
                        {{ $users->appends(['tab' => 'admin-users', 'user_search' => request('user_search')])->links() }}
                    </div>
                </div>
            </div>
        </div>

        <script>
            const adminTabs = document.querySelectorAll('.admin-page .module-tab');
            const adminPanes = document.querySelectorAll('.admin-page .module-pane');

            adminTabs.forEach(tab => tab.addEventListener('click', () => {
                adminTabs.forEach(t => t.classList.remove('active'));
                adminPanes.forEach(p => p.classList.remove('active'));
                tab.classList.add('active');
                const target = document.getElementById(tab.dataset.target);
                if (target) target.classList.add('active');
            }));

            @if (session('admin_success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Succes',
                    text: @json(session('admin_success')),
                    confirmButtonColor: '#4B49AC'
                });
            @endif

            @if (session('admin_error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: @json(session('admin_error')),
                    confirmButtonColor: '#4B49AC'
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation',
                    html: @json(implode('<br>', $errors->all())),
                    confirmButtonColor: '#4B49AC'
                });
            @endif
        </script>
    </div>
</x-layouts::app>
