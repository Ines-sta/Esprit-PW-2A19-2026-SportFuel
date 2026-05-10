<?php
require_once __DIR__ . '/../../Model/coach/User.php';
require_once __DIR__ . '/../../Model/coach/Publication.php';
require_once __DIR__ . '/../../Model/coach/Commentaire.php';

class CoachAdminController {
    private const GLOBAL_REPLY_MARKER = '[[SRC:GLOBAL]] ';
    private const FOCUS_REPLY_MARKER = '[[SRC:FOCUS]] ';
    private $userModel;
    private $publicationModel;
    private $commentaireModel;

    public function __construct() {
        $this->userModel = new User();
        $this->publicationModel = new Publication();
        $this->commentaireModel = new Commentaire();
    }

    public function handlePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $redirectPath = $this->getSafeRedirectPath();
        $focus = strtolower(trim((string)($_GET['focus'] ?? '')));
        $isRestrictedPage = in_array($focus, ['entrainement', 'nutrition'], true);
        $action = $_POST['action'] ?? '';

        if ($isRestrictedPage && !in_array($action, ['add_comment', 'add_comment_manual', 'delete_pub'], true)) {
            $_SESSION['error'] = "Sur cette page, seules les actions Répondre, Ajouter commentaire et Supprimer sont autorisées.";
            header("Location: " . $redirectPath);
            exit;
        }

        // Add publication
        if ($action === 'add_pub') {
            $validation = $this->publicationModel->validateText($_POST['text']);
            if ($validation !== true) {
                $_SESSION['error'] = $validation;
                header("Location: " . $redirectPath);
                exit;
            }
            $utilisateurId = (int)($_POST['id_utilisateur'] ?? 0);
            if ($utilisateurId <= 0) {
                $_SESSION['error'] = "Utilisateur invalide pour cette publication.";
                header("Location: " . $redirectPath);
                exit;
            }
            $stmt = $this->publicationModel->getPdo()->prepare("INSERT INTO publication (id_utilisateur, text, date) VALUES (?, ?, NOW())");
            $stmt->execute([$utilisateurId, $_POST['text']]);
            header("Location: " . $redirectPath);
            exit;
        }

        // Edit publication
        if ($action === 'edit_pub') {
            $validation = $this->publicationModel->validateText($_POST['text']);
            if ($validation !== true) {
                $_SESSION['error'] = $validation;
                header("Location: " . $redirectPath);
                exit;
            }
            $stmt = $this->publicationModel->getPdo()->prepare("UPDATE publication SET text = ? WHERE id_pub = ?");
            $stmt->execute([$_POST['text'], $_POST['id_pub']]);
            header("Location: " . $redirectPath);
            exit;
        }

        // Delete publication
        if ($action === 'delete_pub') {
            $stmt = $this->publicationModel->getPdo()->prepare("DELETE FROM publication WHERE id_pub = ?");
            $stmt->execute([$_POST['id_pub']]);
            header("Location: " . $redirectPath);
            exit;
        }

        // Add comment manually or by reply
        if (in_array($action, ['add_comment_manual', 'add_comment'], true)) {
            if ($action === 'add_comment_manual') {
                if (empty($_POST['id_pub'])) {
                    $_SESSION['error'] = "Veuillez sélectionner une publication.";
                    header("Location: " . $redirectPath);
                    exit;
                }
            }
            $validation = $this->commentaireModel->validateText($_POST['text']);
            if ($validation !== true) {
                $_SESSION['error'] = $validation;
                header("Location: " . $redirectPath);
                exit;
            }
            $utilisateurId = (int)($_POST['id_utilisateur'] ?? $this->resolveCoachUtilisateurId());
            $scopeMarker = $isRestrictedPage ? self::FOCUS_REPLY_MARKER : self::GLOBAL_REPLY_MARKER;
            $commentText = $scopeMarker . $_POST['text'];
            $stmt = $this->commentaireModel->getPdo()->prepare("INSERT INTO commentaire (id_pub, id_utilisateur, text, date) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$_POST['id_pub'], $utilisateurId, $commentText]);
            $stmtUpdateStatus = $this->publicationModel->getPdo()->prepare("UPDATE publication SET statut = 'Répondu' WHERE id_pub = ?");
            $stmtUpdateStatus->execute([$_POST['id_pub']]);
            header("Location: " . $redirectPath);
            exit;
        }

        // Edit comment
        if ($action === 'edit_comment') {
            $validation = $this->commentaireModel->validateText($_POST['text']);
            if ($validation !== true) {
                $_SESSION['error'] = $validation;
                header("Location: " . $redirectPath);
                exit;
            }
            $stmt = $this->commentaireModel->getPdo()->prepare("UPDATE commentaire SET text = ? WHERE id_cmmnt = ?");
            $stmt->execute([$_POST['text'], $_POST['id_cmmnt']]);
            header("Location: " . $redirectPath);
            exit;
        }

        // Delete comment
        if ($action === 'delete_comment') {
            $stmt = $this->commentaireModel->getPdo()->prepare("DELETE FROM commentaire WHERE id_cmmnt = ?");
            $stmt->execute([$_POST['id_cmmnt']]);
            header("Location: " . $redirectPath);
            exit;
        }
    }

    public function getData() {
        $data = [];
        try {
            $publications = [];
            $pdo = $this->publicationModel->getPdo();
            $focus = isset($_GET['focus']) ? strtolower(trim($_GET['focus'])) : '';
            if (!in_array($focus, ['entrainement', 'nutrition'], true)) {
                $focus = '';
            }
            $search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
            $sort = isset($_GET['sort']) ? strtolower(trim((string)$_GET['sort'])) : 'desc';
            if (!in_array($sort, ['asc', 'desc'], true)) {
                $sort = 'desc';
            }

                $sql = "SELECT
                    p.*,
                    COALESCE(ut.nom, CONCAT('Utilisateur #', p.id_utilisateur)) AS nom,
                    ut.photo_profil_url AS photo_profil_url,
                    '' AS prenom
                    FROM publication p
                    LEFT JOIN utilisateurs ut ON p.id_utilisateur = ut.id
                    WHERE (ut.role = 'Sportif' OR ut.id IS NULL)";
            $params = [];

            if ($search !== '') {
                $sql .= " AND (ut.nom LIKE ?)";
                $searchLike = '%' . $search . '%';
                $params[] = $searchLike;
            }

            $sql .= " ORDER BY (COALESCE(p.priority_score, 30) + CASE WHEN TIMESTAMPDIFF(HOUR, p.date, NOW()) >= 48 THEN 10 ELSE 0 END) DESC, p.date " . strtoupper($sort);
            $stmt_pubs = $pdo->prepare($sql);
            $stmt_pubs->execute($params);
            $pubs = $stmt_pubs->fetchAll();
            
            foreach($pubs as $p) {
                $sections = $this->extractRequestSections((string)($p['text'] ?? ''));
                if ($focus === 'entrainement' && $sections['entrainement'] === '') {
                    continue;
                }
                if ($focus === 'nutrition' && $sections['nutrition'] === '') {
                    continue;
                }
                $stmt_c = $pdo->prepare("SELECT c.*, COALESCE(ut.nom, CONCAT('Utilisateur #', c.id_utilisateur)) AS nom, ut.photo_profil_url AS photo_profil_url, '' AS prenom FROM commentaire c LEFT JOIN utilisateurs ut ON c.id_utilisateur = ut.id WHERE c.id_pub = ? ORDER BY c.date ASC");
                $stmt_c->execute([$p['id_pub']]);
                $publicationComments = $stmt_c->fetchAll();
                foreach ($publicationComments as &$pubComment) {
                    $pubComment['text'] = $this->stripScopeMarker((string)($pubComment['text'] ?? ''));
                }
                unset($pubComment);
                $baseScore = isset($p['priority_score']) ? (int)$p['priority_score'] : 30;
                $ageBoost = 0;
                if (!empty($p['date']) && strtotime((string)$p['date']) !== false) {
                    $hoursElapsed = (int) floor((time() - strtotime((string)$p['date'])) / 3600);
                    if ($hoursElapsed >= 48) {
                        $ageBoost = 10;
                    }
                }
                $p['effective_priority_score'] = $baseScore + $ageBoost;
                $p['commentaires'] = $publicationComments;
                $publications[] = $p;
            }
            $data['publications'] = $publications;
            // Fetch comments linked to displayed publications (all authors)
            if ($focus === '') {
                $stmt_comments = $pdo->prepare("SELECT c.*, COALESCE(ut.nom, CONCAT('Utilisateur #', c.id_utilisateur)) AS nom, ut.photo_profil_url AS photo_profil_url, '' AS prenom FROM commentaire c LEFT JOIN utilisateurs ut ON c.id_utilisateur = ut.id ORDER BY c.date DESC");
                $stmt_comments->execute();
                $allComments = $stmt_comments->fetchAll();

                $globalComments = array_values(array_filter($allComments, function ($comment) {
                    return $this->isGlobalComment((string)($comment['text'] ?? ''));
                }));
                foreach ($globalComments as &$globalComment) {
                    $globalComment['text'] = $this->stripScopeMarker((string)($globalComment['text'] ?? ''));
                }
                unset($globalComment);
                $data['commentaires'] = $globalComments;
            } else {
                $publicationIds = array_map(static function ($pub) {
                    return (int)$pub['id_pub'];
                }, $publications);

                if (count($publicationIds) === 0) {
                    $data['commentaires'] = [];
                } else {
                    $placeholders = implode(',', array_fill(0, count($publicationIds), '?'));
                        $sql = "SELECT c.*, COALESCE(ut.nom, CONCAT('Utilisateur #', c.id_utilisateur)) AS nom, ut.photo_profil_url AS photo_profil_url, '' AS prenom
                            FROM commentaire c
                            LEFT JOIN utilisateurs ut ON c.id_utilisateur = ut.id
                            WHERE c.id_pub IN ($placeholders)
                            ORDER BY c.date DESC";
                    $stmt_comments = $pdo->prepare($sql);
                    $stmt_comments->execute($publicationIds);
                    $focusComments = $stmt_comments->fetchAll();
                    foreach ($focusComments as &$focusComment) {
                        $focusComment['text'] = $this->stripScopeMarker((string)($focusComment['text'] ?? ''));
                    }
                    unset($focusComment);
                    $data['commentaires'] = $focusComments;
                }
            }
            $data['users'] = $this->userModel->getAllUsers(); // Assuming method exists
            $data['db_error'] = null;
            $data['focus'] = $focus;
            $data['search'] = $search;
            $data['sort'] = $sort;
            $data['stats'] = $this->buildTypeStats($publications);
        } catch (PDOException $e) {
            $data['publications'] = [];
            $data['commentaires'] = [];
            $data['users'] = [];
            $data['db_error'] = "Base de données non initialisée.";
            $data['focus'] = '';
            $data['search'] = '';
            $data['sort'] = 'desc';
            $data['stats'] = [];
        }
        return $data;
    }

    private function getSafeRedirectPath() {
        $currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');
        $focus = strtolower(trim((string)($_GET['focus'] ?? '')));

        if ($currentScript === 'index.php' && (($_GET['page'] ?? '') === 'coach')) {
            $canonical = '/Esprit-PW-2A19-2026-SportFuel/index.php?page=coach';
            if (in_array($focus, ['entrainement', 'nutrition'], true)) {
                $canonical .= '&focus=' . rawurlencode($focus);
            }
            return $canonical;
        }

        $allowedScripts = ['index.php', 'demandes-entrainement.php', 'demandes-nutrition.php'];

        if (in_array($currentScript, $allowedScripts, true)) {
            return $currentScript;
        }
        return 'index.php';
    }

    private function extractRequestSections($text) {
        $normalizedText = str_replace("\r\n", "\n", (string)$text);
        $result = ['entrainement' => '', 'nutrition' => ''];

        // Use a tolerant pattern so we still parse when accents are malformed (e.g. EntraÃ®nement).
        if (preg_match('/Entra[^:\n]*\s*:[ \t]*(.*?)(?:\n\s*\n\s*Nutrition\s*:|$)/isu', $normalizedText, $trainingMatch)) {
            $result['entrainement'] = trim($trainingMatch[1]);
        }

        if (preg_match('/Nutrition\s*:[ \t]*(.*)$/isu', $normalizedText, $nutritionMatch)) {
            $result['nutrition'] = trim($nutritionMatch[1]);
        }

        return $result;
    }

    private function stripScopeMarker($text) {
        if (strpos($text, self::GLOBAL_REPLY_MARKER) === 0) {
            return substr($text, strlen(self::GLOBAL_REPLY_MARKER));
        }
        if (strpos($text, self::FOCUS_REPLY_MARKER) === 0) {
            return substr($text, strlen(self::FOCUS_REPLY_MARKER));
        }
        return $text;
    }

    private function isGlobalComment($text) {
        return strpos($text, self::GLOBAL_REPLY_MARKER) === 0;
    }

    private function buildTypeStats(array $publications) {
        $counts = [];
        $total = 0;

        foreach ($publications as $publication) {
            $text = (string)($publication['text'] ?? '');
            $type = 'Autre';
            if (preg_match('/Type\s*:\s*(.*?)(?:\n|$)/i', $text, $match)) {
                $parsedType = trim($match[1]);
                if ($parsedType !== '') {
                    $type = $parsedType;
                }
            }

            if (!isset($counts[$type])) {
                $counts[$type] = 0;
            }
            $counts[$type]++;
            $total++;
        }

        if ($total === 0) {
            return [];
        }

        $stats = [];
        foreach ($counts as $type => $count) {
            $stats[] = [
                'type' => $type,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 2),
            ];
        }

        usort($stats, static function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return $stats;
    }

    private function getPdo() {
        return SocialDatabase::getConnection();
    }

    private function resolveCoachUtilisateurId() {
        $stmt = $this->getPdo()->query(
            "SELECT id
             FROM utilisateurs
             WHERE role IN ('Coach', 'Admin')
             ORDER BY id ASC
             LIMIT 1"
        );
        $coach = $stmt->fetch();
        if ($coach && isset($coach['id'])) {
            return (int)$coach['id'];
        }

        return 0;
    }
}
?>