<?php

declare(strict_types=1);

$sessionsPath = getenv('LARAKUBE_SESSIONS_PATH') ?: ($_ENV['LARAKUBE_SESSIONS_PATH'] ?? null);

if (! $sessionsPath) {
    $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/root';
    $sessionsPath = $home.'/.larakube/sessions';
}

return [
    'api_url' => env('LARAKUBE_API_URL', 'http://localhost:8000'),
    'sessions_path' => $sessionsPath,
];
