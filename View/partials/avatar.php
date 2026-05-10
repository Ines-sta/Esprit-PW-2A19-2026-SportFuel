<?php
if (!function_exists('sportfuel_avatar_initials')) {
    function sportfuel_avatar_initials($name) {
        $base = trim((string)$name);
        if ($base === '') {
            return 'SF';
        }
        $parts = preg_split('/\s+/u', $base);
        $initials = '';
        foreach ($parts as $part) {
            if ($part === '') continue;
            $char = mb_substr($part, 0, 1, 'UTF-8');
            $initials .= mb_strtoupper($char, 'UTF-8');
            if (mb_strlen($initials, 'UTF-8') >= 2) {
                break;
            }
        }
        return $initials !== '' ? $initials : 'SF';
    }
}

if (!function_exists('sportfuel_avatar_markup')) {
    function sportfuel_avatar_markup($name, $url, $className = '') {
        $safeClass = trim((string)$className);
        $safeName = htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8');
        $safeUrl = trim((string)$url);
        if ($safeUrl !== '') {
            $encodedUrl = htmlspecialchars($safeUrl, ENT_QUOTES, 'UTF-8');
            return '<span class="sf-avatar ' . htmlspecialchars($safeClass, ENT_QUOTES, 'UTF-8') . '"><img src="' . $encodedUrl . '" alt="' . $safeName . '"></span>';
        }

        $initials = htmlspecialchars(sportfuel_avatar_initials($name), ENT_QUOTES, 'UTF-8');
        return '<span class="sf-avatar sf-avatar-fallback ' . htmlspecialchars($safeClass, ENT_QUOTES, 'UTF-8') . '">' . $initials . '</span>';
    }
}
