<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Utilisateur.php';

$pdo = Config::getConnexion();

if (!isset($_SESSION['user_email'])) {
    header('Location: connexion.html');
    exit();
}

$user = Utilisateur::findByEmail($pdo, $_SESSION['user_email']);
if (!$user) {
    session_destroy();
    header('Location: connexion.html');
    exit();
}
$imc = ($user->getTaille() > 0) ? round($user->getPoids() / (($user->getTaille() / 100) ** 2), 1) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Profil — SportFuel</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="profil.css">
</head>
<body>
  <div class="main">
    <div class="content">
      <div class="page-actions">
        <a class="btn btn-outline" href="/Esprit-PW-2A19-2526-SportFuel/Controller/AuthController.php?action=logout" style="text-decoration:none; color:#dc2626; border-color:#fee2e2; background:#fef2f2; margin-right: 15px;">🚪 Déconnexion</a>
        <button class="btn btn-outline" onclick="toggleEdit()">✏️ Modifier</button>
        <button class="btn btn-primary" onclick="saveProfile()">💾 Enregistrer</button>
      </div>

      <div class="profile-hero">
        <div class="avatar-wrap">
          <div class="avatar"><?= strtoupper(substr($user->getNom(), 0, 1)) ?></div>
          <div class="avatar-edit">📷</div>
        </div>
        <div class="hero-info">
          <div class="hero-name"><?= htmlspecialchars($user->getNom()) ?></div>
          <div class="hero-email"><?= htmlspecialchars($user->getEmail()) ?></div>
          <div class="hero-tags">
            <div class="hero-tag">🏃 <?= htmlspecialchars($user->getSport() ?: 'Sportif') ?></div>
            <div class="hero-tag orange">🎯 <?= htmlspecialchars($user->getObjectif() ?: 'Objectif') ?></div>
            <div class="hero-tag">⭐ <?= htmlspecialchars($user->getNiveau() ?: 'Niveau') ?></div>
          </div>
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
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
                  <input class="field-input" type="text" value="<?= htmlspecialchars($user->getNom()) ?>" id="nom" disabled
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
                <div class="progress-label"><span>IMC</span><span>23.5 / 30</span></div>
                <div class="progress-bar"><div class="progress-fill" style="width: 65%"></div></div>
              </div>
              <div class="progress-wrap" style="margin-top:12px">
                <div class="progress-label"><span>Objectif poids</span><span>65 kg cible</span></div>
                <div class="progress-bar"><div class="progress-fill orange" style="width: 78%"></div></div>
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
                <select class="field-select" id="objectif" disabled>
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
                  <select class="field-select" id="niveau" disabled>
                    <option <?= $user->getNiveau() == 'Avancé' ? 'selected' : '' ?>>Avancé</option>
                    <option <?= $user->getNiveau() == 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                    <option <?= $user->getNiveau() == 'Débutant' ? 'selected' : '' ?>>Débutant</option>
                  </select>
                </div>
                <div class="field">
                  <div class="field-label">Séances/sem</div>
                  <input class="field-input" type="text" id="frequence" value="<?= htmlspecialchars($user->getFrequence() > 0 ? $user->getFrequence() : 5) ?>" disabled
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
            <div class="field-group">
              <div style="display:flex; align-items:center; gap:14px; padding:12px; background:var(--cream); border-radius:12px;">
                <div style="font-size:28px;">🥗</div>
                <div>
                  <div style="font-size:14px; font-weight:600;">Plan alimentaire généré</div>
                  <div style="font-size:12px; color:var(--muted);">Semaine 3 · Marathon · 1 840 kcal</div>
                </div>
                <div style="margin-left:auto; font-size:12px; color:var(--muted);">Aujourd'hui</div>
              </div>
              <div style="display:flex; align-items:center; gap:14px; padding:12px; background:var(--cream); border-radius:12px;">
                <div style="font-size:28px;">🏃</div>
                <div>
                  <div style="font-size:14px; font-weight:600;">Séance enregistrée</div>
                  <div style="font-size:12px; color:var(--muted);">Course · 12 km · 580 kcal brûlées</div>
                </div>
                <div style="margin-left:auto; font-size:12px; color:var(--muted);">Hier</div>
              </div>
              <div style="display:flex; align-items:center; gap:14px; padding:12px; background:var(--cream); border-radius:12px;">
                <div style="font-size:28px;">🛒</div>
                <div>
                  <div style="font-size:14px; font-weight:600;">Liste de courses générée</div>
                  <div style="font-size:12px; color:var(--muted);">14 produits biologiques locaux</div>
                </div>
                <div style="margin-left:auto; font-size:12px; color:var(--muted);">Il y a 2j</div>
              </div>
            </div>
          </div>
        </div>
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

  <div class="save-bar" id="saveBar">
    <span>✏️ Modifications non sauvegardées</span>
    <button class="btn-save" onclick="saveProfile()">Enregistrer</button>
  </div>

  <script src="profil.js"></script>
</body>
</html>
