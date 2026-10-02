<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

use Elros\Testing\Question\QuestionSelector;
use Elros\Testing\Test\Test;
use DateTimeImmutable;
use LogicException;

final class AttemptManager
{
    public function __construct(
        private readonly QuestionSelector $questionSelector,
    ) {}

    /**
     * @param Attempt[] $attempts
     */
    public function start(
        Test $test,
        int $userId,
        array $attempts,
        DateTimeImmutable $startedAt,
    ): Attempt {
        $userAttempts = [];

        foreach ($attempts as $attempt) {
            if (
                $attempt->getTest()->getId() !== $test->getId()
                || $attempt->getUserId() !== $userId
            ) {
                continue;
            }

            $userAttempts[] = $attempt;
        }

        foreach ($userAttempts as $attempt) {
            if (!$attempt->isFinished()) {
                $attempt->finishIfExpired($startedAt);

                if (!$attempt->isFinished()) {
                    return $attempt;
                }
            }
        }

        if (
            count($userAttempts)
            >= $test->getConfiguration()->getAttemptsLimit()
        ) {
            throw new LogicException(
                'Attempts limit has been reached.'
            );
        }

        $questions = $this->questionSelector->select(
            questions: $test->getQuestions(),
            count: $test->getConfiguration()->getQuestionsCount(),
        );

        return new Attempt(
            id: count($attempts) + 1,
            test: $test,
            userId: $userId,
            startedAt: $startedAt,
            questions: $questions,
        );
    }
}
