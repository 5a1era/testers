<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

use Elros\Testing\Test\Test;
use DateTimeImmutable;
use Elros\Testing\Question\Question;
use LogicException;

final class Attempt
{

    /**
     * @var UserAnswer[]
     */
    private array $answers = [];

    public function __construct(
        private readonly int $id,
        private readonly Test $test,
        private readonly int $userId,
        private readonly DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $finishedAt = null,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTest(): Test
    {
        return $this->test;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getStartedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function isFinished(): bool
    {
        return $this->finishedAt !== null;
    }

    public function finish(DateTimeImmutable $finishedAt): void
    {
        if ($this->isFinished()) {
            throw new LogicException(
                'Attempt is already finished.'
            );
        }

        $this->finishedAt = $finishedAt;
    }

    /**
     * @return UserAnswer[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function answerQuestion(UserAnswer $userAnswer): void
    {
        if ($this->isFinished()) {
            throw new LogicException(
                'Cannot answer a finished attempt.'
            );
        }

        if (!$this->hasQuestion($userAnswer->getQuestion())) {
            throw new \LogicException(
                'Question does not belong to this test.'
            );
        }

        foreach ($this->answers as $index => $answer) {
            if (
                $answer->getQuestion()->getId()
                === $userAnswer->getQuestion()->getId()
            ) {
                $this->answers[$index] = $userAnswer;

                return;
            }
        }

        $this->answers[] = $userAnswer;
    }

    private function hasQuestion(Question $question): bool
    {
        foreach ($this->test->getQuestions() as $testQuestion) {
            if ($testQuestion->getId() === $question->getId()) {
                return true;
            }
        }

        return false;
    }
}
