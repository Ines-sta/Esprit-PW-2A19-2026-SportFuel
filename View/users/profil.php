<?php
require_once __DIR__ . '/../partials/avatar.php';

if (!isset($viewData) || !is_array($viewData)) {
  require_once __DIR__ . '/../../Controller/users/ProfilePageController.php';
  $profilePageController = new ProfilePageController();
  $viewData = $profilePageController->getViewData();
}

$user = $viewData['user'];
$imc = $viewData['imc'];
$photoProfilUrl = $viewData['photoProfilUrl'];
$initial = $viewData['initial'];
$roleLabel = $viewData['roleLabel'];
$isBackofficeRole = $viewData['isBackofficeRole'];
$isCoachRole = $viewData['isCoachRole'];
$isAdminRole = $viewData['isAdminRole'];
$isSportifRole = $viewData['isSportifRole'];
$coachStats = $viewData['coachStats'];
$adminStats = $viewData['adminStats'];
$sportifActivities = $viewData['sportifActivities'];
$imcMax = $viewData['imcMax'] ?? 30;
$imcProgress = $viewData['imcProgress'] ?? 0;
$objectifPoidsLabel = $viewData['objectifPoidsLabel'] ?? 'Objectif non défini';
$objectifPoidsProgress = $viewData['objectifPoidsProgress'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Profil — SportFuel</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/style.css">
  <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/View/users/assets/profil.css">
</head>
<body>
  <?php if ($isBackofficeRole): ?>
  <div class="app-layout">
    <?php include __DIR__ . '/../partials/backoffice_sidebar.php'; ?>
    <div class="main-content profile-main-content">
      <div class="page-header">
        <h1>Mon Profil</h1>
        <div class="page-date">Tableau personnel SportFuel</div>
      </div>
  <?php else: ?>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div class="main-content profile-main-content">
      <div class="page-header">
        <h1>Mon Profil</h1>
        <div class="page-date">Espace personnel SportFuel</div>
      </div>
  <?php endif; ?>
      <div class="profile-page">
    <div class="profile-content">
      <input type="hidden" id="profileRole" value="<?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?>">
      <div class="page-actions">
        <a class="btn btn-outline btn-logout" href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=auth&action=logout">Déconnexion</a>
        <button class="btn btn-outline" onclick="toggleEdit()">✏️ Modifier</button>
        <button class="btn btn-primary" onclick="saveProfile()">💾 Enregistrer</button>
      </div>

      <div class="profile-hero">
        <div class="avatar-wrap">
          <div class="avatar" id="profileAvatar" data-initial="<?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($photoProfilUrl !== ''): ?>
              <img src="<?= htmlspecialchars($photoProfilUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Photo de profil de <?= htmlspecialchars($user->getNom(), ENT_QUOTES, 'UTF-8') ?>" id="profileAvatarImg">
            <?php else: ?>
              <span id="profileAvatarFallback"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </div>
          <label class="avatar-edit" for="profilePhotoInput" title="Modifier la photo">📷</label>
          <input type="file" id="profilePhotoInput" accept="image/*" class="profile-photo-input-hidden">
        </div>
        <div class="hero-info">
          <div class="hero-name"><?= htmlspecialchars($user->getNom()) ?></div>
          <div class="hero-email"><?= htmlspecialchars($user->getEmail()) ?></div>
          <div class="hero-tags">
            <?php if ($isSportifRole): ?>
              <div class="hero-tag">🏃 <?= htmlspecialchars($user->getSport() ?: 'Sportif') ?></div>
              <div class="hero-tag orange">🎯 <?= htmlspecialchars($user->getObjectif() ?: 'Objectif') ?></div>
              <div class="hero-tag">⭐ <?= htmlspecialchars($user->getNiveau() ?: 'Niveau') ?></div>
            <?php elseif ($isCoachRole): ?>
              <div class="hero-tag">🧭 Coach</div>
              <div class="hero-tag orange">👥 <?= (int)$coachStats['assigned_athletes'] ?> athlètes suivis</div>
              <div class="hero-tag">📌 <?= (int)$coachStats['pending_publications'] ?> demandes en attente</div>
            <?php else: ?>
              <div class="hero-tag">🛡️ Admin</div>
              <div class="hero-tag orange">👥 <?= (int)$adminStats['total_users'] ?> utilisateurs</div>
              <div class="hero-tag">📌 <?= (int)$adminStats['pending_publications'] ?> publications en attente</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="hero-stats">
          <?php if ($isSportifRole): ?>
              <div class="hero-stat-label">adherence globale</div>
              <div class="hero-stat-val"><?= htmlspecialchars($user->getPoids()) ?></div>
              <div class="hero-stat-label">kg</div>
            </div>
            <div class="hero-stat">
              <div class="hero-stat-val"><?= htmlspecialchars($user->getTaille()) ?></div>
              <div class="hero-stat-label">cm</div>
            </div>
            <div class="hero-stat">
              <div class="hero-stat-val"><?= htmlspecialchars($user->getAge()) ?></div>
              <div class="hero-stat-label">ans</div>
            </div>
          <?php elseif ($isCoachRole): ?>
            <div class="hero-stat">
              <div class="hero-stat-val"><?= (int)$coachStats['assigned_athletes'] ?></div>
              <div class="hero-stat-label">athlètes</div>
            </div>
            <div class="hero-stat">
              <div class="hero-stat-val"><?= (int)$coachStats['recent_workouts'] ?></div>
              <div class="hero-stat-label">séances 7j</div>
            </div>
            <div class="hero-stat">
              <div class="hero-stat-val"><?= (int)$coachStats['completion_rate'] ?>%</div>
              <div class="hero-stat-label">adherence globale</div>
            </div>
          <?php else: ?>
            <div class="hero-stat">
              <div class="hero-stat-val"><?= (int)$adminStats['total_users'] ?></div>
              <div class="hero-stat-label">utilisateurs</div>
            </div>
            <div class="hero-stat">
              <div class="hero-stat-val"><?= (int)$adminStats['coach_count'] ?></div>
              <div class="hero-stat-label">coaches</div>
            </div>
            <div class="hero-stat">
              <div class="hero-stat-val"><?= (int)$adminStats['sportif_count'] ?></div>
              <div class="hero-stat-label">sportifs</div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="profile-grid">
        <div class="card">
          <div class="card-header">
            <div class="card-title">👤 Informations personnelles</div>
            <a class="edit-link" onclick="toggleEdit()">Modifier</a>
          </div>
          <div class="card-body">
            <div class="field-group">
              <div class="field-row">
                <div class="field" style="flex: 1;">
                  <div class="field-label">Nom complet</div>
                  <input class="field-input" type="text" value="<?= htmlspecialchars($user->getNom()) ?>" id="nom" data-editable="1" disabled
                         pattern="[A-Za-zÀ-ÿ\s]+" title="Lettres et espaces uniquement">
                </div>
                <div class="field" style="flex: 1;">
                  <div class="field-label">Email</div>
                  <input class="field-input" type="email" value="<?= htmlspecialchars($user->getEmail()) ?>" id="email" disabled>
                </div>
              </div>
              <div class="field">
                <div class="field-label">Mot de passe</div>
                <input class="field-input" type="password" value="••••••••" id="mdp" disabled>
              </div>
            </div>
          </div>
        </div>

        <?php if ($isSportifRole): ?>
        <div class="card">
          <div class="card-header">
            <div class="card-title">⚖️ Données physiques</div>
          </div>
          <div class="card-body">
            <div class="stat-pills">
              <div class="stat-pill">
                <div class="pill-val"><?= htmlspecialchars($user->getAge()) ?></div>
                <div class="pill-unit">ans</div>
                <div class="pill-label">Âge</div>
              </div>
              <div class="stat-pill">
                <div class="pill-val"><?= htmlspecialchars($user->getPoids()) ?></div>
                <div class="pill-unit">kg</div>
                <div class="pill-label">Poids</div>
              </div>
              <div class="stat-pill">
                <div class="pill-val"><?= htmlspecialchars($user->getTaille()) ?></div>
                <div class="pill-unit">cm</div>
                <div class="pill-label">Taille</div>
              </div>
              <div class="stat-pill">
                <div class="pill-val"><?= htmlspecialchars($imc) ?></div>
                <div class="pill-unit">IMC</div>
                <div class="pill-label">Indicatif</div>
              </div>
            </div>
            <div style="margin-top: 20px;">
              <div class="progress-wrap">
                <div class="progress-label"><span>IMC</span><span><?= $imc > 0 ? htmlspecialchars((string)$imc) : '—' ?> / <?= htmlspecialchars((string)$imcMax) ?></span></div>
                <div class="progress-bar"><div class="progress-fill" style="width: <?= htmlspecialchars((string)$imcProgress) ?>%"></div></div>
              </div>
              <div class="progress-wrap" style="margin-top:12px">
                <div class="progress-label"><span>Objectif poids</span><span><?= htmlspecialchars((string)$objectifPoidsLabel, ENT_QUOTES, 'UTF-8') ?></span></div>
                <div class="progress-bar"><div class="progress-fill orange" style="width: <?= htmlspecialchars((string)$objectifPoidsProgress) ?>%"></div></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title">🏅 Profil sportif</div>
            <a class="edit-link" onclick="toggleEdit()">Modifier</a>
          </div>
          <div class="card-body">
            <div class="field-group">
              <div class="field">
                <div class="field-label">Sport pratiqué</div>
                <div class="sport-tags" id="sportContainer">
                  <div class="sport-tag <?= $user->getSport() == 'Marathon' ? 'active' : '' ?>" data-value="Marathon">🏃 Marathon</div>
                  <div class="sport-tag <?= $user->getSport() == 'Musculation' ? 'active' : '' ?>" data-value="Musculation">💪 Musculation</div>
                  <div class="sport-tag <?= $user->getSport() == 'Yoga' ? 'active' : '' ?>" data-value="Yoga">🧘 Yoga</div>
                  <div class="sport-tag <?= $user->getSport() == 'Natation' ? 'active' : '' ?>" data-value="Natation">🏊 Natation</div>
                  <div class="sport-tag <?= $user->getSport() == 'Cyclisme' ? 'active' : '' ?>" data-value="Cyclisme">🚴 Cyclisme</div>
                </div>
                <input type="hidden" id="sport" value="<?= htmlspecialchars($user->getSport() ? $user->getSport() : 'Marathon') ?>">
              </div>
              <div class="field">
                <div class="field-label">Objectif</div>
                <select class="field-select" id="objectif" data-editable="1" disabled>
                  <option <?= $user->getObjectif() == 'Performance' ? 'selected' : '' ?>>Performance</option>
                  <option <?= $user->getObjectif() == 'Prise de masse' ? 'selected' : '' ?>>Prise de masse</option>
                  <option <?= $user->getObjectif() == 'Perte de poids' ? 'selected' : '' ?>>Perte de poids</option>
                  <option <?= $user->getObjectif() == 'Endurance' ? 'selected' : '' ?>>Endurance</option>
                  <option <?= $user->getObjectif() == 'Légèreté' ? 'selected' : '' ?>>Légèreté</option>
                </select>
              </div>
              <div class="field-row">
                <div class="field">
                  <div class="field-label">Niveau</div>
                  <select class="field-select" id="niveau" data-editable="1" disabled>
                    <option <?= $user->getNiveau() == 'Avancé' ? 'selected' : '' ?>>Avancé</option>
                    <option <?= $user->getNiveau() == 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                    <option <?= $user->getNiveau() == 'Débutant' ? 'selected' : '' ?>>Débutant</option>
                  </select>
                </div>
                <div class="field">
                  <div class="field-label">Séances/sem</div>
                  <input class="field-input" type="text" id="frequence" data-editable="1" value="<?= htmlspecialchars($user->getFrequence() > 0 ? $user->getFrequence() : 5) ?>" disabled
                         inputmode="numeric" pattern="[0-9]*">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title">📈 Activité récente</div>
          </div>
          <div class="card-body">
            <?php if (!empty($sportifActivities)): ?>
            <div class="field-group">
              <?php foreach ($sportifActivities as $activity): ?>
              <div style="display:flex; align-items:center; gap:14px; padding:12px; background:var(--cream); border-radius:12px;">
                <div style="font-size:28px;"><?php echo htmlspecialchars((string)($activity['icon'] ?? '📌'), ENT_QUOTES, 'UTF-8'); ?></div>
                <div>
                  <div style="font-size:14px; font-weight:600;"><?php echo htmlspecialchars(((string)($activity['label'] ?? 'Activité')) . ' · ' . ((string)($activity['title'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></div>
                  <div style="font-size:12px; color:var(--muted);"><?php echo htmlspecialchars((string)($activity['detail'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div style="margin-left:auto; font-size:12px; color:var(--muted);"><?php echo htmlspecialchars((string)($activity['relative_day'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php else: ?>
              <p class="muted-note">Aucune activité récente pour le moment.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php elseif ($isCoachRole): ?>
        <div class="card">
          <div class="card-header">
            <div class="card-title">📊 Vue Coach</div>
          </div>
          <div class="card-body">
            <div class="metric-grid">
              <div class="metric-card"><strong><?= (int)$coachStats['assigned_athletes'] ?></strong><span>Athlètes assignés</span></div>
              <div class="metric-card"><strong><?= (int)$coachStats['pending_publications'] ?></strong><span>Demandes en attente</span></div>
              <div class="metric-card"><strong><?= (int)$coachStats['recent_workouts'] ?></strong><span>Séances sur 7 jours</span></div>
              <div class="metric-card"><strong><?= (int)$coachStats['completion_rate'] ?>%</strong><span>Taux de complétion</span></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title">👥 Athlètes récents</div>
          </div>
          <div class="card-body">
            <?php if (!empty($coachStats['athletes'])): ?>
              <ul class="list-card">
                <?php foreach ($coachStats['athletes'] as $ath): ?>
                  <li>
                    <strong><?= htmlspecialchars((string)($ath['nom'] ?? 'Athlète')) ?></strong>
                    <span><?= htmlspecialchars((string)($ath['sport_pratique'] ?? 'Sport')) ?> · <?= htmlspecialchars((string)($ath['niveau'] ?? 'Niveau')) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="muted-note">Aucun athlète assigné pour le moment.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php elseif ($isAdminRole): ?>
        <div class="card">
          <div class="card-header">
            <div class="card-title">🧩 Vue Administrateur</div>
          </div>
          <div class="card-body">
            <div class="metric-grid">
              <div class="metric-card"><strong><?= (int)$adminStats['total_users'] ?></strong><span>Utilisateurs</span></div>
              <div class="metric-card"><strong><?= (int)$adminStats['coach_count'] ?></strong><span>Coaches</span></div>
              <div class="metric-card"><strong><?= (int)$adminStats['sportif_count'] ?></strong><span>Sportifs</span></div>
              <div class="metric-card"><strong><?= (int)$adminStats['active_this_month'] ?></strong><span>Actifs ce mois</span></div>
              <div class="metric-card"><strong><?= (int)$adminStats['pending_publications'] ?></strong><span>Publications en attente</span></div>
              <div class="metric-card"><strong><?= (int)$adminStats['assignments_count'] ?></strong><span>Assignations coach-sportif</span></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title">🗂️ Derniers utilisateurs</div>
          </div>
          <div class="card-body">
            <?php if (!empty($adminStats['recent_users'])): ?>
              <ul class="list-card">
                <?php foreach ($adminStats['recent_users'] as $recentUser): ?>
                  <li>
                    <strong><?= htmlspecialchars((string)($recentUser['nom'] ?? 'Utilisateur')) ?></strong>
                    <span><?= htmlspecialchars((string)($recentUser['email'] ?? '')) ?> · <?= htmlspecialchars((string)($recentUser['role'] ?? '')) ?> · <?= htmlspecialchars((string)($recentUser['statut'] ?? '')) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="muted-note">Aucune donnée utilisateur récente.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <div class="danger-zone">
        <div class="danger-text">
          <h4>⚠️ Supprimer mon compte</h4>
          <p>Cette action est irréversible. Toutes vos données seront définitivement supprimées.</p>
        </div>
        <button class="btn btn-danger" onclick="deleteAccount()">Supprimer mon compte</button>
      </div>
    </div>
  </div>
  </div>
  </div>

  <div class="save-bar" id="saveBar">
    <span>✏️ Modifications non sauvegardées</span>
    <button class="btn-save" onclick="saveProfile()">Enregistrer</button>
  </div>

  <script src="/Esprit-PW-2A19-2026-SportFuel-main/View/users/assets/profil.js"></script>
</body>
</html>
