<?php

declare(strict_types=1);

namespace App\Data;

final readonly class FirewallRuleData
{
    /**
     * @param  list<string>  $sourceIps
     */
    public function __construct(
        public string $direction,
        public string $protocol,
        public int $portStart,
        public int $portEnd,
        public array $sourceIps = [],
    ) {}

    public static function fromPortString(string $direction, string $protocol, string $port, array $sourceIps = []): self
    {
        if (str_contains($port, '-')) {
            [$start, $end] = explode('-', $port, 2);

            return new self($direction, $protocol, (int) $start, (int) $end, $sourceIps);
        }

        return new self($direction, $protocol, (int) $port, (int) $port, $sourceIps);
    }

    public function toPortString(): string
    {
        if ($this->portStart === $this->portEnd) {
            return (string) $this->portStart;
        }

        return $this->portStart.'-'.$this->portEnd;
    }
}
