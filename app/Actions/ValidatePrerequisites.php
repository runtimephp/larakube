<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\PrerequisiteChecker;
use App\Data\PrerequisiteResultData;

final readonly class ValidatePrerequisites
{
    private const REQUIRED_BINARIES = ['kind', 'clusterctl', 'kubectl'];

    public function __construct(
        private PrerequisiteChecker $checker,
    ) {}

    public function handle(): PrerequisiteResultData
    {
        $missing = [];

        foreach (self::REQUIRED_BINARIES as $binary) {
            if (! $this->checker->hasBinary($binary)) {
                $missing[] = $binary;
            }
        }

        if (! $this->checker->isDockerRunning()) {
            $missing[] = 'docker';
        }

        return new PrerequisiteResultData(
            ok: $missing === [],
            missing: $missing,
        );
    }
}
