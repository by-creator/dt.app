<x-layouts::app :title="__('Gestion des rapports')">
    @php
        $isAdmin = auth()->user()?->role?->name === 'ADMIN';
    @endphp

    <div class="rapport-page flex h-full w-full flex-1 flex-col gap-6 pb-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        <style>
            .rapport-page { overflow: visible; min-height: max-content; }
            .rapport-page .module-tabs { display:flex; justify-content:center; gap:6px; flex-wrap:nowrap; overflow-x:auto; overflow-y:hidden; border-bottom:2px solid var(--dt-border); margin-bottom:22px; background:var(--dt-panel-bg); padding:0 8px 6px; scrollbar-width:thin; box-shadow:var(--dt-shadow); }
            .rapport-page .module-tab { flex:0 0 auto; border:none; background:transparent; color:var(--dt-muted-text); font-size:14px; font-weight:600; padding:12px 16px; border-bottom:3px solid transparent; margin-bottom:-2px; display:inline-flex; align-items:center; gap:8px; cursor:pointer; transition:color .2s; }
            .rapport-page .module-tab:hover { color:#4B49AC; }
            .rapport-page .module-tab.active { color:#4B49AC; border-bottom-color:#4B49AC; }
            .rapport-page .module-pane { display:none; opacity:0; transform:translateY(10px); }
            .rapport-page .module-pane.active { display:block; animation:tabPaneFade .25s ease forwards; }
            @keyframes tabPaneFade { from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)} }
            .rapport-page .r-card { background:var(--dt-panel-bg); color:var(--dt-page-text); border:1px solid var(--dt-border); border-radius:12px; box-shadow:var(--dt-shadow); padding:24px; max-width:1200px; margin:0 auto; }
            .rapport-page .r-card-title { font-size:20px; font-weight:700; color:var(--dt-page-text); display:flex; align-items:center; gap:10px; margin-bottom:16px; }
            .rapport-page .r-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
            .rapport-page .r-toolbar-left { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
            .rapport-page .r-search { width:260px; border:1px solid var(--dt-input-border); background:var(--dt-input-bg); color:var(--dt-page-text); border-radius:7px; padding:7px 12px; font-size:13px; outline:none; }
            .rapport-page .r-search:focus { border-color:#4B49AC; box-shadow:0 0 0 4px var(--dt-ring); }
            .rapport-page .r-btn { border:none; border-radius:7px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px; min-height:36px; text-decoration:none; transition:opacity .15s; }
            .rapport-page .r-btn:hover { opacity:.88; }
            .rapport-page .r-btn-primary { background:#4B49AC; color:#fff; }
            .rapport-page .r-btn-light { background:var(--dt-panel-alt-bg); color:#818cf8; border:1px solid var(--dt-border); }
            .rapport-page .r-btn-export { background:#28a745; color:#fff; }
            .rapport-page .r-file-input { border:1px solid var(--dt-input-border); background:var(--dt-input-bg); color:var(--dt-page-text); border-radius:8px; padding:8px; font-size:13px; width:100%; margin-bottom:14px; }
            .rapport-page .r-admin-card { border:1px solid var(--dt-border); background:var(--dt-panel-alt-bg); border-radius:10px; padding:18px; }
            .rapport-page .r-status { margin:12px 0; padding:10px 14px; border-radius:8px; display:none; font-size:13px; }
            .rapport-page .r-status-ok  { background:var(--dt-success-bg); color:var(--dt-success-text); border:1px solid var(--dt-success-border); }
            .rapport-page .r-status-err { background:var(--dt-danger-bg);  color:var(--dt-danger-text);  border:1px solid var(--dt-danger-border); }
        </style>

        <div class="dt-page-header" style="text-align:center">
            <h1><i class="fas fa-boxes-stacked" style="color:#4B49AC"></i>Gestion des rapports</h1>
        </div>

        <div class="module-tabs">
            <button type="button" class="module-tab active" data-target="rapport-suivi"><i class="fas fa-list"></i> Liste</button>
            @if ($isAdmin)
                <button type="button" class="module-tab" data-target="rapport-admin"><i class="fas fa-cog"></i> Admin</button>
            @endif
        </div>

        <div id="rapport-suivi" class="module-pane active">
            <div class="r-card">
                <div class="r-toolbar">
                    <div class="r-toolbar-left">
                        <input id="rapport-search" class="r-search" type="search" placeholder="Rechercher...">
                        <button type="button" class="r-btn r-btn-primary" id="rapport-refresh"><i class="fas fa-sync-alt"></i> Actualiser</button>
                    </div>
                    <a href="/facturation/api/rapports/export" class="r-btn r-btn-export"><i class="fas fa-file-excel"></i> Exporter Excel</a>
                </div>

                <div class="dt-table-card">
                    <div class="dt-table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Terminal</th>
                                    <th>Equipment Number</th>
                                    <th>Type / Size</th>
                                    <th>Event Code</th>
                                    <th>Event Name</th>
                                    <th>Event Family</th>
                                    <th>Event Date</th>
                                    <th>Booking Sec No</th>
                                </tr>
                            </thead>
                            <tbody id="rapport-tbody">
                                <tr><td colspan="8" class="dt-empty-state"><i class="fas fa-spinner fa-spin fa-2x" style="display:block;margin-bottom:10px;color:#ccc"></i>Chargement...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="dt-pagination-bar" id="rapport-pagination-bar" style="display:none">
                        <span id="rapport-count-info"></span>
                        <div class="dt-pagination-pages" id="rapport-pagination-pages"></div>
                    </div>
                </div>
            </div>
        </div>

        @if ($isAdmin)
            <div id="rapport-admin" class="module-pane">
                <div class="r-card">
                    <div class="r-card-title"><i class="fas fa-cog" style="color:#4B49AC"></i> Administration - Suivi des vides</div>
                    <div id="admin-status" class="r-status"></div>
                    <div class="r-admin-card">
                        <h5 style="margin-bottom:8px;font-weight:700;color:var(--dt-page-text)">Importer un fichier</h5>
                        <p style="font-size:13px;color:var(--dt-muted-text);margin-bottom:14px">
                            Formats acceptes : <strong>XLSX, CSV</strong> (separateur , ou ;).<br>
                            Colonnes attendues : Terminal, EquipmentNumber, EquipmentTypeSize, EventCode, EventName, EventFamily, EventDate, Booking Sec No.
                        </p>
                        <input type="file" id="admin-import-file" accept=".csv,.xlsx" class="r-file-input">
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <button type="button" id="admin-import-btn" class="r-btn r-btn-primary"><i class="fas fa-upload"></i> Importer</button>
                            <a href="/facturation/api/rapports/export" class="r-btn r-btn-export"><i class="fas fa-file-excel"></i> Exporter Excel</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            document.querySelectorAll('.module-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.module-tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.module-pane').forEach(p => p.classList.remove('active'));
                    tab.classList.add('active');
                    const target = document.getElementById(tab.dataset.target);
                    if (target) target.classList.add('active');
                    if (tab.dataset.target === 'rapport-suivi') loadRapports();
                });
            });

            function setStatus(el, message, success) {
                if (!el) return;
                el.textContent = message;
                el.className = 'r-status ' + (success ? 'r-status-ok' : 'r-status-err');
                el.style.display = 'block';
            }

            function escHtml(str) {
                if (str == null) return '';
                return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }

            const PAGE_SIZE = 15;
            let allRapports = [];
            let rapportCurrentPage = 1;
            let rapportSearch = '';

            async function loadRapports() {
                const tbody = document.getElementById('rapport-tbody');
                tbody.innerHTML = '<tr><td colspan="8" class="dt-empty-state"><i class="fas fa-spinner fa-spin fa-2x" style="display:block;margin-bottom:10px;color:#ccc"></i>Chargement...</td></tr>';
                try {
                    const searchParam = rapportSearch ? `&search=${encodeURIComponent(rapportSearch)}` : '';
                    const res = await fetch(`/facturation/api/rapports?page=0&size=9999${searchParam}`);
                    const data = await res.json();
                    allRapports = data.content || [];
                    renderRapportPage(1);
                } catch {
                    tbody.innerHTML = '<tr><td colspan="8" class="dt-empty-state">Erreur de chargement.</td></tr>';
                }
            }

            function renderRapportPage(page) {
                rapportCurrentPage = page;
                const total = allRapports.length;
                const pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                if (rapportCurrentPage > pages) rapportCurrentPage = pages;
                const start = (rapportCurrentPage - 1) * PAGE_SIZE;
                const slice = allRapports.slice(start, start + PAGE_SIZE);
                const tbody = document.getElementById('rapport-tbody');

                if (total === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="dt-empty-state"><i class="fas fa-inbox fa-2x" style="display:block;margin-bottom:10px;color:#ccc"></i>Aucune donnee disponible.</td></tr>';
                } else {
                    tbody.innerHTML = slice.map(r => `<tr>
                        <td>${escHtml(r.terminal)}</td>
                        <td>${escHtml(r.equipmentNumber)}</td>
                        <td>${escHtml(r.equipmentTypeSize)}</td>
                        <td>${escHtml(r.eventCode)}</td>
                        <td>${escHtml(r.eventName)}</td>
                        <td>${escHtml(r.eventFamily)}</td>
                        <td>${escHtml(r.eventDate)}</td>
                        <td>${escHtml(r.bookingSecNo)}</td>
                    </tr>`).join('');
                }

                const bar = document.getElementById('rapport-pagination-bar');
                bar.style.display = total > PAGE_SIZE ? 'flex' : 'none';
                document.getElementById('rapport-count-info').textContent = `${start + 1}-${Math.min(start + PAGE_SIZE, total)} sur ${total}`;

                let html = `<button class="dt-page-btn" onclick="renderRapportPage(${rapportCurrentPage - 1})" ${rapportCurrentPage <= 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                for (let p = 1; p <= pages; p++) {
                    if (pages <= 7 || p === 1 || p === pages || Math.abs(p - rapportCurrentPage) <= 1) {
                        html += `<button class="dt-page-btn ${p === rapportCurrentPage ? 'active' : ''}" onclick="renderRapportPage(${p})">${p}</button>`;
                    } else if (Math.abs(p - rapportCurrentPage) === 2) {
                        html += '<span style="padding:4px 6px;color:var(--dt-muted-text)">...</span>';
                    }
                }
                html += `<button class="dt-page-btn" onclick="renderRapportPage(${rapportCurrentPage + 1})" ${rapportCurrentPage >= pages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                document.getElementById('rapport-pagination-pages').innerHTML = html;
            }

            document.getElementById('rapport-refresh').onclick = () => loadRapports();
            document.getElementById('rapport-search').addEventListener('input', function () {
                rapportSearch = this.value.trim();
                loadRapports();
            });

            const adminImportBtn = document.getElementById('admin-import-btn');
            if (adminImportBtn) {
                adminImportBtn.onclick = async () => {
                    const fileInput = document.getElementById('admin-import-file');
                    const adminStatus = document.getElementById('admin-status');
                    if (!fileInput.files.length) {
                        setStatus(adminStatus, 'Selectionnez un fichier a importer.', false);
                        return;
                    }
                    adminImportBtn.disabled = true;
                    adminImportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Import en cours...';
                    const formData = new FormData();
                    formData.append('file', fileInput.files[0]);
                    formData.append('_token', csrfToken);
                    const resp = await fetch('/facturation/api/rapports/import', { method: 'POST', body: formData });
                    const msg = await resp.text();
                    setStatus(adminStatus, msg, resp.ok);
                    if (resp.ok) {
                        fileInput.value = '';
                        dtToast(msg, 'success');
                        loadRapports();
                    }
                    adminImportBtn.disabled = false;
                    adminImportBtn.innerHTML = '<i class="fas fa-upload"></i> Importer';
                };
            }

            loadRapports();
        </script>
    </div>
</x-layouts::app>
