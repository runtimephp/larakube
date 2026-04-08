<?php

declare(strict_types=1);

return [
    'hetzner' => [
        'token' => env('HCLOUD_TOKEN', ''),
    ],
    'do' => [
        'token' => env('DO_TOKEN', ''),
    ],
];
