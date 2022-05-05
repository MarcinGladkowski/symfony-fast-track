<?php

declare(strict_types=1);

namespace App\Message;

class CommentMessage
{
    public function __construct(private int $id, private array $context = [])
    {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function context(): array
    {
        return $this->context;
    }
}
