<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SyncSummaryData
{
    public function __construct(
        public int $created,
        public int $updated,
        public int $deleted,
    ) {}

    /**
     * @param  array{created: int, updated: int, deleted: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            created: $data['created'],
            updated: $data['updated'],
            deleted: $data['deleted'],
        );
    }

    /**
     * @return array{created: int, updated: int, deleted: int}
     */
    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'deleted' => $this->deleted,
        ];
    }
}
