<?php
require_once __DIR__ . '/../../Model/coach/User.php';
require_once __DIR__ . '/../../Model/coach/Publication.php';
require_once __DIR__ . '/../../Model/coach/Commentaire.php';

class CoachUserController {
    private $userModel;
    private $publicationModel;
    private $commentaireModel;
    private $sportif_id = 3; // Simuler connecté (utilisateurs.id)

    public function __construct() {
        $this->userModel = new User();
        $this->publicationModel = new Publication();
        $this->commentaireModel = new Commentaire();
        $this->sportif_id = $this->resolveSportifId();
    }

    public function handlePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $redirectPath = $this->getSafeRedirectPath();
        $focus = strtolower(trim((string)($_GET['focus'] ?? '')));
        $isFocusPage = in_array($focus, ['entrainement', 'nutrition'], true);
        $action = $_POST['action'] ?? '';

        if ($isFocusPage && $action === 'edit_pub') {
            $_SESSION['error'] = "Sur cette page, la modification est désactivée.";
            header("Location: " . $redirectPath);
            exit;
        }

        // Add publication
        if ($action === 'add_pub') {
            $demandeEntrainement = trim((string)($_POST['demande_entrainement'] ?? ''));
            $demandeNutrition = trim((string)($_POST['demande_nutrition'] ?? ''));

            if ($demandeEntrainement !== '' && $this->containsNutritionKeywords($demandeEntrainement)) {
                $_SESSION['error'] = "Le champ entraînement contient des mots liés à la nutrition. Veuillez corriger le contenu.";
                header("Location: " . $redirectPath);
                exit;
            }

            if ($demandeNutrition !== '' && $this->containsTrainingKeywords($demandeNutrition)) {
                $_SESSION['error'] = "Le champ nutrition contient des mots liés à l'entraînement. Veuillez corriger le contenu.";
                header("Location: " . $redirectPath);
                exit;
            }

            $validation = $this->publicationModel->validateText($_POST['text']);
            if ($validation !== true) {
                $_SESSION['error'] = $validation;
                header("Location: " . $redirectPath);
                exit;
            }
            $priorityData = $this->computePriority((string)($_POST['text'] ?? ''));
            try {
                $stmt = $this->publicationModel->getPdo()->prepare("INSERT INTO publication (id_utilisateur, text, priorite, priority_score, statut, date) VALUES (?, ?, ?, ?, 'En attente', NOW())");
                $stmt->execute([$this->sportif_id, $_POST['text'], $priorityData['priorite'], $priorityData['score']]);
            } catch (PDOException $e) {
                $_SESSION['error'] = "Impossible d'ajouter la publication: utilisateur invalide.";
                header("Location: " . $redirectPath);
                exit;
            }
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
            $stmt = $this->publicationModel->getPdo()->prepare("UPDATE publication SET text = ? WHERE id_pub = ? AND id_utilisateur = ?");
            $stmt->execute([$_POST['text'], $_POST['id_pub'], $this->sportif_id]);
            header("Location: " . $redirectPath);
            exit;
        }

        // Delete publication
        if ($action === 'delete_pub') {
            $stmt = $this->publicationModel->getPdo()->prepare("DELETE FROM publication WHERE id_pub = ? AND id_utilisateur = ?");
            $stmt->execute([$_POST['id_pub'], $this->sportif_id]);
            header("Location: " . $redirectPath);
            exit;
        }

        // Add comment by reply
        if ($action === 'add_comment') {
            $validation = $this->commentaireModel->validateText($_POST['text'] ?? '');
            if ($validation !== true) {
                $_SESSION['error'] = $validation;
                header("Location: " . $redirectPath);
                exit;
            }
            $stmt = $this->commentaireModel->getPdo()->prepare("INSERT INTO commentaire (id_pub, id_utilisateur, text, date) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$_POST['id_pub'], $this->sportif_id, $_POST['text']]);
            header("Location: " . $redirectPath);
            exit;
        }
    }

    public function getData() {
        $data = [];
        try {
            $data['current_user'] = $this->userModel->getUserById($this->sportif_id);
            $publications = [];
            $pdo = $this->publicationModel->getPdo();
            $focus = isset($_GET['focus']) ? strtolower(trim($_GET['focus'])) : '';
            if (!in_array($focus, ['entrainement', 'nutrition'], true)) {
                $focus = '';
            }
            $stmt_pubs = $pdo->prepare("SELECT * FROM publication WHERE id_utilisateur = ? ORDER BY date DESC");
            $stmt_pubs->execute([$this->sportif_id]);
            $pubs = $stmt_pubs->fetchAll();
            
            foreach($pubs as $p) {
                $sections = $this->extractRequestSections((string)($p['text'] ?? ''));
                if ($focus === 'entrainement' && $sections['entrainement'] === '') {
                    continue;
                }
                if ($focus === 'nutrition' && $sections['nutrition'] === '') {
                    continue;
                }
                $stmt_c = $pdo->prepare("SELECT c.*, ut.nom AS auteur_nom, ut.photo_profil_url AS photo_profil_url FROM commentaire c LEFT JOIN utilisateurs ut ON ut.id = c.id_utilisateur WHERE c.id_pub = ? ORDER BY c.date ASC");
                $stmt_c->execute([$p['id_pub']]);
                $publicationComments = $stmt_c->fetchAll();
                foreach ($publicationComments as &$pubComment) {
                    $pubComment['text'] = $this->stripScopeMarker((string)($pubComment['text'] ?? ''));
                }
                unset($pubComment);
                $p['commentaires'] = $publicationComments;
                $publications[] = $p;
            }
            $data['publications'] = $publications;
            $data['db_error'] = null;
            $data['focus'] = $focus;
        } catch (PDOException $e) {
            $data['current_user'] = null;
            $data['publications'] = [];
            $data['db_error'] = "Base de données non initialisée.";
            $data['focus'] = '';
        }
        return $data;
    }

    private function getSafeRedirectPath() {
        $currentScript = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');
        $focus = strtolower(trim((string)($_GET['focus'] ?? '')));

        if ($currentScript === 'index.php' && (($_GET['page'] ?? '') === 'coach')) {
            $canonical = '/Esprit-PW-2A19-2026-SportFuel/index.php?page=coach';
            if (in_array($focus, ['entrainement', 'nutrition'], true)) {
                $canonical .= '&focus=' . rawurlencode($focus);
            }
            return $canonical;
        }

        $allowedScripts = ['dashboard.php', 'demandes-entrainement.php', 'demandes-nutrition.php'];

        if (in_array($currentScript, $allowedScripts, true)) {
            return $currentScript;
        }
        return 'dashboard.php';
    }

    private function extractRequestSections($text) {
        $normalizedText = str_replace("\r\n", "\n", (string)$text);
        $result = ['entrainement' => '', 'nutrition' => ''];

        if (preg_match('/Entra(?:î|i)nement\s*:[ \t]*(.*?)(?:\n\s*\n\s*Nutrition\s*:|$)/isu', $normalizedText, $trainingMatch)) {
            $result['entrainement'] = trim($trainingMatch[1]);
        }

        if (preg_match('/Nutrition\s*:[ \t]*(.*)$/isu', $normalizedText, $nutritionMatch)) {
            $result['nutrition'] = trim($nutritionMatch[1]);
        }

        return $result;
    }

    private function stripScopeMarker($text) {
        $globalMarker = '[[SRC:GLOBAL]] ';
        $focusMarker = '[[SRC:FOCUS]] ';
        if (strpos($text, $globalMarker) === 0) {
            return substr($text, strlen($globalMarker));
        }
        if (strpos($text, $focusMarker) === 0) {
            return substr($text, strlen($focusMarker));
        }
        return $text;
    }

    private function containsTrainingKeywords($text) {
        $keywords = [
            'entrainement', 'entraînement', 'seance', 'séance', 'cardio', 'musculation',
            'repetition', 'répétition', 'series', 'séries', 'squat', 'pompe', 'deadlift',
            'running', 'course', 'echauffement', 'échauffement'
        ];
        return $this->containsAnyKeyword($text, $keywords);
    }

    private function containsNutritionKeywords($text) {
        $keywords = [
            'nutrition', 'calorie', 'calories', 'proteine', 'protéine', 'proteines', 'protéines',
            'glucide', 'glucides', 'lipide', 'lipides', 'repas', 'aliment', 'aliments',
            'hydration', 'eau', 'vitamine', 'supplement', 'supplément'
        ];
        return $this->containsAnyKeyword($text, $keywords);
    }

    private function containsAnyKeyword($text, $keywords) {
        $normalized = mb_strtolower((string)$text, 'UTF-8');
        foreach ($keywords as $keyword) {
            if (mb_strpos($normalized, mb_strtolower($keyword, 'UTF-8')) !== false) {
                return true;
            }
        }
        return false;
    }

    private function computePriority($text) {
        $normalized = mb_strtolower((string)$text, 'UTF-8');

        $urgentPatterns = [
            'blessure', 'douleur', 'je ne peux plus', 'probleme genou', 'problème genou',
            'probleme dos', 'problème dos', 'mal au genou', 'mal au dos', 'douleur forte'
        ];
        $importantPatterns = [
            'stagnation', 'je ne progresse plus', 'plateau', 'fatigue extreme', 'fatigue extrême'
        ];

        $priorite = 'normal';
        $score = 30;

        foreach ($urgentPatterns as $pattern) {
            if (mb_strpos($normalized, $pattern) !== false) {
                $priorite = 'urgent';
                $score = 100;
                break;
            }
        }

        if ($priorite === 'normal') {
            foreach ($importantPatterns as $pattern) {
                if (mb_strpos($normalized, $pattern) !== false) {
                    $priorite = 'important';
                    $score = 70;
                    break;
                }
            }
        }

        if ($this->isPremiumUser($this->sportif_id)) {
            $score += 20;
        }

        return [
            'priorite' => $priorite,
            'score' => $score,
        ];
    }

    private function isPremiumUser($userId) {
        try {
            $pdo = $this->publicationModel->getPdo();
            $stmt = $pdo->prepare(
                "SELECT
                    ut.*,
                    ut.role AS utilisateur_role,
                    ut.statut AS utilisateur_statut
                 FROM utilisateurs ut
                 WHERE ut.id = ?
                 LIMIT 1"
            );
            $stmt->execute([(int)$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                return false;
            }

            if (isset($user['is_premium']) && (int)$user['is_premium'] === 1) {
                return true;
            }
            if (isset($user['premium']) && (int)$user['premium'] === 1) {
                return true;
            }
            if (isset($user['plan']) && mb_strtolower((string)$user['plan'], 'UTF-8') === 'premium') {
                return true;
            }
            if (isset($user['role']) && mb_strtolower((string)$user['role'], 'UTF-8') === 'premium') {
                return true;
            }
            if (isset($user['utilisateur_role']) && mb_strtolower((string)$user['utilisateur_role'], 'UTF-8') === 'premium') {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    private function resolveSportifId() {
        // Prefer the authenticated session user.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($sessionId > 0) {
            return $sessionId;
        }

        // Keep configured id only if that user is a Sportif.
        $pdo = $this->publicationModel->getPdo();
        $stmtConfigured = $pdo->prepare(
            "SELECT ut.id
             FROM utilisateurs ut
             WHERE ut.id = ?
                 AND COALESCE(ut.role, 'Sportif') = 'Sportif'
             LIMIT 1"
        );
        $stmtConfigured->execute([$this->sportif_id]);
        $configuredUser = $stmtConfigured->fetch();
        if ($configuredUser && isset($configuredUser['id'])) {
            return (int)$configuredUser['id'];
        }

        // Fallback to first user with role Sportif (not just first user in table).
        $stmtSportif = $pdo->query(
            "SELECT ut.id
             FROM utilisateurs ut
             WHERE COALESCE(ut.role, 'Sportif') = 'Sportif'
             ORDER BY ut.id ASC
             LIMIT 1"
        );
        $sportif = $stmtSportif->fetch();
        if ($sportif && isset($sportif['id'])) {
            return (int)$sportif['id'];
        }

        return 0;
    }

    private function getPdo() {
        return SocialDatabase::getConnection();
    }
}
?>