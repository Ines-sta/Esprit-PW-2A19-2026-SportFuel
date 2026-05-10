<?php
/**
 * Admin Dashboard View - Presentation only
 * Data is provided by AdminDashboardController
 */

// French date formatting
$jours_fr = ['Sunday'=>'Dimanche','Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi'];
$mois_fr  = ['January'=>'janvier','February'=>'février','March'=>'mars','April'=>'avril','May'=>'mai','June'=>'juin','July'=>'juillet','August'=>'août','September'=>'septembre','October'=>'octobre','November'=>'novembre','December'=>'décembre'];
$today = date('l j F Y');
foreach ($jours_fr as $en => $fr) $today = str_replace($en, $fr, $today);
foreach ($mois_fr  as $en => $fr) $today = str_replace($en, $fr, $today);

$dashboardNotice = $_SESSION['dashboard_notice'] ?? '';
$dashboardError = $_SESSION['dashboard_error'] ?? '';
unset($_SESSION['dashboard_notice'], $_SESSION['dashboard_error']);

$assignmentData = $metrics['assignmentManagement'] ?? ['coaches' => [], 'sportifs' => [], 'assignments' => []];
$coaches = $assignmentData['coaches'] ?? [];
$sportifs = $assignmentData['sportifs'] ?? [];
$assignments = $assignmentData['assignments'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SportFuel Admin</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2526-SportFuel/public/css/style.css">
</head>
<body>
    <?php
    $sidebarActive = 'dashboard';
    include __DIR__ . '/../partials/backoffice_sidebar.php';
    ?>

    <div class="main-area">
        <div class="page-header">
            <h1>Tableau de bord</h1>
            <div class="page-date"><?= $today ?></div>
        </div>

        <div class="content-area">
            <?php if ($dashboardNotice !== ''): ?>
                <div class="dashboard-alert dashboard-alert-success"><?= htmlspecialchars($dashboardNotice) ?></div>
            <?php endif; ?>
            <?php if ($dashboardError !== ''): ?>
                <div class="dashboard-alert dashboard-alert-error"><?= htmlspecialchars($dashboardError) ?></div>
            <?php endif; ?>

            <!-- STATS GRID -->
            <div class="stats-row">
                <!-- Stat 1: Total Users by Role -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['usersByRole']['total'] ?></div>
                    <div class="stat-label">Utilisateurs total</div>
                    <div class="stat-delta green">
                        Sportifs: <?= $metrics['usersByRole']['breakdown']['Sportif'] ?? 0 ?> | 
                        Coaches: <?= $metrics['usersByRole']['breakdown']['Coach'] ?? 0 ?> | 
                        Admins: <?= $metrics['usersByRole']['breakdown']['Admin'] ?? 0 ?>
                    </div>
                </div>

                <!-- Stat 2: Active Users This Month -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['activeUsersThisMonth'] ?></div>
                    <div class="stat-label">Nouvel utilisateurs ce mois</div>
                    <div class="stat-delta green">+<?= round($metrics['activeUsersThisMonth'] > 0 ? ($metrics['activeUsersThisMonth'] / $metrics['usersByRole']['total'] * 100) : 0) ?>% du total</div>
                </div>

                <!-- Stat 3: Plans Count -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['planMetrics']['total'] ?></div>
                    <div class="stat-label">Plans alimentaires</div>
                    <div class="stat-delta green">Actifs et assignés</div>
                </div>

                <!-- Stat 4: Training Completion -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['trainingMetrics']['completionRate'] ?>%</div>
                    <div class="stat-label">Taux complétude entraînement</div>
                    <div class="stat-delta green"><?= $metrics['trainingMetrics']['completed'] ?>/<?= $metrics['trainingMetrics']['total'] ?> séances</div>
                </div>
            </div>

            <!-- ADDITIONAL STATS ROW -->
            <div class="stats-row">
                <!-- Stat 5: Publication Queue -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['pendingPublications'] ?></div>
                    <div class="stat-label">Publications en attente</div>
                    <div class="stat-delta stable">À approuver</div>
                </div>

                <!-- Stat 6: Coach Assignments -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['coachAssignments']['coaches_count'] ?? 0 ?></div>
                    <div class="stat-label">Coaches actifs</div>
                    <div class="stat-delta green">Avec <?= $metrics['coachAssignments']['athletes_count'] ?? 0 ?> athlètes</div>
                </div>

                <!-- Stat 7: Plans Distribution -->
                <div class="stat-card">
                    <div class="stat-value"><?= count($metrics['planMetrics']['byType']) ?></div>
                    <div class="stat-label">Types de plans</div>
                    <div class="stat-delta green">
                        <?php foreach ($metrics['planMetrics']['byType'] as $pt): ?>
                            <?= ucfirst(str_replace('_', ' ', $pt['type'])) ?>: <?= $pt['count'] ?> | 
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Stat 8: Average Sessions -->
                <div class="stat-card">
                    <div class="stat-value"><?= $metrics['trainingMetrics']['total'] ?></div>
                    <div class="stat-label">Séances total</div>
                    <div class="stat-delta stable">Enregistrées</div>
                </div>
            </div>

            <div class="table-section">
                <div class="table-header">
                    <h3>Assignments Coach - Sportif</h3>
                </div>
                <div class="assignment-form-wrap">
                    <form method="post" action="/Esprit-PW-2A19-2526-SportFuel/index.php?page=dashboard" class="assignment-form">
                        <input type="hidden" name="dashboard_action" value="assign_sportif">
                        <div class="assignment-field">
                            <label for="coach_id">Coach</label>
                            <select name="coach_id" id="coach_id" required>
                                <option value="">Sélectionnez un coach</option>
                                <?php foreach ($coaches as $coach): ?>
                                    <option value="<?= (int)$coach['id'] ?>"><?= htmlspecialchars(($coach['nom'] ?? 'Coach') . ' - ' . ($coach['email'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="assignment-field">
                            <label for="sportif_id">Sportif</label>
                            <select name="sportif_id" id="sportif_id" required>
                                <option value="">Sélectionnez un sportif</option>
                                <?php foreach ($sportifs as $sportif): ?>
                                    <option value="<?= (int)$sportif['id'] ?>"><?= htmlspecialchars(($sportif['nom'] ?? 'Sportif') . ' - ' . ($sportif['email'] ?? '') . (!empty($sportif['sport_pratique']) ? ' (' . $sportif['sport_pratique'] . ')' : '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-assign">Assigner</button>
                    </form>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Coach</th>
                            <th>Sportif</th>
                            <th>Sport</th>
                            <th>Assigné le</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($assignments)): ?>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($assignment['coach_nom'] ?? 'N/A') ?></strong><br><small><?= htmlspecialchars($assignment['coach_email'] ?? '') ?></small></td>
                                    <td><strong><?= htmlspecialchars($assignment['sportif_nom'] ?? 'N/A') ?></strong><br><small><?= htmlspecialchars($assignment['sportif_email'] ?? '') ?></small></td>
                                    <td><?= htmlspecialchars($assignment['sport_pratique'] ?? 'N/A') ?></td>
                                    <td><?= !empty($assignment['assigned_at']) ? date('d/m/Y H:i', strtotime($assignment['assigned_at'])) : 'N/A' ?></td>
                                    <td>
                                        <form method="post" action="/Esprit-PW-2A19-2526-SportFuel/index.php?page=dashboard" onsubmit="return confirm('Supprimer cette assignment ?');">
                                            <input type="hidden" name="dashboard_action" value="remove_assignment">
                                            <input type="hidden" name="assignment_id" value="<?= (int)$assignment['id_assignment'] ?>">
                                            <button type="submit" class="btn-remove">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-3);">Aucune assignment configurée.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- RECENT ACTIVITY TABLE -->
            <div class="table-section">
                <div class="table-header">
                    <h3>Utilisateurs récemment inscrits</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Sport</th>
                            <th>Date inscription</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($metrics['recentUsers'])): ?>
                            <?php foreach ($metrics['recentUsers'] as $user): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($user['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($user['role']) ?>">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($user['sport_pratique'] ?? 'N/A') ?></td>
                                <td><?= $user['date_inscription'] ? date('d/m/Y', strtotime($user['date_inscription'])) : 'N/A' ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($user['statut']) ?>">
                                        <?= htmlspecialchars($user['statut']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--text-3);">Aucun utilisateur trouvé</td></tr>
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
                            <th>Auteur</th>
                            <th>Contenu</th>
                            <th>Priorité</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($metrics['pendingPublicationsList'])): ?>
                            <?php foreach ($metrics['pendingPublicationsList'] as $pub): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($pub['nom']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($pub['text'], 0, 100)) ?>...</td>
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
        </div>
    </div>

    <style>
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

        .dashboard-alert {
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid transparent;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
        }

        .dashboard-alert-success {
            background: #ecfdf5;
            color: #166534;
            border-color: #bbf7d0;
        }

        .dashboard-alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .assignment-form-wrap {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            background: #fbfdff;
        }

        .assignment-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .assignment-field {
            min-width: 260px;
            flex: 1 1 260px;
        }

        .assignment-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            color: var(--text-3);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .assignment-field select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
        }

        .btn-assign,
        .btn-remove {
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            padding: 10px 14px;
        }

        .btn-assign {
            background: var(--accent);
            color: #fff;
            min-height: 40px;
        }

        .btn-remove {
            background: #fee2e2;
            color: #991b1b;
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

        .table-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }

        .table-section + .table-section {
            margin-top: 32px;
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

        .badge-admin {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-coach {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-sportif {
            background: #d8f3dc;
            color: #2d6a4f;
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
