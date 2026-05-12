<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: /Esprit-PW-2A19-2026-SportFuel-main/View/auth/connexion.html');
    exit;
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/users/Utilisateur.php';
require_once __DIR__ . '/../partials/avatar.php';

$pdo = Config::getConnexion();
$stats = Utilisateur::getStats($pdo);
$utilisateurs = Utilisateur::getAll($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel Admin — Gestion des utilisateurs</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/style.css">
    <style>
        .users-admin-page .main {
            width: 100%;
            display: flex;
            flex-direction: column;
            animation: usersFadeIn 0.35s ease both;
        }

        @keyframes usersFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .users-admin-page .topbar {
            background: #ffffff;
            padding: 18px 26px;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
            height: auto;
        }

        .users-admin-page .page-title h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--sb-bg);
        }

        .users-admin-page .page-title p {
            margin-top: 3px;
            color: var(--text-3);
            font-size: 13px;
        }

        .users-admin-page .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .users-admin-page .content {
            padding: 24px 26px 34px;
        }

        .users-admin-page .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .users-admin-page .table-section {
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
        }

        .users-admin-page .table-header {
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid var(--border);
            gap: 14px;
        }

        .users-admin-page .table-head-main {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .users-admin-page .table-header h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            color: var(--sb-bg);
        }

        .users-admin-page .table-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            width: 100%;
        }

        .users-admin-page .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8faf8;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 12px;
            flex: 1;
            min-width: 220px;
        }

        .users-admin-page .search-box input {
            border: none;
            background: transparent;
            outline: none;
            min-width: 0;
            width: 100%;
            color: var(--text);
            font-size: 14px;
        }

        .users-admin-page .filter-select {
            height: 37px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0 12px;
            background: #fff;
            color: var(--text);
            font-size: 13px;
        }

        .users-admin-page table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-admin-page thead tr {
            background: #f8faf8;
            border-bottom: 1px solid var(--border);
        }

        .users-admin-page th {
            padding: 13px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .users-admin-page td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            font-size: 13.5px;
            color: var(--text);
        }

        .users-admin-page tbody tr:hover {
            background: #f8fbf8;
        }

        .users-admin-page tbody tr:last-child td {
            border-bottom: none;
        }

        .users-admin-page .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .users-admin-page .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .users-admin-page .user-avatar.sf-avatar-fallback,
        .users-admin-page .user-avatar .sf-avatar-fallback {
            background: linear-gradient(135deg, #52b788, #2d6a4f);
            color: #fff;
        }

        .users-admin-page .user-name {
            font-weight: 600;
        }

        .users-admin-page .user-email {
            color: var(--text-3);
            font-size: 12px;
            margin-top: 2px;
        }

        .users-admin-page .badge-admin {
            background: #fef3c7;
            color: #b45309;
        }

        .users-admin-page .badge-sportif {
            background: #d8f3dc;
            color: #1e6b47;
        }

        .users-admin-page .actions {
            display: flex;
            gap: 6px;
        }

        .users-admin-page .action-btn {
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s ease;
        }

        .users-admin-page .action-btn.edit {
            background: #fef3c7;
        }

        .users-admin-page .action-btn.delete {
            background: #fee2e2;
        }

        .users-admin-page .action-btn:hover {
            transform: scale(1.08);
        }

        .users-admin-page .pagination {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
        }

        .users-admin-page .pagination-info {
            color: var(--text-3);
            font-size: 13px;
        }

        .users-admin-page .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 200;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
        }

        .users-admin-page .modal-overlay.open {
            display: flex;
        }

        .users-admin-page .modal {
            background: #fff;
            border-radius: 18px;
            width: min(560px, 92vw);
            overflow: hidden;
        }

        .users-admin-page .modal-header {
            background: var(--sb-bg);
            color: #fff;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .users-admin-page .modal-title {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
        }

        .users-admin-page .modal-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 18px;
        }

        .users-admin-page .modal-body {
            padding: 24px;
        }

        .users-admin-page .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .users-admin-page .modal-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .users-admin-page .modal-group.full {
            grid-column: 1 / -1;
        }

        .users-admin-page .modal-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .users-admin-page .modal-input,
        .users-admin-page .modal-select {
            width: 100%;
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0 12px;
            font-size: 14px;
            color: var(--text);
            background: #fff;
        }

        .users-admin-page .modal-footer {
            border-top: 1px solid var(--border);
            padding: 14px 24px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        @media (max-width: 980px) {
            .users-admin-page .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .users-admin-page .topbar {
                padding: 14px;
            }

            .users-admin-page .content {
                padding: 14px;
            }

            .users-admin-page .stats-grid {
                grid-template-columns: 1fr;
            }

            .users-admin-page .modal-grid {
                grid-template-columns: 1fr;
            }

            .users-admin-page .table-section {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body class="users-admin-page">
    <?php
    $sidebarActive = 'users';
    include __DIR__ . '/../partials/backoffice_sidebar.php';
    ?>

    <div class="main-area">
    <div class="main">
        <div class="topbar">
            <div class="page-title">
                <h2>Gestion des utilisateurs</h2>
            </div>
        </div>

        <div class="content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?php echo (int)$stats['total']; ?></div>
                    <div class="stat-label">Utilisateurs totaux</div>
                    <div class="stat-change up">Membres globaux</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏃</div>
                    <div class="stat-value"><?php echo (int)$stats['sportifs']; ?></div>
                    <div class="stat-label">Sportifs inscrits</div>
                    <div class="stat-change stable">Communauté active</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏋️</div>
                    <div class="stat-value"><?php echo (int)$stats['coachs']; ?></div>
                    <div class="stat-label">Coaches inscrits</div>
                    <div class="stat-change up">Professionnels</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-value"><?php echo (int)$stats['inactifs']; ?></div>
                    <div class="stat-label">Comptes inactifs</div>
                    <div class="stat-change stable">À relancer</div>
                </div>
            </div>

            <div class="table-section">
                <div class="table-header">
                    <div class="table-head-main">
                        <h3>Liste des utilisateurs</h3>
                        <button class="btn btn-primary" type="button" onclick="openModal(false)">Ajouter un utilisateur</button>
                    </div>
                    <div class="table-controls">
                        <div class="search-box">
                            🔍 <input type="text" id="searchInput" placeholder="Rechercher...">
                        </div>
                        <select class="filter-select" id="roleFilter">
                            <option value="">Tous les rôles</option>
                            <option value="Sportif">Sportif</option>
                            <option value="Coach">Coach</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <select class="filter-select" id="statusFilter">
                            <option value="">Tous les statuts</option>
                            <option value="Actif">Actif</option>
                            <option value="Inactif">Inactif</option>
                        </select>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Sport</th>
                            <th>Objectif</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTable">
                        <?php foreach ($utilisateurs as $u): ?>
                            <?php
                            $roleBadge = 'badge-sportif';
                            $roleIcon = '🏃';
                            if ($u->getRole() === 'Coach') {
                                $roleBadge = 'badge-coach';
                                $roleIcon = '🏋️';
                            } elseif ($u->getRole() === 'Admin') {
                                $roleBadge = 'badge-admin';
                                $roleIcon = '⭐';
                            }
                            $statutBadge = $u->getStatut() === 'Actif' ? 'badge-actif' : 'badge-inactif';
                            $statutText = $u->getStatut() === 'Actif' ? '● Actif' : '● Inactif';
                            $photoUrl = (string)($u->getPhotoProfilUrl() ?? '');
                            $dateStr = !empty($u->date_inscription) ? date('d M Y', strtotime($u->date_inscription)) : 'Inconnue';
                            ?>
                            <tr
                                data-id="<?php echo htmlspecialchars((string)$u->getId(), ENT_QUOTES); ?>"
                                data-nom="<?php echo htmlspecialchars($u->getNom(), ENT_QUOTES); ?>"
                                data-email="<?php echo htmlspecialchars($u->getEmail(), ENT_QUOTES); ?>"
                                data-sport="<?php echo htmlspecialchars($u->getSport(), ENT_QUOTES); ?>"
                                data-role="<?php echo htmlspecialchars($u->getRole(), ENT_QUOTES); ?>"
                                data-age="<?php echo htmlspecialchars((string)$u->getAge(), ENT_QUOTES); ?>"
                                data-statut="<?php echo htmlspecialchars($u->getStatut(), ENT_QUOTES); ?>"
                                data-photo="<?php echo htmlspecialchars($photoUrl, ENT_QUOTES); ?>"
                            >
                                <td>
                                    <div class="user-cell">
                                        <?php echo sportfuel_avatar_markup($u->getNom(), $photoUrl, 'user-avatar'); ?>
                                        <div>
                                            <div class="user-name"><?php echo htmlspecialchars($u->getNom(), ENT_QUOTES); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($u->getEmail(), ENT_QUOTES); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($u->getSport(), ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($u->getObjectif(), ENT_QUOTES); ?></td>
                                <td><span class="badge <?php echo htmlspecialchars($roleBadge, ENT_QUOTES); ?>"><?php echo $roleIcon; ?> <?php echo htmlspecialchars($u->getRole(), ENT_QUOTES); ?></span></td>
                                <td><span class="badge <?php echo htmlspecialchars($statutBadge, ENT_QUOTES); ?>"><?php echo htmlspecialchars($statutText, ENT_QUOTES); ?></span></td>
                                <td><?php echo htmlspecialchars($dateStr, ENT_QUOTES); ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="action-btn edit" type="button" title="Modifier">✏️</button>
                                        <button class="action-btn delete" type="button" title="Supprimer">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <div class="pagination-info"><?php echo count($utilisateurs); ?> utilisateur(s) affiché(s)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Ajouter un utilisateur</div>
                <button class="modal-close" type="button" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="modal-grid">
                    <div class="modal-group full"><div class="modal-label">Nom complet</div><input class="modal-input" id="modNom" type="text" placeholder="Nom complet"></div>
                    <div class="modal-group full"><div class="modal-label">Email</div><input class="modal-input" id="modEmail" type="email" placeholder="email@exemple.com"></div>
                    <div class="modal-group"><div class="modal-label">Mot de passe</div><input class="modal-input" id="modPass" type="password" placeholder="••••••••"></div>
                    <div class="modal-group"><div class="modal-label">Rôle</div><select class="modal-select" id="modRole"><option value="Sportif">Sportif</option><option value="Coach">Coach</option><option value="Admin">Admin</option></select></div>
                    <div class="modal-group"><div class="modal-label">Statut</div><select class="modal-select" id="modStatut"><option value="Actif">Actif</option><option value="Inactif">Inactif</option></select></div>
                    <div class="modal-group"><div class="modal-label">Âge</div><input class="modal-input" id="modAge" type="number" min="0" max="120" placeholder="25"></div>
                    <div class="modal-group full"><div class="modal-label">Sport</div><input class="modal-input" id="modSport" type="text" placeholder="Musculation"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeModal()">Annuler</button>
                <button class="btn btn-primary" type="button" id="saveUserButton">Créer l'utilisateur</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiBase = '/Esprit-PW-2A19-2026-SportFuel-main/Controller/api/api.php';
        let editMode = false;
        let currentEditId = null;

        window.openModal = function (isEdit, userData) {
            const modal = document.getElementById('modal');
            const title = modal.querySelector('.modal-title');
            const saveButton = document.getElementById('saveUserButton');

            editMode = !!isEdit;
            currentEditId = isEdit && userData ? userData.id : null;

            title.textContent = editMode ? 'Modifier un utilisateur' : 'Ajouter un utilisateur';
            saveButton.textContent = editMode ? 'Enregistrer les modifications' : "Créer l'utilisateur";

            document.getElementById('modNom').value = userData ? (userData.nom || '') : '';
            document.getElementById('modEmail').value = userData ? (userData.email || '') : '';
            document.getElementById('modPass').value = '';
            document.getElementById('modRole').value = userData ? (userData.role || 'Sportif') : 'Sportif';
            document.getElementById('modStatut').value = userData ? (userData.statut || 'Actif') : 'Actif';
            document.getElementById('modAge').value = userData ? (userData.age || '') : '';
            document.getElementById('modSport').value = userData ? (userData.sport || '') : '';

            modal.classList.add('open');
        };

        window.closeModal = function () {
            document.getElementById('modal').classList.remove('open');
        };

        document.getElementById('modal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeModal();
            }
        });

        document.getElementById('usersTable').addEventListener('click', function (event) {
            const button = event.target.closest('.action-btn');
            if (!button) {
                return;
            }

            const row = button.closest('tr');
            const userData = {
                id: row.dataset.id,
                nom: row.dataset.nom,
                email: row.dataset.email,
                sport: row.dataset.sport,
                role: row.dataset.role,
                age: row.dataset.age,
                statut: row.dataset.statut,
            };

            if (button.classList.contains('edit')) {
                openModal(true, userData);
                return;
            }

            if (button.classList.contains('delete')) {
                if (!confirm('Supprimer définitivement ' + userData.nom + ' ?')) {
                    return;
                }

                fetch(apiBase + '?action=delete_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userData.id })
                })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    if (!payload.success) {
                        alert(payload.message || 'Suppression impossible.');
                        return;
                    }
                    row.remove();
                })
                .catch(function () {
                    alert('Erreur de connexion au serveur.');
                });
            }
        });

        document.getElementById('saveUserButton').addEventListener('click', function () {
            const payload = {
                nom: document.getElementById('modNom').value.trim(),
                email: document.getElementById('modEmail').value.trim(),
                password: document.getElementById('modPass').value,
                role: document.getElementById('modRole').value,
                statut: document.getElementById('modStatut').value,
                age: document.getElementById('modAge').value,
                sport: document.getElementById('modSport').value.trim()
            };

            if (payload.nom.length < 3) {
                alert('Le nom doit contenir au moins 3 caractères.');
                return;
            }

            if (!payload.email.includes('@')) {
                alert('Veuillez saisir un email valide.');
                return;
            }

            if (!editMode && payload.password.length < 6) {
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                return;
            }

            if (editMode) {
                payload.id = currentEditId;
            }

            fetch(apiBase + '?action=' + (editMode ? 'edit_user' : 'add_user'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (response) { return response.json(); })
            .then(function (response) {
                if (!response.success) {
                    alert(response.message || 'Action impossible.');
                    return;
                }
                window.location.reload();
            })
            .catch(function () {
                alert('Erreur de connexion au serveur.');
            });
        });

        function filterRows() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const role = document.getElementById('roleFilter').value.toLowerCase();
            const status = document.getElementById('statusFilter').value.toLowerCase();

            document.querySelectorAll('#usersTable tr').forEach(function (row) {
                const name = (row.dataset.nom || '').toLowerCase();
                const email = (row.dataset.email || '').toLowerCase();
                const rowRole = (row.dataset.role || '').toLowerCase();
                const rowStatus = (row.dataset.statut || '').toLowerCase();

                const matchesQuery = query === '' || name.includes(query) || email.includes(query);
                const matchesRole = role === '' || rowRole === role;
                const matchesStatus = status === '' || rowStatus === status;

                row.style.display = matchesQuery && matchesRole && matchesStatus ? '' : 'none';
            });
        }

        document.getElementById('searchInput').addEventListener('input', filterRows);
        document.getElementById('roleFilter').addEventListener('change', filterRows);
        document.getElementById('statusFilter').addEventListener('change', filterRows);
    });
    </script>
    </div>
    </div>
</body>
</html>