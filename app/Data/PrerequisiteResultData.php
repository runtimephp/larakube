<?php

declare(strict_types=1);

namespace App\Data;

final readonly class PrerequisiteResultData
{
    /**
     * @param  list<string>  $missing
     */
    public function __construct(
        public bool $ok,
        public array $missing = [],
    ) {}
}
