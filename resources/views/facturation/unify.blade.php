<x-layouts::app :title="__('Gestion Unify')">
    @php
        $roleName = auth()->user()?->role?->name;
        $isAdmin = $roleName === 'ADMIN';
    @endphp

    <div class="unify-page flex min-h-full w-full flex-1 flex-col gap-6 pb-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        <style>
            .unify-page .module-tabs { display:flex; justify-content:center; gap:6px; flex-wrap:nowrap; overflow-x:auto; border-bottom:2px solid #e8e8f0; margin-bottom:22px; background:#fff; padding:0 8px 6px; scrollbar-width:thin; }
            .unify-page .module-tab { flex:0 0 auto; border:none; background:transparent; color:#6c757d; font-size:14px; font-weight:600; padding:12px 16px; border-bottom:3px solid transparent; margin-bottom:-2px; display:inline-flex; align-items:center; gap:8px; cursor:pointer; transition:color .2s; }
            .unify-page .module-tab:hover { color:#4B49AC; }
            .unify-page .module-tab.active { color:#4B49AC; border-bottom-color:#4B49AC; }
            .unify-page .module-pane { display:none; opacity:0; transform:translateY(10px); }
            .unify-page .module-pane.active { display:block; animation:tabPaneFade .25s ease forwards; }
            @keyframes tabPaneFade { from { opacity:0; transform:translateY(10px);} to { opacity:1; transform:translateY(0);} }
            .unify-page .wizard-container, .unify-page .simple-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.06); padding:24px; max-width:1000px; margin:0 auto; }
            .unify-page .wizard-header { margin-bottom:22px; text-align:center; }
            .unify-page .wizard-title { font-size:20px; font-weight:700; color:#191C24; display:flex; align-items:center; gap:10px; justify-content:center; }
            .unify-page .wizard-subtitle { color:#888; font-size:13px; }
            .unify-page .wizard-steps { display:flex; justify-content:flex-start; gap:10px; flex-wrap:nowrap; overflow-x:auto; margin:20px 0; border-bottom:1px solid #f2f2f7; padding:0 0 15px; scrollbar-width:thin; }
            .unify-page .wizard-step-indicator { flex:0 0 auto; background:#f8f9fa; color:#6c757d; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:8px; cursor:pointer; border:1px solid #eee; }
            .unify-page .wizard-step-indicator.active { background:#4B49AC; color:#fff; border-color:#4B49AC; }
            .unify-page .wizard-step { display:none; }
            .unify-page .wizard-step.active { display:block; }
            .unify-page .form-grid-layout { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px 24px; align-items:start; }
            .unify-page .form-group-custom label { font-size:13px; font-weight:600; color:#444; display:block; margin-bottom:8px; }
            .unify-page .form-control-custom { width:100%; border:1px solid #dee2e6; border-radius:8px; padding:9px 13px; font-size:13px; height:42px; min-height:42px; box-sizing:border-box; }
            .unify-page .wizard-footer { margin-top:24px; padding-top:16px; border-top:1px solid #f2f2f7; display:flex; justify-content:space-between; gap:12px; }
            .unify-page .btn-gfa { border:none; border-radius:7px; padding:10px 16px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
            .unify-page .btn-primary-gfa { background:#4B49AC; color:#fff; }
            .unify-page .btn-light-gfa { background:#f2f2f7; color:#555; }
            .unify-page .table-unify { width:100%; border-collapse:collapse; font-size:13px; }
            .unify-page .table-unify th, .unify-page .table-unify td { padding:10px 12px; border-bottom:1px solid #ececf5; text-align:left; }
            .unify-page .table-unify th { color:#4B49AC; background:#f8f9ff; }
            .unify-page .unify-section-title { font-size:20px; font-weight:700; color:#191C24; display:flex; align-items:center; gap:10px; margin-bottom:12px; }
            .unify-page .tiers-search-bar { margin-bottom:14px; }
            .unify-page .tiers-search-input { max-width:360px; }
            .unify-page .muted { color:#8d8d8d; }
            .unify-page .status-box { margin:12px 0; padding:10px; border-radius:8px; display:none; }
            .unify-page .status-success { background:#e8f7ef; color:#146c43; }
            .unify-page .status-error { background:#fdecea; color:#842029; }
            .unify-page .pagination { display:flex; justify-content:flex-end; gap:8px; margin-top:12px; align-items:center; }
            .unify-page .admin-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
            .unify-page .unify-admin-card { border:1px solid #ececf5; border-radius:10px; padding:16px; }
            .unify-page iframe.tutorial-pdf { width:100%; min-height:620px; border:1px solid #ececf5; border-radius:10px; }
            .unify-page .page-header { text-align:center; margin-bottom:20px; }
            .unify-page .page-header h1 { margin:0; display:flex; align-items:center; justify-content:center; gap:10px; }

            @media (max-width: 768px) {
                .unify-page .form-grid-layout, .unify-page .admin-grid { grid-template-columns:1fr; }
                .unify-page iframe.tutorial-pdf { min-height:420px; }
                .unify-page .wizard-footer { flex-direction:column; }
            }
        </style>

        <div class="page-header">
            <h1><i class="fas fa-clipboard" style="color:#4B49AC"></i>Gestion Unify</h1>
        </div>

        <div class="module-tabs">
            <button type="button" class="module-tab active" data-target="unify-formulaire"><i class="fas fa-file-alt"></i> Formulaire de creation</button>
            <button type="button" class="module-tab" data-target="unify-tiers"><i class="fas fa-list"></i> Liste des tiers</button>
            <button type="button" class="module-tab" data-target="unify-tutoriel"><i class="fas fa-book-open"></i> Tutoriel</button>
            @if ($isAdmin)
                <button type="button" class="module-tab" data-target="unify-admin"><i class="fas fa-cog"></i> Admin</button>
            @endif
        </div>

        <div id="unify-formulaire" class="module-pane active">
            <div class="wizard-container">
                <div class="wizard-header">
                    <div class="wizard-title"><i class="fas fa-file-invoice" style="color:#4B49AC"></i> Formulaire de creation</div>
                    <div class="wizard-subtitle">Espace dedie a la creation des demandes Unify.</div>
                </div>
                <div id="wizard-status" class="status-box"></div>
                <div class="wizard-steps">
                    <div class="wizard-step-indicator active" data-step="0">1. Informations Unify</div>
                    <div class="wizard-step-indicator" data-step="1">2. Contacts</div>
                    <div class="wizard-step-indicator" data-step="2">3. Responsable societe</div>
                    <div class="wizard-step-indicator" data-step="3">4. Finalisation</div>
                </div>

                <form id="wizard-form" method="post">
                    @csrf
                    <section class="wizard-step active" data-step="0">
                        <div class="form-grid-layout">
                            <div class="form-group-custom"><label for="dateActivite">Date</label><input id="dateActivite" name="dateActivite" type="date" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="typePersonne">Type de personne</label><input id="typePersonne" name="typePersonne" class="form-control-custom" value="MORALE"></div>
                            <div class="form-group-custom"><label for="compteIpaki">Compte Ipaki *</label><input id="compteIpaki" name="compteIpaki" class="form-control-custom" required></div>
                            <div class="form-group-custom"><label for="compteNeptune">Compte Neptune</label><input id="compteNeptune" name="compteNeptune" class="form-control-custom"></div>
                        </div>
                    </section>
                    <section class="wizard-step" data-step="1">
                        <div class="form-grid-layout">
                            <div class="form-group-custom"><label for="raisonSociale">Raison sociale *</label><input id="raisonSociale" name="raisonSociale" class="form-control-custom" required></div>
                            <div class="form-group-custom"><label for="telephone">Telephone</label><input id="telephone" name="telephone" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="email">Email</label><input id="email" name="email" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="adresse">Adresse</label><input id="adresse" name="adresse" class="form-control-custom"></div>
                        </div>
                    </section>
                    <section class="wizard-step" data-step="2">
                        <div class="form-grid-layout">
                            <div class="form-group-custom"><label for="dg">Directeur General</label><input id="dg" name="dg" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="telDg">Telephone DG</label><input id="telDg" name="telDg" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="df">Directeur Financier</label><input id="df" name="df" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="telDf">Telephone DF</label><input id="telDf" name="telDf" class="form-control-custom"></div>
                        </div>
                    </section>
                    <section class="wizard-step" data-step="3">
                        <div class="form-grid-layout">
                            <div class="form-group-custom"><label for="ninea">NINEA</label><input id="ninea" name="ninea" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="registre">Registre de commerce</label><input id="registre" name="registre" class="form-control-custom"></div>
                            <div class="form-group-custom"><button type="button" id="print-fiche-btn" class="btn-gfa btn-light-gfa"><i class="fas fa-print"></i> Imprimer Fiche</button></div>
                            <div class="form-group-custom"><button type="button" id="print-attest-btn" class="btn-gfa btn-light-gfa"><i class="fas fa-file-contract"></i> Imprimer Attestation</button></div>
                        </div>
                    </section>

                    <div class="wizard-footer">
                        <button type="button" class="btn-gfa btn-light-gfa" id="wizard-prev"><i class="fas fa-chevron-left"></i> Precedent</button>
                        <button type="button" class="btn-gfa btn-primary-gfa" id="wizard-next">Suivant <i class="fas fa-chevron-right"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div id="unify-tiers" class="module-pane">
            <div class="simple-card">
                <h3 class="unify-section-title"><i class="fas fa-list" style="color:#4B49AC"></i> Liste des tiers Unify</h3>
                <div class="tiers-search-bar">
                    <input id="tiers-search" class="form-control-custom tiers-search-input" type="search" placeholder="Rechercher un tiers (raison sociale, compte...)">
                </div>
                <div style="overflow-x:auto;">
                    <table class="table-unify">
                        <thead><tr><th>Compte Ipaki</th><th>Raison sociale</th></tr></thead>
                        <tbody id="tiers-tbody"></tbody>
                    </table>
                </div>
                <div class="pagination">
                    <button type="button" class="btn-gfa btn-light-gfa" id="tiers-prev">Precedent</button>
                    <span id="tiers-page-info" class="muted"></span>
                    <button type="button" class="btn-gfa btn-light-gfa" id="tiers-next">Suivant</button>
                </div>
            </div>
        </div>

        <div id="unify-tutoriel" class="module-pane">
            <div class="simple-card">
                <h3 class="unify-section-title"><i class="fas fa-book-open" style="color:#4B49AC"></i> Tutoriel Unify</h3>
                <p>Deposez votre PDF ici : <code>public/docs/unify-tutoriel.pdf</code>.</p>
                <iframe class="tutorial-pdf" src="/docs/unify-tutoriel.pdf" title="Tutoriel Unify"></iframe>
            </div>
        </div>

        @if ($isAdmin)
            <div id="unify-admin" class="module-pane">
                <div class="simple-card">
                    <h3 class="unify-section-title"><i class="fas fa-cog" style="color:#4B49AC"></i> Administration Unify</h3>
                    <p>Zone reservee aux utilisateurs ADMIN.</p>
                    <div class="admin-grid">
                        <div class="unify-admin-card">
                            <h5>Ajout manuel d'un tiers</h5>
                            <div id="admin-status" class="status-box"></div>
                            <div class="form-group-custom"><label for="admin-raison">Raison sociale *</label><input id="admin-raison" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="admin-ipaki">Compte Ipaki *</label><input id="admin-ipaki" class="form-control-custom"></div>
                            <div class="form-group-custom"><label for="admin-neptune">Compte Neptune</label><input id="admin-neptune" class="form-control-custom"></div>
                            <button type="button" id="admin-add-btn" class="btn-gfa btn-primary-gfa">Ajouter</button>
                        </div>
                        <div class="unify-admin-card">
                            <h5>Import / Export tiers</h5>
                            <p class="muted">Importer un fichier CSV ou XLSX puis exporter les donnees en XLSX.</p>
                            <input type="file" id="admin-import-file" accept=".csv,.xlsx" class="form-control-custom" style="height:auto; padding:8px;">
                            <div style="display:flex; gap:8px; margin-top:12px;">
                                <button type="button" id="admin-import-btn" class="btn-gfa btn-primary-gfa">Importer</button>
                                <a class="btn-gfa btn-light-gfa" href="/facturation/api/tiers-unify/export/xlsx">Exporter XLSX</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <script>
            const tabs = document.querySelectorAll('.module-tab');
            const panes = document.querySelectorAll('.module-pane');
            tabs.forEach(tab => tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                panes.forEach(p => p.classList.remove('active'));
                tab.classList.add('active');
                const target = document.getElementById(tab.dataset.target);
                if (target) target.classList.add('active');
                if (tab.dataset.target === 'unify-tiers') loadTiers();
            }));

            const steps = document.querySelectorAll('.wizard-step');
            const indicators = document.querySelectorAll('.wizard-step-indicator');
            const nextBtn = document.getElementById('wizard-next');
            const prevBtn = document.getElementById('wizard-prev');
            const wizardStatus = document.getElementById('wizard-status');
            let currentStep = 0;

            function setStatus(el, message, success) {
                if (!el) return;
                el.textContent = message;
                el.className = 'status-box ' + (success ? 'status-success' : 'status-error');
                el.style.display = 'block';
            }

            function updateWizard() {
                steps.forEach((s, i) => s.classList.toggle('active', i === currentStep));
                indicators.forEach((ind, i) => ind.classList.toggle('active', i === currentStep));
                prevBtn.style.visibility = currentStep === 0 ? 'hidden' : 'visible';
                nextBtn.innerHTML = currentStep === steps.length - 1 ? 'Terminer <i class="fas fa-check"></i>' : 'Suivant <i class="fas fa-chevron-right"></i>';
            }

            async function saveWizardTiers() {
                const payload = {
                    raisonSociale: document.getElementById('raisonSociale').value.trim(),
                    compteIpaki: document.getElementById('compteIpaki').value.trim(),
                    compteNeptune: document.getElementById('compteNeptune').value.trim() || null
                };
                if (!payload.raisonSociale || !payload.compteIpaki) {
                    setStatus(wizardStatus, 'Raison sociale et Compte Ipaki sont obligatoires.', false);
                    return;
                }
                const response = await fetch('/facturation/api/tiers-unify/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await response.text();
                if (!response.ok) {
                    setStatus(wizardStatus, data, false);
                    return;
                }
                setStatus(wizardStatus, 'Tiers enregistre avec succes.', true);
                await loadTiers(true);
            }

            nextBtn.onclick = async () => {
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    updateWizard();
                    return;
                }
                await saveWizardTiers();
            };

            prevBtn.onclick = () => {
                if (currentStep > 0) {
                    currentStep--;
                    updateWizard();
                }
            };

            indicators.forEach(ind => ind.onclick = function () {
                currentStep = parseInt(this.dataset.step, 10);
                updateWizard();
            });

            updateWizard();

            document.getElementById('print-fiche-btn').onclick = () => {
                const form = document.getElementById('wizard-form');
                form.action = '/facturation/unify/print/fiche';
                form.target = '_blank';
                form.submit();
            };

            document.getElementById('print-attest-btn').onclick = () => {
                const form = document.getElementById('wizard-form');
                form.action = '/facturation/unify/print/attestation';
                form.target = '_blank';
                form.submit();
            };

            let tiersPage = 0;
            const tiersSize = 10;
            let tiersSearch = '';

            async function loadTiers(jumpFirst = false) {
                if (jumpFirst) tiersPage = 0;
                const tbody = document.getElementById('tiers-tbody');
                const searchParam = tiersSearch ? `&search=${encodeURIComponent(tiersSearch)}` : '';
                const res = await fetch(`/facturation/api/tiers-unify?page=${tiersPage}&size=${tiersSize}${searchParam}`);
                if (!res.ok) {
                    tbody.innerHTML = '<tr><td colspan="2" class="muted">Erreur de chargement.</td></tr>';
                    return;
                }
                const page = await res.json();
                if (!page.content || page.content.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2" class="muted">Aucun tiers disponible.</td></tr>';
                } else {
                    tbody.innerHTML = page.content.map(t => `<tr><td>${t.compteIpaki ?? ''}</td><td>${t.raisonSociale ?? ''}</td></tr>`).join('');
                }
                document.getElementById('tiers-page-info').textContent = `Page ${page.page + 1} / ${Math.max(1, page.totalPages)}`;
                document.getElementById('tiers-prev').disabled = page.first;
                document.getElementById('tiers-next').disabled = page.last;
            }

            document.getElementById('tiers-prev').onclick = () => {
                if (tiersPage > 0) {
                    tiersPage--;
                    loadTiers();
                }
            };

            document.getElementById('tiers-next').onclick = () => {
                tiersPage++;
                loadTiers();
            };

            const tiersSearchInput = document.getElementById('tiers-search');
            if (tiersSearchInput) {
                tiersSearchInput.addEventListener('input', () => {
                    tiersSearch = tiersSearchInput.value.trim();
                    loadTiers(true);
                });
            }

            const adminStatus = document.getElementById('admin-status');
            const adminAddBtn = document.getElementById('admin-add-btn');
            if (adminAddBtn) {
                adminAddBtn.onclick = async () => {
                    const payload = {
                        raisonSociale: document.getElementById('admin-raison').value.trim(),
                        compteIpaki: document.getElementById('admin-ipaki').value.trim(),
                        compteNeptune: document.getElementById('admin-neptune').value.trim() || null
                    };
                    const resp = await fetch('/facturation/api/tiers-unify/save', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const msg = await resp.text();
                    setStatus(adminStatus, resp.ok ? 'Tiers ajoute.' : msg, resp.ok);
                    if (resp.ok) loadTiers(true);
                };

                document.getElementById('admin-import-btn').onclick = async () => {
                    const fileInput = document.getElementById('admin-import-file');
                    if (!fileInput.files.length) {
                        setStatus(adminStatus, 'Selectionnez un fichier CSV ou XLSX.', false);
                        return;
                    }
                    const formData = new FormData();
                    formData.append('file', fileInput.files[0]);
                    const resp = await fetch('/facturation/api/tiers-unify/import', { method: 'POST', body: formData });
                    const msg = await resp.text();
                    setStatus(adminStatus, msg, resp.ok);
                    if (resp.ok) loadTiers(true);
                };
            }
        </script>
    </div>
</x-layouts::app>
