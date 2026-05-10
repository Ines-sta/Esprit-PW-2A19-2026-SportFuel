<?php
/**
 * Coach Dashboard View — Presentation only
 * Data is provided by CoachDashboardController
 */

// French date formatting
$jours_fr = ['Sunday'=>'Dimanche','Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi'];
$mois_fr  = ['January'=>'janvier','February'=>'février','March'=>'mars','April'=>'avril','May'=>'mai','June'=>'juin','July'=>'juillet','August'=>'août','September'=>'septembre','October'=>'octobre','November'=>'novembre','December'=>'décembre'];
$today = date('l j F Y');
foreach ($jours_fr as $en => $fr) $today = str_replace($en, $fr, $today);
foreach ($mois_fr  as $en => $fr) $today = str_replace($en, $fr, $today);
require_once __DIR__ . '/../partials/avatar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SportFuel Coach</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel/public/css/style.css">
</head>
<body>
    <?php
    $sidebarActive = 'dashboard';
    include __DIR__ . '/../partials/backoffice_sidebar.php';
    ?>

    <div class="main-area">
        <div class="page-header">
            <div>
                <h1>Tableau de bord Coach</h1>
                <div class="page-date"><?= $today ?></div>
            </div>
        </div>

        <div class="content-area">
            <!-- STATS GRID -->
            <div class="stats-row">
                <!-- Stat 1: Assigned Athletes -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['assignedAthletes'] ?></div>
                    <div class="stat-label">Athlètes assignés</div>
                    <div class="stat-delta green">Sous votre supervision</div>
                </div>

                <!-- Stat 2: Active Plans -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['activePlans'] ?></div>
                    <div class="stat-label">Plans alimentaires actifs</div>
                    <div class="stat-delta green">En cours de suivi</div>
                </div>

                <!-- Stat 3: Workouts This Week -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['thisWeekStats']['workouts_count'] ?></div>
                    <div class="stat-label">Entraînements cette semaine</div>
                    <div class="stat-delta green">Duree moyenne: <?= $metrics['thisWeekStats']['average_duration'] ?> min</div>
                </div>

                <!-- Stat 4: Completion Rate This Week -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['workoutCompletionThisWeek']['rate'] ?>%</div>
                    <div class="stat-label">Taux de completion (semaine)</div>
                    <div class="stat-delta green"><?= $metrics['workoutCompletionThisWeek']['completed'] ?>/<?= $metrics['workoutCompletionThisWeek']['total'] ?> séances</div>
                </div>
            </div>

            <!-- ADDITIONAL STATS ROW -->
            <div class="stats-row">
                <!-- Stat 5: Overall Adherence -->
                <div class="stat-card">
                    <div class="stat-value"><?= (int)($metrics['adherenceRate']['rate'] ?? 0) ?>%</div>
                    <div class="stat-label">Taux d'adhérence global</div>
                    <div class="stat-delta stable">Tous les athlètes, tous les temps</div>
                </div>

                <!-- Stat 6: Pending Approvals -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['pendingApprovals'] ?></div>
                    <div class="stat-label">Approbations en attente</div>
                    <div class="stat-delta orange">À vérifier</div>
                </div>
            </div>

            <!-- ASSIGNED ATHLETES TABLE -->
            <div class="table-section">
                <div class="table-header">
                    <h3>Athlètes assignés</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Sport</th>
                            <th>Date d'assignation</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($metrics['athletes'])): ?>
                            <?php foreach ($metrics['athletes'] as $athlete): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <?= sportfuel_avatar_markup($athlete['nom'] ?? 'Athlete', $athlete['photo_profil_url'] ?? '', 'user-avatar') ?>
                                        <strong><?= htmlspecialchars((string)($athlete['nom'] ?? 'N/A')) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars((string)($athlete['email'] ?? 'N/A')) ?></td>
                                <td><?= htmlspecialchars($athlete['sport_pratique'] ?? 'N/A') ?></td>
                                <td><?= !empty($athlete['assigned_at']) ? date('d/m/Y', strtotime($athlete['assigned_at'])) : 'N/A' ?></td>
                                <td>
                                    <span class="badge badge-actif">Actif</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-3);">Aucun athlète assigné</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PENDING PUBLICATIONS TABLE -->
            <div class="table-section" style="margin-top: 32px;">
                <div class="table-header">
                    <h3>Publications en attente d'approbation</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Athlète</th>
                            <th>Contenu</th>
                            <th>Priorité</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($metrics['pendingPublications'])): ?>
                            <?php foreach ($metrics['pendingPublications'] as $pub): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <?= sportfuel_avatar_markup($pub['nom'] ?? 'Auteur', $pub['photo_profil_url'] ?? '', 'user-avatar') ?>
                                        <strong><?= htmlspecialchars($pub['nom']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars(substr((string)($pub['text'] ?? ''), 0, 100)) ?>...</td>
                                <td>
                                    <span class="badge" style="background: #fef3c7; color: #b45309;">
                                        Priorité: <?= htmlspecialchars($pub['priorite'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($pub['date'])) ?></td>
                                <td>
                                    <span class="badge badge-en-attente">
                                        <?= htmlspecialchars($pub['statut']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-3);">Aucune publication en attente</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- RECENT WORKOUTS TABLE -->
            <div class="table-section" style="margin-top: 32px;">
                <div class="table-header">
                    <h3>Entraînements récents</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Athlète</th>
                            <th>Nom entraînement</th>
                            <th>Durée</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($metrics['recentWorkouts'])): ?>
                            <?php foreach ($metrics['recentWorkouts'] as $wo): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <?= sportfuel_avatar_markup($wo['nom'] ?? 'Athlete', $wo['photo_profil_url'] ?? '', 'user-avatar') ?>
                                        <strong><?= htmlspecialchars($wo['nom']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($wo['titre'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($wo['duree_totale'] ?? 'N/A') ?> min</td>
                                <td><?= date('d/m/Y H:i', strtotime($wo['date_entrainement'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(str_replace(' ', '-', $wo['statut'])) ?>">
                                        <?= htmlspecialchars($wo['statut']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-3);">Aucun entraînement récent</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar.sf-avatar {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, #52b788, #2d6a4f);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        .page-header {
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }

        .page-date {
            font-size: 12px;
            color: var(--text-3);
            margin-top: 4px;
        }

        .content-area {
            padding: 32px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
            line-height: 1.2;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-3);
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-delta {
            font-size: 11px;
            margin-top: 10px;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
            font-weight: 500;
        }

        .stat-delta.green {
            background: rgba(82, 183, 136, 0.1);
            color: #2d6a4f;
        }

        .stat-delta.stable {
            background: rgba(212, 112, 10, 0.1);
            color: #d4700a;
        }

        .stat-delta.orange {
            background: rgba(251, 146, 60, 0.1);
            color: #c2410c;
        }

        .table-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            background: #f9fafb;
        }

        .table-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #f9fafb;
            border-bottom: 1px solid var(--border);
        }

        th {
            padding: 12px 24px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 16px 24px;
            font-size: 13px;
            color: var(--text);
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-actif {
            background: #d8f3dc;
            color: #2d6a4f;
        }

        .badge-inactif {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-en-attente {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-complétée {
            background: #d8f3dc;
            color: #2d6a4f;
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            th, td {
                padding: 12px 16px;
                font-size: 12px;
            }
        }
    </style>
</body>
</html>
