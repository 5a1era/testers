<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

use Elros\Testing\Test\Test;
use DateTimeImmutable;
use LogicException;

final class AttemptManager
{
    /**
     * @param Attempt[] $attempts
     */
    public function start(
        Test $test,
        int $userId,
        array $attempts,
        DateTimeImmutable $startedAt,
    ): Attempt {
        $userAttemptsCount = 0;

        foreach ($attempts as $attempt) {
            if (
                $attempt->getTest()->getId() === $test->getId()
                && $attempt->getUserId() === $userId
            ) {
                $userAttemptsCount++;
            }
        }

        if (
            $userAttemptsCount
            >= $test->getConfiguration()->getAttemptsLimit()
        ) {
            throw new LogicException(
                'Attempts limit has been reached.'
            );
        }

        return new Attempt(
            id: count($attempts) + 1,
            test: $test,
            userId: $userId,
            startedAt: $startedAt,
        );
    }
}
