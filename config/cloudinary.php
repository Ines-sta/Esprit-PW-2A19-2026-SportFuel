<?php
// ============================================
// SportFuel — Helper Cloudinary (upload signé via cURL, sans Composer)
// ============================================
//
// IMPORTANT (sécurité) :
// - Ce fichier contient l'API_SECRET. Ne JAMAIS exposer ce secret côté navigateur.
// - Recommandé : ajouter `config/cloudinary.php` à .gitignore avant push public,
//   et faire tourner les credentials sur le dashboard Cloudinary si déjà partagés.

const CLOUDINARY_CLOUD_NAME = 'dpenelv7d';
const CLOUDINARY_API_KEY    = '695693717387431';
const CLOUDINARY_API_SECRET = 'rtMpnBHwcWy00lMYTh7SXe8e150';
const CLOUDINARY_FOLDER     = 'sportfuel';

// Limites validation (côté serveur)
const CLOUDINARY_MAX_BYTES  = 5 * 1024 * 1024; // 5 Mo
const CLOUDINARY_ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

function cloudinary_ini_bytes($value) {
    $value = trim((string)$value);
    if ($value === '') return 0;
    $unit = strtolower(substr($value, -1));
    $num = (float)$value;
    switch ($unit) {
        case 'g': return (int)($num * 1024 * 1024 * 1024);
        case 'm': return (int)($num * 1024 * 1024);
        case 'k': return (int)($num * 1024);
        default: return (int)$num;
    }
}

function cloudinary_effective_upload_limit() {
    $uploadMax = cloudinary_ini_bytes(ini_get('upload_max_filesize'));
    $postMax = cloudinary_ini_bytes(ini_get('post_max_size'));

    // 0 can mean "unlimited" in php.ini for some directives.
    $limits = [CLOUDINARY_MAX_BYTES];
    if ($uploadMax > 0) $limits[] = $uploadMax;
    if ($postMax > 0) $limits[] = $postMax;

    return min($limits);
}

function cloudinary_upload_error_message($errorCode) {
    $maxBytes = cloudinary_effective_upload_limit();
    $maxMo = max(1, (int)round($maxBytes / (1024 * 1024)));

    switch ((int)$errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "Image trop volumineuse (max environ {$maxMo} Mo selon configuration serveur).";
        case UPLOAD_ERR_PARTIAL:
            return 'Téléversement interrompu. Réessayez.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Configuration serveur invalide: dossier temporaire manquant.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Impossible d\'écrire le fichier temporaire sur le serveur.';
        case UPLOAD_ERR_EXTENSION:
            return 'Le téléversement a été bloqué par une extension PHP.';
        default:
            return "Erreur lors du téléversement (code {$errorCode}).";
    }
}

/**
 * Upload signé d'un fichier vers Cloudinary.
 * @param string $tmpFilePath Chemin local du fichier (ex: $_FILES['image']['tmp_name'])
 * @param string $folder      Sous-dossier Cloudinary (ex: 'sportfuel/aliments')
 * @param string &$debug      Message d'erreur détaillé (out)
 * @return array|null         Réponse JSON décodée (incl. 'secure_url', 'public_id') ou null en cas d'erreur
 */
function cloudinary_upload($tmpFilePath, $folder = CLOUDINARY_FOLDER, &$debug = '') {
    if (!is_file($tmpFilePath)) { $debug = 'Fichier temporaire introuvable.'; return null; }
    if (!function_exists('curl_init')) { $debug = 'Extension cURL PHP non disponible.'; return null; }

    $timestamp = time();

    // Signature : SHA1 de "folder=...&timestamp=..." + API_SECRET
    // Les paramètres signés doivent être triés alphabétiquement.
    $toSign = "folder={$folder}&timestamp={$timestamp}";
    $signature = sha1($toSign . CLOUDINARY_API_SECRET);

    $endpoint = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD_NAME . '/image/upload';

    $postFields = [
        'file'      => new CURLFile($tmpFilePath),
        'api_key'   => CLOUDINARY_API_KEY,
        'timestamp' => $timestamp,
        'folder'    => $folder,
        'signature' => $signature,
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    $curlNo   = curl_errno($ch);
    curl_close($ch);

    // Fallback : si erreur SSL côté WAMP (pas de CA bundle configuré dans php.ini),
    // on retente une seule fois sans vérification du certificat. Suffisant pour dev local.
    if ($response === false && in_array($curlNo, [60, 77], true)) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        $curlNo   = curl_errno($ch);
        curl_close($ch);
    }

    if ($response === false) {
        $debug = "cURL erreur #{$curlNo} : {$curlErr}";
        return null;
    }

    $data = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        $apiMsg = is_array($data) && isset($data['error']['message']) ? $data['error']['message'] : substr((string)$response, 0, 300);
        $debug = "HTTP {$httpCode} : {$apiMsg}";
        return null;
    }

    if (!is_array($data) || empty($data['secure_url'])) {
        $debug = 'Réponse Cloudinary invalide : ' . substr((string)$response, 0, 300);
        return null;
    }

    return $data;
}

/**
 * Gère un champ $_FILES (validation + upload) et renvoie l'URL Cloudinary, ou null si vide / invalide.
 * @param array  $fileField $_FILES['image'] (peut être absent / vide → null retourné sans erreur)
 * @param string $folder    Sous-dossier Cloudinary
 * @param string &$error    Message d'erreur de validation (out)
 * @return string|null      secure_url ou null
 */
function cloudinary_handle_upload($fileField, $folder = CLOUDINARY_FOLDER, &$error = '') {
    // Aucun fichier envoyé → comportement normal (pas d'erreur)
    if (!is_array($fileField) || ($fileField['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($fileField['error'] !== UPLOAD_ERR_OK) {
        $error = cloudinary_upload_error_message($fileField['error']);
        return null;
    }

    $maxBytes = cloudinary_effective_upload_limit();
    if (($fileField['size'] ?? 0) > $maxBytes) {
        $maxMo = max(1, (int)round($maxBytes / (1024 * 1024)));
        $error = "Image trop volumineuse (max {$maxMo} Mo).";
        return null;
    }

    // Détection MIME réelle
    $tmp = $fileField['tmp_name'] ?? '';
    // Certains environnements retournent false sur is_uploaded_file malgré un tmp valide.
    if (!is_uploaded_file($tmp) && !is_file($tmp)) {
        $error = 'Fichier invalide.';
        return null;
    }
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
    $mime  = $finfo ? finfo_file($finfo, $tmp) : ($fileField['type'] ?? '');
    if ($finfo) finfo_close($finfo);

    if (!in_array($mime, CLOUDINARY_ALLOWED_MIMES, true)) {
        $error = 'Format non supporté (JPEG, PNG, WebP, GIF uniquement).';
        return null;
    }

    $debug = '';
    $resp = cloudinary_upload($tmp, $folder, $debug);
    if ($resp === null) {
        $error = "Échec de l'upload Cloudinary." . ($debug !== '' ? ' (' . $debug . ')' : '');
        return null;
    }
    return $resp['secure_url'];
}

/**
 * Insère une transformation Cloudinary (ex: c_fill,w_300,h_300) dans une URL existante.
 * Inoffensif si l'URL n'est pas Cloudinary : renvoie l'URL d'origine.
 */
function cloudinary_thumb($url, $width = 300, $height = 300) {
    if (!$url || strpos($url, 'res.cloudinary.com') === false) return $url;
    $tx = "c_fill,w_{$width},h_{$height},q_auto,f_auto";
    return preg_replace('#/upload/#', "/upload/{$tx}/", $url, 1);
}
