<?php

declare(strict_types=1);

namespace Elros\Testing\Question;

final class AnswerOption
{
    public function __construct(
        private readonly int $id,
        private string $text,
        private bool $correct
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isCorrect(): bool
    {
        return $this->correct;
    }
}
