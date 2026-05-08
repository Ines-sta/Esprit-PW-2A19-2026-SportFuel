<?php
session_start();
if (!isset($_SESSION['user_email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.html');
    exit;
}
require_once __DIR__ . '/../controller/config.php';
require_once __DIR__ . '/../model/Utilisateur.php';

$stats = Utilisateur::getStats($pdo);
$utilisateurs = Utilisateur::getAll($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Backoffice Admin — SportFuel</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
</head>
<body>

  <!-- MAIN -->

  <div class="main">
    <div class="topbar">
      <div class="page-title">
        <h2>👥 Gestion des utilisateurs</h2>
        <p>Administration — Panneau de contrôle</p>
      </div>
      <div class="topbar-actions">
        <a class="btn btn-outline" href="/SportFuel-Module1/view/index.html" style="text-decoration:none;">🏠 Accueil</a>
        <a class="btn btn-outline" href="/SportFuel-Module1/controller/AuthController.php?action=logout" style="text-decoration:none; color:#dc2626; border-color:#fee2e2; background:#fef2f2;">🚪 Déconnexion</a>
        <div class="notif-btn">🔔<div class="notif-dot"></div></div>
        <button class="btn btn-outline">📤 Exporter</button>
        <button class="btn btn-primary" onclick="openModal(false)">➕ Ajouter un utilisateur</button>
      </div>
    </div>

    <div class="content">
      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-value"><?= $stats['total'] ?></div>
          <div class="stat-label">Utilisateurs totaux</div>
          <div class="stat-change up">Membres globaux</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🏃</div>
          <div class="stat-value"><?= $stats['sportifs'] ?></div>
          <div class="stat-label">Sportifs inscrits</div>
          <div class="stat-change stable">Communauté active</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🏋️</div>
          <div class="stat-value"><?= $stats['coachs'] ?></div>
          <div class="stat-label">Coachs inscrits</div>
          <div class="stat-change up">Professionnels</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⚠️</div>
          <div class="stat-value"><?= $stats['inactifs'] ?></div>
          <div class="stat-label">Comptes inactifs</div>
          <div class="stat-change stable">À relancer</div>
        </div>
      </div>

      <!-- TABLE -->
      <div class="table-section">
        <div class="table-header">
          <h3>Liste des utilisateurs</h3>
          <div class="table-controls">
            <div class="search-box">
              🔍 <input type="text" id="searchInput" placeholder="Rechercher...">
            </div>
            <select class="filter-select" id="roleFilter">
              <option value="">Tous les rôles</option>
              <option>Sportif</option>
              <option>Coach</option>
              <option>Admin</option>
            </select>
            <select class="filter-select" id="statusFilter">
              <option value="">Tous les statuts</option>
              <option>Actif</option>
              <option>Inactif</option>
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
            <?php foreach($utilisateurs as $u): 
                $roleBadge = 'badge-sportif'; $roleIcon = '🏃';
                if ($u->getRole() === 'Coach') { $roleBadge = 'badge-coach'; $roleIcon = '🏋️'; }
                elseif ($u->getRole() === 'Admin') { $roleBadge = 'badge-admin'; $roleIcon = '⭐'; }

                $statutBadge = $u->getStatut() === 'Actif' ? 'badge-actif' : 'badge-inactif';
                $statutText = $u->getStatut() === 'Actif' ? '● Actif' : '● Inactif';
                
                $initial = strtoupper(substr($u->getNom(), 0, 1));
                $dateStr = !empty($u->date_inscription) ? date('d M Y', strtotime($u->date_inscription)) : 'Inconnue';
            ?>
            <tr data-id="<?= htmlspecialchars($u->getId(), ENT_QUOTES) ?>"
                data-nom="<?= htmlspecialchars($u->getNom(), ENT_QUOTES) ?>"
                data-email="<?= htmlspecialchars($u->getEmail(), ENT_QUOTES) ?>"
                data-sport="<?= htmlspecialchars($u->getSport(), ENT_QUOTES) ?>"
                data-role="<?= htmlspecialchars($u->getRole(), ENT_QUOTES) ?>"
                data-age="<?= htmlspecialchars($u->getAge(), ENT_QUOTES) ?>"
                data-statut="<?= htmlspecialchars($u->getStatut(), ENT_QUOTES) ?>">
              <td><div class="user-cell"><div class="user-avatar" style="background:linear-gradient(135deg,#52b788,#2d6a4f)"><?= $initial ?></div><div><div class="user-name"><?= htmlspecialchars($u->getNom(), ENT_QUOTES) ?></div><div class="user-email"><?= htmlspecialchars($u->getEmail(), ENT_QUOTES) ?></div></div></div></td>
              <td><?= htmlspecialchars($u->getSport(), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars($u->getObjectif(), ENT_QUOTES) ?></td>
              <td><span class="badge <?= $roleBadge ?>"><?= $roleIcon ?> <?= htmlspecialchars($u->getRole(), ENT_QUOTES) ?></span></td>
              <td><span class="badge <?= $statutBadge ?>"><?= $statutText ?></span></td>
              <td><?= $dateStr ?></td>
              <td>
                <div class="actions">
                  <button class="action-btn edit" title="Modifier">✏️</button>
                  <button class="action-btn delete" title="Supprimer">🗑️</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="pagination">
          <div class="pagination-info">Affichage de 1 à 6 sur 2 847 utilisateurs</div>
          <div class="pagination-btns">
            <button class="page-btn">‹</button>
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">...</button>
            <button class="page-btn">475</button>
            <button class="page-btn">›</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  

  <div class="modal-overlay" id="modal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-title">➕ Ajouter un utilisateur</div>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <div class="modal-grid">
          <div class="modal-group full"><div class="modal-label">Nom Complet</div><input class="modal-input" id="modNom" type="text" placeholder="Nom Complet"></div>
          <div class="modal-group full"><div class="modal-label">Email</div><input class="modal-input" id="modEmail" type="text" placeholder="email@exemple.com"></div>
          <div class="modal-group"><div class="modal-label">Mot de passe</div><input class="modal-input" id="modPass" type="password" placeholder="••••••••"></div>
          <div class="modal-group"><div class="modal-label">Rôle</div><select class="modal-select" id="modRole"><option>Sportif</option><option>Coach</option><option>Admin</option></select></div>
          <div class="modal-group"><div class="modal-label">Statut</div><select class="modal-select" id="modStatut"><option>Actif</option><option>Inactif</option></select></div>
          <div class="modal-group"><div class="modal-label">Âge</div><input class="modal-input" id="modAge" type="text" placeholder="25"></div>
          <div class="modal-group"><div class="modal-label">Sport</div><select class="modal-select" id="modSport"><option>Marathon</option><option>Musculation</option><option>Yoga</option><option>Natation</option><option>Cyclisme</option></select></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal()">Annuler</button>
        <button class="btn btn-primary">Créer l'utilisateur</button>
      </div>
    </div>
  </div>

  <script src="admin.js"></script>

</body>
</html>
