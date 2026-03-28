<?php

declare(strict_types=1);

use App\Data\SyncSummaryData;

test('constructor sets properties', function (): void {
    $data = new SyncSummaryData(created: 3, updated: 1, deleted: 2);

    expect($data)
        ->created->toBe(3)
        ->updated->toBe(1)
        ->deleted->toBe(2);
});

test('fromArray and toArray round-trip', function (): void {
    $original = ['created' => 3, 'updated' => 1, 'deleted' => 2];

    $data = SyncSummaryData::fromArray($original);

    expect($data->toArray())->toBe($original);
});
