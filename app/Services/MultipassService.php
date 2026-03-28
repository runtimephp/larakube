<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CloudProviderService;
use Closure;
use Symfony\Component\Process\Process;

final readonly class MultipassService implements CloudProviderService
{
    /** @var Closure(list<string>): Process */
    private Closure $processFactory;

    /**
     * @param  Closure(list<string>): Process|null  $processFactory
     */
    public function __construct(?Closure $processFactory = null)
    {
        $this->processFactory = $processFactory ?? fn (array $command): Process => new Process($command);
    }

    public function validateToken(): bool
    {
        $process = ($this->processFactory)(['multipass', 'version']);
        $process->run();

        return $process->isSuccessful();
    }
}
