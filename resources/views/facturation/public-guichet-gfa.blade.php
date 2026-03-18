<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guichet GFA - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            color-scheme: light;
            --primary: #4B49AC;
            --primary-dark: #3e3d99;
            --bg: #f4f6fb;
            --text: #191c24;
            --muted: #6b7280;
            --card: #ffffff;
            --border: #dee2e6;
            --shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Instrument Sans', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(75, 73, 172, 0.10), transparent 28%),
                radial-gradient(circle at top right, rgba(40, 167, 69, 0.08), transparent 22%),
                var(--bg);
            color: var(--text);
        }

        main {
            width: min(1180px, calc(100vw - 32px));
            margin: 0 auto;
            padding: 28px 0 40px;
        }

        .page-header {
            margin-bottom: 20px;
        }

        .page-header h1 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-size: clamp(1.8rem, 2vw, 2.35rem);
            font-weight: 800;
        }

        .guichet-header-card { background:#fff; border-radius:12px; box-shadow:var(--shadow); padding:28px 24px 22px; text-align:center; margin-bottom:20px; }
        .guichet-selects { display:flex; flex-wrap:wrap; gap:14px; justify-content:center; margin-bottom:16px; }
        .guichet-selects select { border:1px solid #d0d0e8; border-radius:8px; padding:9px 16px; font-size:14px; font-family:inherit; color:#343a40; background:#fff; min-width:220px; cursor:pointer; outline:none; transition:border-color .2s; }
        .guichet-selects select:focus { border-color:var(--primary); }
        .client-en-cours { font-size:26px; font-weight:800; color:#191C24; margin-bottom:6px; transition:color .3s; }
        .client-en-cours.active { color:var(--primary); }
        .client-attente-msg { font-size:13px; color:#9e9e9e; }
        .service-badge { display:inline-block; background:#f0f0fb; color:var(--primary); border-radius:6px; padding:3px 12px; font-size:12px; font-weight:700; letter-spacing:1px; margin-bottom:10px; }
        .guichet-alert { display:none; background:#fff3cd; border:1px solid #ffc107; border-radius:8px; padding:10px 18px; font-size:13px; color:#856404; text-align:center; margin-bottom:14px; }
        .guichet-alert.show { display:block; }
        .action-buttons { display:flex; flex-wrap:wrap; justify-content:center; gap:12px; margin-bottom:14px; }
        .action-buttons .btn-action { display:flex; align-items:center; gap:8px; padding:10px 24px; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; color:#fff; transition:opacity .2s, transform .1s; }
        .action-buttons .btn-action:disabled { opacity:.45; cursor:not-allowed; transform:none !important; }
        .action-buttons .btn-action:not(:disabled):hover { opacity:.88; transform:translateY(-1px); }
        .action-buttons .btn-action:not(:disabled):active { transform:translateY(0); }
        .btn-suivant { background:var(--primary); } .btn-rappel { background:#6c757d; } .btn-termine { background:#28a745; }
        .btn-incomplet { background:#ffc107; color:#212529 !important; } .btn-absent { background:#dc3545; }
        .guichet-bottom { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .queue-card, .guide-card { background:#fff; border-radius:12px; box-shadow:var(--shadow); padding:24px; }
        .queue-title { font-size:14px; font-weight:700; color:#191C24; margin-bottom:14px; }
        .guichet-tabs { display:flex; gap:0; border-bottom:1px solid var(--border); margin-bottom:18px; }
        .guichet-tabs .tab-item { padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; border-bottom:2px solid transparent; color:#6c757d; background:none; border-top:none; border-left:none; border-right:none; transition:.2s; }
        .guichet-tabs .tab-item.active { color:var(--primary); border-bottom-color:var(--primary); }
        .tab-pane { display:none; } .tab-pane.active { display:block; }
        .tab-empty-msg { font-size:13px; color:#9e9e9e; margin-bottom:12px; }
        .ticket-info { font-size:13px; color:#555; margin-bottom:14px; }
        .btn-ticket { background:var(--primary); color:#fff; border:none; border-radius:6px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; transition:.2s; }
        .btn-ticket:hover { background:var(--primary-dark); } .btn-ticket:disabled { opacity:.45; cursor:not-allowed; }
        .queue-list { list-style:none; padding:0; margin:0; }
        .queue-item { display:flex; align-items:center; gap:10px; padding:7px 0; border-bottom:1px solid #f2f2f7; font-size:13px; }
        .queue-item:last-child { border-bottom:none; }
        .queue-item .q-num { font-weight:700; color:var(--primary); min-width:50px; }
        .queue-item .q-time { color:#aaa; font-size:11px; margin-left:auto; }
        .guide-card .guide-title { font-size:14px; font-weight:700; color:#191C24; margin-bottom:14px; }
        .guide-card ul { list-style:none; padding:0; margin:0; }
        .guide-card ul li { font-size:13px; color:#555; padding:5px 0; display:flex; align-items:flex-start; gap:6px; }
        .guide-card ul li::before { content:'•'; color:var(--primary); font-weight:700; flex-shrink:0; }
        .guide-card ul li strong { color:#191C24; }
        .ws-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:4px; background:#dc3545; transition:background .3s; }
        .ws-dot.connected { background:#28a745; }
        .ws-status { font-size:11px; color:#aaa; text-align:center; margin-top:8px; }
        .tk-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; }
        .tk-overlay.open { display:flex; }
        .tk-modal { background:#fff; border-radius:14px; padding:28px 24px; width:100%; max-width:400px; box-shadow:0 12px 48px rgba(0,0,0,.18); position:relative; }
        .tk-modal h5 { font-size:16px; font-weight:700; color:#191C24; margin-bottom:18px; }
        .tk-modal label { font-size:12px; font-weight:700; color:#555; display:block; margin-bottom:4px; margin-top:12px; }
        .tk-modal select, .tk-modal input { width:100%; border:1px solid var(--border); border-radius:7px; padding:8px 12px; font-size:14px; font-family:inherit; color:#343a40; outline:none; }
        .tk-modal select:focus, .tk-modal input:focus { border-color:var(--primary); }
        .tk-modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
        .tk-btn-cancel { background:#f2f2f7; color:#555; border:none; border-radius:7px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; }
        .tk-btn-ok { background:var(--primary); color:#fff; border:none; border-radius:7px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; }
        .tk-btn-ok:hover { background:var(--primary-dark); }
        .tk-close { position:absolute; top:12px; right:14px; background:none; border:none; font-size:18px; cursor:pointer; color:#888; }
        .tk-created { display:none; text-align:center; padding:12px 0 4px; }
        .tk-created .tk-num { font-size:56px; font-weight:900; color:var(--primary); line-height:1; }
        .tk-created .tk-msg { font-size:13px; color:#555; margin-top:6px; }

        @media (max-width: 768px) {
            main { width: min(100vw - 24px, 1180px); padding-top: 20px; }
            .guichet-bottom { grid-template-columns:1fr; }
            .action-buttons .btn-action { justify-content: center; width: 100%; }
            .queue-item { align-items: flex-start; flex-wrap: wrap; }
            .queue-item .q-time { margin-left: 0; width: 100%; padding-left: 60px; }
        }
    </style>
</head>
<body>
<main>
    <div class="page-header">
        <h1><i class="fas fa-desktop" style="color:#4B49AC"></i>Guichet</h1>
    </div>

    <div class="guichet-header-card">
        <div class="guichet-selects">
            <select id="sel-guichet" onchange="onGuichetChange()">
                <option value="">-- Sélectionner un guichet --</option>
            </select>
        </div>
        <div id="service-badge-display" class="service-badge" style="display:none"></div>
        <div class="client-en-cours" id="client-en-cours">Client en cours : &mdash;</div>
        <div class="client-attente-msg" id="attente-msg">Aucun client en attente</div>
        <div class="ws-status"><span class="ws-dot" id="ws-dot"></span><span id="ws-label">Non connecté</span></div>
    </div>

    <div id="guichet-alert" class="guichet-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <span id="alert-msg">Veuillez sélectionner un guichet avant d'utiliser les boutons.</span>
    </div>

    <div class="action-buttons">
        <button id="btn-suivant" class="btn-action btn-suivant" onclick="actionSuivant()" disabled><i class="fas fa-bell"></i> Suivant</button>
        <button id="btn-rappel" class="btn-action btn-rappel" onclick="actionRappel()" disabled><i class="fas fa-redo"></i> Rappel</button>
        <button id="btn-termine" class="btn-action btn-termine" onclick="actionTermine()" disabled><i class="fas fa-check-square"></i> Terminé</button>
        <button id="btn-incomplet" class="btn-action btn-incomplet" onclick="actionIncomplet()" disabled><i class="fas fa-exclamation"></i> Incomplet</button>
        <button id="btn-absent" class="btn-action btn-absent" onclick="actionAbsent()" disabled><i class="fas fa-times-circle"></i> Absent</button>
    </div>

    <div class="guichet-bottom">
        <div class="queue-card">
            <div class="queue-title" id="queue-title">CLIENT(S) EN ATTENTE : 0</div>
            <div class="guichet-tabs">
                <button class="tab-item active" onclick="switchTab(event, 'tab-client')">Client</button>
                <button class="tab-item" onclick="switchTab(event, 'tab-personnel')">Personnel</button>
                <button class="tab-item" onclick="switchTab(event, 'tab-rapports')">Rapports</button>
            </div>
            <div id="tab-client" class="tab-pane active">
                <ul class="queue-list" id="queue-list"><li><p class="tab-empty-msg">Aucun client en attente</p></li></ul>
                <p class="ticket-info">Pour les clients qui ne peuvent pas scanner :</p>
                <button class="btn-ticket" id="btn-ticket" onclick="openTicketModal()" disabled>TICKET</button>
            </div>
            <div id="tab-personnel" class="tab-pane"><p class="tab-empty-msg">Aucun personnel en attente.</p></div>
            <div id="tab-rapports" class="tab-pane"><p class="tab-empty-msg">Aucun rapport disponible.</p></div>
        </div>
        <div class="guide-card">
            <div class="guide-title">Guide d'utilisation</div>
            <ul>
                <li><span><strong>Suivant</strong> : appeler le prochain client</span></li>
                <li><span><strong>Rappel</strong> : rappeler le client en cours</span></li>
                <li><span><strong>Incomplet</strong> : dossier incomplet</span></li>
                <li><span><strong>Terminé</strong> : traitement terminé</span></li>
                <li><span><strong>Absent</strong> : client absent</span></li>
            </ul>
        </div>
    </div>

    <div id="tk-overlay" class="tk-overlay" onclick="if (event.target === this) closeTkModal()">
        <div class="tk-modal">
            <button class="tk-close" onclick="closeTkModal()">&times;</button>
            <div id="tk-form-section">
                <h5><i class="fas fa-ticket-alt" style="color:#4B49AC"></i>Créer un ticket</h5>
                <label>Service</label><select id="tk-service-sel"></select>
                <label>Nom du client <span style="font-weight:400;color:#aaa">(optionnel)</span></label>
                <input type="text" id="tk-nom-client" placeholder="ex: DIOP Mamadou">
                <div class="tk-modal-footer">
                    <button class="tk-btn-cancel" onclick="closeTkModal()">Annuler</button>
                    <button class="tk-btn-ok" onclick="submitManualTicket()"><i class="fas fa-plus"></i>Créer</button>
                </div>
            </div>
            <div class="tk-created" id="tk-created-section">
                <h5 style="margin-bottom:4px">Ticket créé !</h5>
                <div class="tk-num" id="tk-created-num">—</div>
                <div class="tk-msg" id="tk-created-msg"></div>
                <div class="tk-modal-footer" style="justify-content:center;margin-top:16px">
                    <button class="tk-btn-ok" onclick="closeTkModal()">Fermer</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sockjs-client@1/dist/sockjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/stompjs@2.3.3/lib/stomp.min.js"></script>
<script>
    let _guichetId = null, _serviceId = null, _currentTicket = null, _stompClient = null, _queueSub = null, _alertTimer = null;

    (async function init() {
        const res = await fetch('/gfa/api/guichets');
        if (!res.ok) return;
        const guichets = await res.json();
        const sel = document.getElementById('sel-guichet');
        guichets.forEach(g => {
            const o = document.createElement('option');
            o.value = g.id;
            o.textContent = g.numero;
            sel.appendChild(o);
        });
    })().catch(() => {});

    function switchTab(e, tabId) {
        document.querySelectorAll('.guichet-tabs .tab-item').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        e.currentTarget.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    async function onGuichetChange() {
        const sel = document.getElementById('sel-guichet');
        _guichetId = sel.value ? Number(sel.value) : null;
        if (_queueSub) { try { _queueSub.unsubscribe(); } catch (e) {} _queueSub = null; }
        _serviceId = null;
        _currentTicket = null;
        updateCurrentDisplay(null);
        updateQueueDisplay([]);
        setButtons(false);
        if (!_guichetId) {
            document.getElementById('service-badge-display').style.display = 'none';
            return;
        }
        const infoRes = await fetch('/gfa/api/guichet/' + _guichetId + '/info');
        if (!infoRes.ok) return;
        const info = await infoRes.json();
        _serviceId = info.serviceId;
        const badge = document.getElementById('service-badge-display');
        if (info.serviceNom) {
            badge.textContent = info.serviceNom;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
        const curRes = await fetch('/gfa/api/guichet/' + _guichetId + '/current');
        if (curRes.status === 200) {
            _currentTicket = await curRes.json();
            updateCurrentDisplay(_currentTicket);
        }
        if (_serviceId) {
            const wRes = await fetch('/gfa/api/guichet/' + _guichetId + '/waiting');
            if (wRes.ok) updateQueueDisplay(await wRes.json());
        }
        setButtons(true);
        if (_stompClient && _stompClient.connected && _serviceId) subscribeQueue();
    }

    function connectWS() {
        const sock = new SockJS('/ws');
        _stompClient = Stomp.over(sock);
        _stompClient.debug = null;
        _stompClient.connect({}, function () {
            document.getElementById('ws-dot').classList.add('connected');
            document.getElementById('ws-label').textContent = 'Connecté';
            if (_serviceId) subscribeQueue();
        }, function () {
            document.getElementById('ws-dot').classList.remove('connected');
            document.getElementById('ws-label').textContent = 'Déconnecté - reconnexion...';
            setTimeout(connectWS, 3000);
        });
    }

    function subscribeQueue() {
        if (!_serviceId || !_stompClient || !_stompClient.connected) return;
        if (_queueSub) { try { _queueSub.unsubscribe(); } catch (e) {} }
        _queueSub = _stompClient.subscribe('/topic/service/' + _serviceId + '/queue', function (msg) {
            updateQueueDisplay(JSON.parse(msg.body));
        });
    }

    connectWS();

    function updateCurrentDisplay(ticket) {
        const el = document.getElementById('client-en-cours');
        const msg = document.getElementById('attente-msg');
        if (ticket) {
            el.textContent = 'Client en cours : ' + ticket.numero;
            el.classList.add('active');
            msg.textContent = ticket.nomClient || '';
        } else {
            el.innerHTML = 'Client en cours : &mdash;';
            el.classList.remove('active');
            msg.textContent = 'Aucun client en attente';
        }
        const hasTicket = !!ticket;
        document.getElementById('btn-rappel').disabled = !hasTicket || !_guichetId;
        document.getElementById('btn-termine').disabled = !hasTicket || !_guichetId;
        document.getElementById('btn-incomplet').disabled = !hasTicket || !_guichetId;
        document.getElementById('btn-absent').disabled = !hasTicket || !_guichetId;
    }

    function updateQueueDisplay(queue) {
        const count = Array.isArray(queue) ? queue.length : 0;
        document.getElementById('queue-title').textContent = 'CLIENT(S) EN ATTENTE : ' + count;
        document.getElementById('attente-msg').textContent = _currentTicket ? (_currentTicket.nomClient || '') : (count > 0 ? count + ' client(s) en attente' : 'Aucun client en attente');
        const list = document.getElementById('queue-list');
        if (!count) {
            list.innerHTML = '<li><p class="tab-empty-msg">Aucun client en attente</p></li>';
            return;
        }
        list.innerHTML = queue.map(t => `<li class="queue-item"><span class="q-num">${t.numero}</span><span>${t.nomClient || '—'}</span><span class="q-time">${t.createdAt ? new Date(t.createdAt).toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' }) : ''}</span></li>`).join('');
    }

    function setButtons(enabled) {
        document.getElementById('btn-suivant').disabled = !enabled;
        document.getElementById('btn-ticket').disabled = !enabled;
        const hasTicket = enabled && !!_currentTicket;
        document.getElementById('btn-rappel').disabled = !hasTicket;
        document.getElementById('btn-termine').disabled = !hasTicket;
        document.getElementById('btn-incomplet').disabled = !hasTicket;
        document.getElementById('btn-absent').disabled = !hasTicket;
    }

    function showAlert(msg) {
        const el = document.getElementById('guichet-alert');
        document.getElementById('alert-msg').textContent = msg;
        el.classList.add('show');
        clearTimeout(_alertTimer);
        _alertTimer = setTimeout(() => el.classList.remove('show'), 3500);
    }

    async function actionSuivant() {
        if (!_guichetId) { showAlert('Veuillez sélectionner un guichet.'); return; }
        try {
            const res = await fetch('/gfa/api/guichet/call-next', { method:'POST', headers:{ 'Content-Type':'application/json' }, body:JSON.stringify({ guichetId:_guichetId }) });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                showAlert(err.error || 'Aucun ticket en attente.');
                return;
            }
            _currentTicket = await res.json();
            updateCurrentDisplay(_currentTicket);
        } catch (e) {
            showAlert('Erreur de connexion.');
        }
    }

    async function actionRappel() {
        if (!_currentTicket) { showAlert('Aucun client en cours.'); return; }
        try {
            await fetch('/gfa/api/guichet/recall', { method:'POST', headers:{ 'Content-Type':'application/json' }, body:JSON.stringify({ ticketId:_currentTicket.id }) });
        } catch (e) {
            showAlert('Erreur de connexion.');
        }
    }

    async function actionTicketStatus(endpoint) {
        if (!_currentTicket) { showAlert('Aucun client en cours.'); return; }
        try {
            const res = await fetch('/gfa/api/guichet/ticket/' + _currentTicket.id + '/' + endpoint, { method:'PATCH' });
            if (!res.ok) {
                showAlert('Erreur lors de la mise à jour.');
                return;
            }
            _currentTicket = null;
            updateCurrentDisplay(null);
            if (_guichetId) {
                const wRes = await fetch('/gfa/api/guichet/' + _guichetId + '/waiting');
                if (wRes.ok) updateQueueDisplay(await wRes.json());
            }
        } catch (e) {
            showAlert('Erreur de connexion.');
        }
    }

    function actionTermine() { actionTicketStatus('termine'); }
    function actionIncomplet() { actionTicketStatus('incomplet'); }
    function actionAbsent() { actionTicketStatus('absent'); }

    async function openTicketModal() {
        try {
            const res = await fetch('/gfa/api/scan-token');
            if (!res.ok) { showAlert('Impossible de générer le lien ticket.'); return; }
            const data = await res.json();
            window.open('/gfa/ticket?token=' + encodeURIComponent(data.token), '_blank');
        } catch (e) {
            showAlert('Erreur de connexion.');
        }
    }

    function closeTkModal() {
        document.getElementById('tk-overlay').classList.remove('open');
    }

    async function submitManualTicket() {
        const serviceId = document.getElementById('tk-service-sel').value;
        const nomClient = document.getElementById('tk-nom-client').value.trim();
        if (!serviceId) { alert('Veuillez sélectionner un service.'); return; }
        try {
            const body = { serviceId: Number(serviceId) };
            if (nomClient) body.nomClient = nomClient;
            const res = await fetch('/gfa/api/tickets', { method:'POST', headers:{ 'Content-Type':'application/json' }, body:JSON.stringify(body) });
            if (!res.ok) { alert('Impossible de créer le ticket.'); return; }
            const t = await res.json();
            document.getElementById('tk-form-section').style.display = 'none';
            document.getElementById('tk-created-num').textContent = t.numero;
            document.getElementById('tk-created-msg').textContent = nomClient ? 'Client : ' + nomClient : '';
            document.getElementById('tk-created-section').style.display = 'block';
        } catch (e) {
            alert('Erreur de connexion.');
        }
    }
</script>
</body>
</html>
