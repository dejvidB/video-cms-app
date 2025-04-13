<?php

declare(strict_types=1);

namespace App\Http;

use InvalidArgumentException;

class Request
{
    /**
     * @var array<string, mixed>
     */
    private array $body;

    public function __construct()
    {
        $this->body = json_decode((string)file_get_contents('php://input'), true) ?? [];
    }

    /**
     * @param string[] $required
     * @return array<string, mixed>
     */
    public function validate(array $required): array
    {
        foreach ($required as $field) {
            if (!isset($this->body[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        return $this->body;
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return (new self())->setBody($data);
    }

    /**
     * @param array<string, mixed> $body
     * @return self
     */
    private function setBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}
