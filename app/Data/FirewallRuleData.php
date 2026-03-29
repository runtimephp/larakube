<?php

declare(strict_types=1);

namespace App\Data;

use InvalidArgumentException;

final readonly class FirewallRuleData
{
    /**
     * @param  list<string>  $sourceIps
     * @param  list<string>  $destinationIps
     */
    public function __construct(
        public string $direction,
        public string $protocol,
        public ?int $portStart,
        public ?int $portEnd,
        public array $sourceIps = [],
        public array $destinationIps = [],
    ) {}

    /**
     * @param  list<string>  $sourceIps
     * @param  list<string>  $destinationIps
     *
     * @throws InvalidArgumentException
     */
    public static function fromPortString(
        string $direction,
        string $protocol,
        ?string $port,
        array $sourceIps = [],
        array $destinationIps = [],
    ): self {
        if ($port === null || $port === '') {
            return new self($direction, $protocol, null, null, $sourceIps, $destinationIps);
        }

        if (str_contains($port, '-')) {
            [$startStr, $endStr] = explode('-', $port, 2);
            $start = self::validatePort($startStr);
            $end = self::validatePort($endStr);

            if ($start > $end) {
                throw new InvalidArgumentException("Port range start ({$start}) must not exceed end ({$end}).");
            }

            return new self($direction, $protocol, $start, $end, $sourceIps, $destinationIps);
        }

        $port = self::validatePort($port);

        return new self($direction, $protocol, $port, $port, $sourceIps, $destinationIps);
    }

    public function toPortString(): ?string
    {
        if ($this->portStart === null || $this->portEnd === null) {
            return null;
        }

        if ($this->portStart === $this->portEnd) {
            return (string) $this->portStart;
        }

        return $this->portStart.'-'.$this->portEnd;
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function validatePort(string $value): int
    {
        if (! ctype_digit($value)) {
            throw new InvalidArgumentException("Port must be numeric, got '{$value}'.");
        }

        $port = (int) $value;

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException("Port must be between 1 and 65535, got {$port}.");
        }

        return $port;
    }
}
