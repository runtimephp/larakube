<?php

declare(strict_types=1);

$sessionPath = getenv('LARAKUBE_SESSION_PATH') ?: ($_ENV['LARAKUBE_SESSION_PATH'] ?? null);

if (! $sessionPath) {
    $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/root';
    $sessionPath = $home.'/.larakube/session.json';
}

return [
    'session_path' => $sessionPath,
];
