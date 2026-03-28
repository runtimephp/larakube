<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ApiErrorCode;

final readonly class ApiErrorData
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        public string $message,
        public ApiErrorCode $code,
        public array $errors = [],
    ) {}

    /**
     * @param  array{message: string, code: string, errors?: array<string, list<string>>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            code: ApiErrorCode::from($data['code']),
            errors: $data['errors'] ?? [],
        );
    }

    /**
     * @return array{message: string, code: string, errors: array<string, list<string>>}
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code' => $this->code->value,
            'errors' => $this->errors,
        ];
    }
}
