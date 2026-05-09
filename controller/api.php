<?php
/**
 * API centrale SportFuel
 * Toutes les requêtes JS passent par ici
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Utilisateur.php';

$pdo = Config::getConnexion();

header('Content-Type: application/json; charset=utf-8');

function smtpRead($socket) {
    $data = '';
    while (($line = fgets($socket, 515)) !== false) {
        $data .= $line;
        if (preg_match('/^\d{3}\s/', $line)) {
            break;
        }
    }
    return $data;
}

function smtpWrite($socket, $command) {
    fwrite($socket, $command . "\r\n");
}

function smtpExpect($socket, array $okCodes) {
    $response = smtpRead($socket);
    foreach ($okCodes as $code) {
        if (strpos($response, (string)$code) === 0) {
            return [true, $response];
        }
    }
    return [false, $response];
}

function smtpSendMail($to, $subject, $body, $settings) {
    $host = $settings['host'];
    $port = (int)$settings['port'];
    $username = $settings['username'];
    $password = $settings['password'];
    $fromEmail = $settings['from_email'];
    $fromName = $settings['from_name'];

    $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 20);
    if (!$socket) {
        return [false, "Connexion SMTP impossible: $errstr ($errno)"];
    }
    stream_set_timeout($socket, 20);

    [$ok, $resp] = smtpExpect($socket, [220]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, 'EHLO localhost');
    [$ok, $resp] = smtpExpect($socket, [250]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, 'STARTTLS');
    [$ok, $resp] = smtpExpect($socket, [220]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return [false, 'Echec activation TLS'];
    }

    smtpWrite($socket, 'EHLO localhost');
    [$ok, $resp] = smtpExpect($socket, [250]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, 'AUTH LOGIN');
    [$ok, $resp] = smtpExpect($socket, [334]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, base64_encode($username));
    [$ok, $resp] = smtpExpect($socket, [334]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, base64_encode($password));
    [$ok, $resp] = smtpExpect($socket, [235]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, 'MAIL FROM:<' . $fromEmail . '>');
    [$ok, $resp] = smtpExpect($socket, [250]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, 'RCPT TO:<' . $to . '>');
    [$ok, $resp] = smtpExpect($socket, [250, 251]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, 'DATA');
    [$ok, $resp] = smtpExpect($socket, [354]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'To: <' . $to . '>';
    $headers[] = 'Subject: ' . $encodedSubject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';

    $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
    smtpWrite($socket, $data);
    [$ok, $resp] = smtpExpect($socket, [250]);
    if (!$ok) { fclose($socket); return [false, $resp]; }

    smtpWrite($socket, 'QUIT');
    fclose($socket);
    return [true, 'Email envoyé'];
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

/* ────────────────────────────────────────────
   Route publique : vérification de session
──────────────────────────────────────────── */
if ($action === 'check_session') {
    if (isset($_SESSION['user_email'])) {
        echo json_encode([
            'authenticated' => true,
            'role'  => $_SESSION['role'] ?? 'Sportif',
            'nom'   => $_SESSION['user_nom'] ?? '',
            'email' => $_SESSION['user_email']
        ]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
    exit;
}

/* ────────────────────────────────────────────
   Routes publiques : mot de passe oublié
──────────────────────────────────────────── */
if ($action === 'send_reset_code' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $emailInput = trim((string)($data['email'] ?? ''));

    if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        exit;
    }

    $user = Utilisateur::findByEmail($pdo, $emailInput);
    if (!$user) {
        echo json_encode(['success' => true, 'message' => 'Si cet email existe, le code a été envoyé.']);
        exit;
    }

    $otp = (string)random_int(1000, 9999);
    $_SESSION['reset_email'] = $emailInput;
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_otp_expires'] = time() + 600; // 10 min
    $_SESSION['reset_otp_verified'] = false;

    require __DIR__ . '/smtp_settings.php';
    if (empty($SMTP_ENABLED)) {
        echo json_encode([
            'success' => true,
            'message' => 'Mode local actif: email non configuré, code affiché dans l’interface.',
            'dev_code' => $otp
        ]);
        exit;
    }

    $smtpSettings = [
        'host' => $SMTP_HOST ?? '',
        'port' => $SMTP_PORT ?? 587,
        'username' => $SMTP_USERNAME ?? '',
        'password' => $SMTP_PASSWORD ?? '',
        'from_email' => $SMTP_FROM_EMAIL ?? '',
        'from_name' => $SMTP_FROM_NAME ?? 'SportFuel',
    ];
    if (
        empty($smtpSettings['host']) ||
        empty($smtpSettings['username']) ||
        empty($smtpSettings['password']) ||
        empty($smtpSettings['from_email']) ||
        strpos($smtpSettings['username'], 'votre_adresse') !== false ||
        strpos($smtpSettings['password'], 'votre_app_password') !== false
    ) {
        echo json_encode([
            'success' => true,
            'message' => 'SMTP non configuré: code affiché dans l’interface (mode local).',
            'dev_code' => $otp
        ]);
        exit;
    }

    $subject = 'SportFuel - Code de reinitialisation';
    $message = "Bonjour,\n\nVotre code OTP SportFuel est : {$otp}\nIl expire dans 10 minutes.\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez cet email.";
    [$sent, $smtpError] = smtpSendMail($emailInput, $subject, $message, $smtpSettings);

    if (!$sent) {
        echo json_encode([
            'success' => true,
            'message' => 'Echec SMTP local, code affiché dans l’interface.',
            'dev_code' => $otp,
            'smtp_error' => $smtpError
        ]);
        exit;
    }
    echo json_encode(['success' => true, 'message' => 'Code envoyé à votre email 📧']);
    exit;
}

if ($action === 'verify_reset_code' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $emailInput = trim((string)($data['email'] ?? ''));
    $codeInput = trim((string)($data['code'] ?? ''));

    if (
        empty($_SESSION['reset_email']) ||
        empty($_SESSION['reset_otp']) ||
        empty($_SESSION['reset_otp_expires'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Aucun code actif. Redemandez un OTP.']);
        exit;
    }

    if (time() > (int)$_SESSION['reset_otp_expires']) {
        echo json_encode(['success' => false, 'message' => 'Code expiré.']);
        exit;
    }

    if (
        strcasecmp($_SESSION['reset_email'], $emailInput) !== 0 ||
        $_SESSION['reset_otp'] !== $codeInput
    ) {
        echo json_encode(['success' => false, 'message' => 'Code OTP incorrect.']);
        exit;
    }

    $_SESSION['reset_otp_verified'] = true;
    echo json_encode(['success' => true, 'message' => 'Code validé.']);
    exit;
}

if ($action === 'reset_password' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $emailInput = trim((string)($data['email'] ?? ''));
    $newPassword = (string)($data['password'] ?? '');
    $confirmPassword = (string)($data['confirm_password'] ?? '');

    if ($newPassword === '' || strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe trop court (min 6).']);
        exit;
    }
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
        exit;
    }
    if (empty($_SESSION['reset_otp_verified']) || $_SESSION['reset_otp_verified'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Code OTP non vérifié.']);
        exit;
    }
    if (empty($_SESSION['reset_email']) || strcasecmp($_SESSION['reset_email'], $emailInput) !== 0) {
        echo json_encode(['success' => false, 'message' => 'Session de réinitialisation invalide.']);
        exit;
    }

    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
    $stmt->execute([$hash, $emailInput]);

    if ($stmt->rowCount() > 0) {
        unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['reset_otp_expires'], $_SESSION['reset_otp_verified']);
        echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur : l’utilisateur n’existe pas ou le mot de passe est identique.']);
    }
    exit;
}

/* ────────────────────────────────────────────
   Routes Face ID (Publiques pour la connexion)
──────────────────────────────────────────── */
if ($action === 'get_face_descriptors' && $method === 'GET') {
    // Retourne la liste des empreintes faciales pour la comparaison
    $stmt = $pdo->prepare("SELECT id, nom, face_descriptor FROM utilisateurs WHERE face_descriptor IS NOT NULL");
    $stmt->execute();
    $descriptors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'descriptors' => $descriptors]);
    exit;
}

if ($action === 'login_by_id' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $userId = (int)($data['id'] ?? 0);

    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['statut'] === 'Banni') {
            echo json_encode(['success' => false, 'message' => 'banned']);
            exit;
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['role'] = $user['role'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Connexion réussie',
            'redirect' => (strcasecmp($user['role'], 'Admin') === 0) ? '/Esprit-PW-2A19-2526-SportFuel/BackOffice/index.php' : '/Esprit-PW-2A19-2526-SportFuel/View/profil.html'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    }
    exit;
}

/* ────────────────────────────────────────────
   Toutes les autres routes nécessitent une session
──────────────────────────────────────────── */
if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$email = $_SESSION['user_email'];
$role  = trim((string)($_SESSION['role'] ?? 'Sportif'));

/* ── GET : mon profil ── */
if ($action === 'me' && $method === 'GET') {
    $user = Utilisateur::findByEmail($pdo, $email);
    if ($user) {
        $imc = ($user->getTaille() > 0)
            ? round($user->getPoids() / (($user->getTaille() / 100) ** 2), 1)
            : 0;
        echo json_encode(['success' => true, 'user' => [
            'id'        => $user->getId(),
            'nom'       => $user->getNom(),
            'email'     => $user->getEmail(),
            'age'       => $user->getAge(),
            'poids'     => $user->getPoids(),
            'taille'    => $user->getTaille(),
            'sport'     => $user->getSport(),
            'objectif'  => $user->getObjectif(),
            'niveau'    => $user->getNiveau(),
            'frequence' => $user->getFrequence(),
            'role'      => $user->getRole(),
            'statut'    => $user->getStatut(),
            'imc'       => $imc
        ]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    }
    exit;
}

/* ── POST : sauvegarder profil ── */
if ($action === 'save_profil' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $user = Utilisateur::findByEmail($pdo, $email);
    if (!$user) { echo json_encode(['success' => false, 'message' => 'Introuvable']); exit; }

    if (isset($data['nom']))      $user->setNom($data['nom']);
    if (isset($data['age']))      $user->setAge((int)$data['age']);
    if (isset($data['poids']))    $user->setPoids((float)$data['poids']);
    if (isset($data['taille']))   $user->setTaille((float)$data['taille']);
    if (isset($data['sport']))    $user->setSport($data['sport']);
    if (isset($data['objectif'])) $user->setObjectif($data['objectif']);
    if (isset($data['niveau']))   $user->setNiveau($data['niveau']);
    if (isset($data['frequence'])) $user->setFrequence((int)$data['frequence']);

    if ($user->update($pdo)) {
        $_SESSION['user_nom'] = $user->getNom();
        echo json_encode(['success' => true, 'message' => 'Profil enregistré avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
    }
    exit;
}

/* ── POST : sauvegarder empreinte faciale ── */
if ($action === 'save_face_descriptor' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $descriptor = $data['descriptor'] ?? null;

    if (!$descriptor) {
        echo json_encode(['success' => false, 'message' => 'Empreinte manquante']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE utilisateurs SET face_descriptor = ? WHERE email = ?");
    $result = $stmt->execute([json_encode($descriptor), $email]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Empreinte faciale enregistrée en base de données']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l’enregistrement']);
    }
    exit;
}

/* ── POST : supprimer mon compte ── */
if ($action === 'delete_account' && $method === 'POST') {
    $user = Utilisateur::findByEmail($pdo, $email);
    if ($user && Utilisateur::delete($pdo, $user->getId())) {
        session_destroy();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur suppression']);
    }
    exit;
}

/* ────────────────────────────────────────────
   Routes réservées à l'Admin
──────────────────────────────────────────── */
if (strcasecmp($role, 'Admin') !== 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux administrateurs']);
    exit;
}

/* ── GET : liste utilisateurs + stats ── */
if ($action === 'users' && $method === 'GET') {
    $users = Utilisateur::getAll($pdo);
    $stats = Utilisateur::getStats($pdo);
    $list  = [];
    foreach ($users as $u) {
        $list[] = [
            'id'               => $u->getId(),
            'nom'              => $u->getNom(),
            'email'            => $u->getEmail(),
            'age'              => $u->getAge(),
            'sport'            => $u->getSport(),
            'objectif'         => $u->getObjectif(),
            'role'             => $u->getRole(),
            'statut'           => $u->getStatut(),
            'date_inscription' => $u->date_inscription ?? ''
        ];
    }
    echo json_encode(['success' => true, 'users' => $list, 'stats' => $stats]);
    exit;
}

/* ── POST : ajouter un utilisateur ── */
if ($action === 'add_user' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($data['nom']) || empty($data['email']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes']); exit;
    }
    try {
        $u = new Utilisateur(null, $data['nom'], $data['email'], '',
            $data['age'] ?? 0, 0, 0,
            $data['sport'] ?? 'Aucun', 'Non défini', 'Débutant', 1,
            $data['role'] ?? 'Sportif', $data['statut'] ?? 'Actif');
        $u->setPassword($data['password']);
        echo json_encode(['success' => $u->save($pdo)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

/* ── POST : modifier un utilisateur ── */
if ($action === 'edit_user' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($data['id']) || empty($data['nom']) || empty($data['email'])) {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes']); exit;
    }
    try {
        $params = [$data['nom'], $data['email'], $data['role'], $data['statut'], $data['age'] ?? 0, $data['sport'] ?? '', $data['id']];
        $sql    = "UPDATE utilisateurs SET nom=?, email=?, role=?, statut=?, age=?, sport_pratique=? WHERE id=?";
        if (!empty($data['password'])) {
            $sql    = "UPDATE utilisateurs SET nom=?, email=?, mot_de_passe=?, role=?, statut=?, age=?, sport_pratique=? WHERE id=?";
            $params = [$data['nom'], $data['email'], password_hash($data['password'], PASSWORD_BCRYPT), $data['role'], $data['statut'], $data['age'] ?? 0, $data['sport'] ?? '', $data['id']];
        }
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute($params);
        if ($success) {
            Utilisateur::syncSocialBridge($pdo, (int)$data['id']);
        }
        echo json_encode(['success' => $success]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

/* ── POST : supprimer un utilisateur ── */
if ($action === 'delete_user' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    if (!isset($data['id'])) { echo json_encode(['success' => false, 'message' => 'ID manquant']); exit; }
    try {
        echo json_encode(['success' => Utilisateur::delete($pdo, $data['id'])]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action inconnue']);
?>
