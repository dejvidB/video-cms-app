<?php

declare(strict_types=1);

namespace App\Models;

interface Model
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
