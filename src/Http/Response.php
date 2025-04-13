<?php

declare(strict_types=1);

namespace App\Http;

use App\Models\Model;

class Response
{
    /**
     * @param mixed|null $data
     * @param int $status
     * @param array<string, string> $headers
     */
    public function __construct(
        private mixed $data = null,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'application/json']
    ) {
        $this->data = $this->transform($data);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function transform(mixed $data): mixed
    {
        if ($data instanceof Model) {
            return $data->toArray();
        }

        foreach ($data as $key => $value) {
            if ($value instanceof Model) {
                $data[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $data[$key] = $this->transform($value);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        echo json_encode($this->data);

        exit();
    }
}
