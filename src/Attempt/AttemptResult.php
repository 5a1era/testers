<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

final class AttemptResult
{
    public function __construct(
        private readonly int $totalQuestions,
        private readonly int $answeredQuestions,
        private readonly int $correctAnswers,
        private readonly int $score,
        private readonly bool $passed,
    ) {}

    public function getTotalQuestions(): int
    {
        return $this->totalQuestions;
    }

    public function getAnsweredQuestions(): int
    {
        return $this->answeredQuestions;
    }

    public function getCorrectAnswers(): int
    {
        return $this->correctAnswers;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function isPassed(): bool
    {
        return $this->passed;
    }
}
