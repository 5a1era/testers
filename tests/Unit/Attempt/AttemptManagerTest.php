<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Attempt;

use Elros\Testing\Attempt\Attempt;
use Elros\Testing\Attempt\AttemptManager;
use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class AttemptManagerTest extends TestCase
{
    public function testFirstAttemptCanBeStarted(): void
    {
        $test = $this->createTest(attemptsLimit: 2);

        $manager = new AttemptManager();

        $attempt = $manager->start(
            test: $test,
            userId: 100,
            attempts: [],
            startedAt: new DateTimeImmutable('2026-10-01 10:00:00'),
        );

        self::assertSame(1, $attempt->getId());
        self::assertSame(100, $attempt->getUserId());
        self::assertSame($test, $attempt->getTest());
        self::assertSame(
            '2026-10-01 10:00:00',
            $attempt->getStartedAt()->format('Y-m-d H:i:s')
        );
    }

    public function testSecondAttemptCanBeStartedWhenLimitIsTwo(): void
    {
        $test = $this->createTest(attemptsLimit: 2);

        $firstAttempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-01 10:00:00'),
        );

        $manager = new AttemptManager();

        $secondAttempt = $manager->start(
            test: $test,
            userId: 100,
            attempts: [$firstAttempt],
            startedAt: new DateTimeImmutable('2026-10-01 11:00:00'),
        );

        self::assertSame(2, $secondAttempt->getId());
    }

    public function testThirdAttemptCannotBeStartedWhenLimitIsTwo(): void
    {
        $test = $this->createTest(attemptsLimit: 2);

        $firstAttempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-01 10:00:00'),
        );

        $secondAttempt = new Attempt(
            id: 2,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-01 11:00:00'),
        );

        $manager = new AttemptManager();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains(
            'Attempts limit has been reached.'
        );

        $manager->start(
            test: $test,
            userId: 100,
            attempts: [
                $firstAttempt,
                $secondAttempt,
            ],
            startedAt: new DateTimeImmutable('2026-10-01 12:00:00'),
        );
    }

    public function testAttemptsOfAnotherUserDoNotCount(): void
    {
        $test = $this->createTest(attemptsLimit: 2);

        $firstAttempt = new Attempt(
            id: 1,
            test: $test,
            userId: 200,
            startedAt: new DateTimeImmutable('2026-10-01 10:00:00'),
        );

        $secondAttempt = new Attempt(
            id: 2,
            test: $test,
            userId: 200,
            startedAt: new DateTimeImmutable('2026-10-01 11:00:00'),
        );

        $manager = new AttemptManager();

        $attempt = $manager->start(
            test: $test,
            userId: 100,
            attempts: [
                $firstAttempt,
                $secondAttempt,
            ],
            startedAt: new DateTimeImmutable('2026-10-01 12:00:00'),
        );

        self::assertSame(3, $attempt->getId());
        self::assertSame(100, $attempt->getUserId());
    }

    public function testAttemptsForAnotherTestDoNotCount(): void
    {
        $test = $this->createTest(
            id: 1,
            attemptsLimit: 2,
        );

        $anotherTest = $this->createTest(
            id: 2,
            attemptsLimit: 2,
        );

        $firstAttempt = new Attempt(
            id: 1,
            test: $anotherTest,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-01 10:00:00'),
        );

        $secondAttempt = new Attempt(
            id: 2,
            test: $anotherTest,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-01 11:00:00'),
        );

        $manager = new AttemptManager();

        $attempt = $manager->start(
            test: $test,
            userId: 100,
            attempts: [
                $firstAttempt,
                $secondAttempt,
            ],
            startedAt: new DateTimeImmutable('2026-10-01 12:00:00'),
        );

        self::assertSame(3, $attempt->getId());
    }

    /**
     * @param int $id
     * @param int $attemptsLimit
     */
    private function createTest(
        int $id = 1,
        int $attemptsLimit = 2,
    ): Test {
        $question = new Question(
            id: 1,
            text: '2 + 2 = ?',
            answers: [
                new AnswerOption(
                    id: 1,
                    text: '4',
                    correct: true,
                ),
            ],
        );

        return new Test(
            id: $id,
            title: 'Test',
            description: 'Test description',
            questions: [$question],
            configuration: new TestConfiguration(
                timeLimit: 1800,
                attemptsLimit: $attemptsLimit,
                passingScore: 70,
            ),
        );
    }
}
