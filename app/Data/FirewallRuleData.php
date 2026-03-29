<?php

declare(strict_types=1);

namespace App\Data;

final readonly class FirewallRuleData
{
    /**
     * @param  list<string>  $sourceIps
     * @param  list<string>  $destinationIps
     */
    public function __construct(
        public string $direction,
        public string $protocol,
        public int $portStart,
        public int $portEnd,
        public array $sourceIps = [],
        public array $destinationIps = [],
    ) {}

    /**
     * @param  list<string>  $sourceIps
     * @param  list<string>  $destinationIps
     */
    public static function fromPortString(
        string $direction,
        string $protocol,
        string $port,
        array $sourceIps = [],
        array $destinationIps = [],
    ): self {
        if (str_contains($port, '-')) {
            [$start, $end] = explode('-', $port, 2);

            return new self($direction, $protocol, (int) $start, (int) $end, $sourceIps, $destinationIps);
        }

        return new self($direction, $protocol, (int) $port, (int) $port, $sourceIps, $destinationIps);
    }

    public function toPortString(): string
    {
        if ($this->portStart === $this->portEnd) {
            return (string) $this->portStart;
        }

        return $this->portStart.'-'.$this->portEnd;
    }
}
