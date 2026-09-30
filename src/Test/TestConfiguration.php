<?php

declare(strict_types=1);

namespace Elros\Testing\Test;

use InvalidArgumentException;

final class TestConfiguration
{
    public function __construct(
        private readonly int $timeLimit,
        private readonly int $attemptsLimit,
        private readonly int $passingScore,
    ) {
        if ($this->timeLimit <= 0) {
            throw new InvalidArgumentException(
                'Time limit must be greater than zero.'
            );
        }

        if ($this->attemptsLimit <= 0) {
            throw new InvalidArgumentException(
                'Attempts limit must be greater than zero.'
            );
        }

        if ($this->passingScore < 0 || $this->passingScore > 100) {
            throw new InvalidArgumentException(
                'Passing score must be between 0 and 100.'
            );
        }
    }

    public function getTimeLimit(): int
    {
        return $this->timeLimit;
    }

    public function getAttemptsLimit(): int
    {
        return $this->attemptsLimit;
    }

    public function getPassingScore(): int
    {
        return $this->passingScore;
    }
}
