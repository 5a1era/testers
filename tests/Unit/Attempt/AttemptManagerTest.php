<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Attempt;

use Elros\Testing\Attempt\Attempt;
use Elros\Testing\Attempt\AttemptManager;
use Elros\Testing\Question\Question;
use Elros\Testing\Question\QuestionSelector;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class AttemptManagerTest extends TestCase
{
    public function testCreatesFirstAttempt(): void
    {
        $test = $this->createTest(
            questionsCount: 3,
            attemptsLimit: 2,
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $attempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [],
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
        );

        self::assertInstanceOf(Attempt::class, $attempt);
        self::assertSame(1, $attempt->getId());
        self::assertSame(10, $attempt->getUserId());
        self::assertSame($test, $attempt->getTest());
        self::assertCount(3, $attempt->getQuestions());
        self::assertSame(
            '2026-10-02 10:00:00',
            $attempt->getStartedAt()->format('Y-m-d H:i:s'),
        );
    }

    public function testSelectedQuestionsBelongToTest(): void
    {
        $questions = $this->createQuestions(5);

        $test = $this->createTest(
            questionsCount: 3,
            attemptsLimit: 2,
            questions: $questions,
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $attempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [],
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
        );

        foreach ($attempt->getQuestions() as $question) {
            self::assertContains($question, $questions);
        }
    }

    public function testReturnsActiveAttemptInsteadOfCreatingNewOne(): void
    {
        $test = $this->createTest(
            questionsCount: 3,
            attemptsLimit: 2,
        );

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $activeAttempt = new Attempt(
            id: 1,
            test: $test,
            userId: 10,
            startedAt: $startedAt,
            questions: $test->getQuestions(),
        );

        $result = $manager->start(
            test: $test,
            userId: 10,
            attempts: [$activeAttempt],
            startedAt: new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        self::assertSame($activeAttempt, $result);
    }

    public function testExpiredAttemptIsFinishedAndNewAttemptIsCreated(): void
    {
        $test = $this->createTest(
            questionsCount: 3,
            attemptsLimit: 2,
            timeLimit: 60,
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $oldAttempt = new Attempt(
            id: 1,
            test: $test,
            userId: 10,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: $test->getQuestions(),
        );

        $newAttempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [$oldAttempt],
            startedAt: new DateTimeImmutable('2026-10-02 10:01:00'),
        );

        self::assertTrue($oldAttempt->isFinished());
        self::assertNotSame($oldAttempt, $newAttempt);
        self::assertSame(2, $newAttempt->getId());
    }

    public function testAttemptsOfAnotherUserDoNotCount(): void
    {
        $test = $this->createTest(
            questionsCount: 3,
            attemptsLimit: 1,
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $otherUserAttempt = new Attempt(
            id: 1,
            test: $test,
            userId: 20,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: $test->getQuestions(),
        );

        $attempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [$otherUserAttempt],
            startedAt: new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        self::assertSame(2, $attempt->getId());
        self::assertSame(10, $attempt->getUserId());
    }

    public function testAttemptsOfAnotherTestDoNotCount(): void
    {
        $test = $this->createTest(
            id: 1,
            questionsCount: 3,
            attemptsLimit: 1,
        );

        $otherTest = $this->createTest(
            id: 2,
            questionsCount: 3,
            attemptsLimit: 1,
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $otherTestAttempt = new Attempt(
            id: 1,
            test: $otherTest,
            userId: 10,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: $otherTest->getQuestions(),
        );

        $attempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [$otherTestAttempt],
            startedAt: new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        self::assertSame(2, $attempt->getId());
        self::assertSame($test, $attempt->getTest());
    }

    public function testThrowsWhenAttemptsLimitIsReached(): void
    {
        $test = $this->createTest(
            questionsCount: 3,
            attemptsLimit: 2,
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $attempt1 = new Attempt(
            id: 1,
            test: $test,
            userId: 10,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: $test->getQuestions(),
            finishedAt: new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $attempt2 = new Attempt(
            id: 2,
            test: $test,
            userId: 10,
            startedAt: new DateTimeImmutable('2026-10-02 11:00:00'),
            questions: $test->getQuestions(),
            finishedAt: new DateTimeImmutable('2026-10-02 11:10:00'),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('Attempts limit has been reached.');

        $manager->start(
            test: $test,
            userId: 10,
            attempts: [$attempt1, $attempt2],
            startedAt: new DateTimeImmutable('2026-10-02 12:00:00'),
        );
    }

    public function testExpiredAttemptCountsTowardsAttemptsLimit(): void
    {
        $test = $this->createTest(
            questionsCount: 3,
            attemptsLimit: 1,
            timeLimit: 60,
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 10,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: $test->getQuestions(),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('Attempts limit has been reached.');

        $manager->start(
            test: $test,
            userId: 10,
            attempts: [$attempt],
            startedAt: new DateTimeImmutable('2026-10-02 10:01:00'),
        );

        self::assertTrue($attempt->isFinished());
    }

    /**
     * @param Question[]|null $questions
     */
    private function createTest(
        int $id = 1,
        int $questionsCount = 3,
        int $attemptsLimit = 2,
        int $timeLimit = 3600,
        ?array $questions = null,
    ): Test {
        return new Test(
            id: $id,
            title: 'Test',
            description: 'Description',
            questions: $questions ?? $this->createQuestions(5),
            configuration: new TestConfiguration(
                timeLimit: $timeLimit,
                attemptsLimit: $attemptsLimit,
                passingScore: 70,
                questionsCount: $questionsCount,
            ),
        );
    }

    public function testAttemptExpiresExactlyAtStartTimeOfNewAttempt(): void
    {
        $test = $this->createTest(
            questionsCount: 3,
            attemptsLimit: 2,
            timeLimit: 60,
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $oldAttempt = new Attempt(
            id: 1,
            test: $test,
            userId: 10,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: $test->getQuestions(),
        );

        $newAttempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [$oldAttempt],
            startedAt: new DateTimeImmutable('2026-10-02 10:01:00'),
        );

        self::assertTrue($oldAttempt->isFinished());
        self::assertNotSame($oldAttempt, $newAttempt);
        self::assertSame(2, $newAttempt->getId());
        self::assertEquals(
            new DateTimeImmutable('2026-10-02 10:01:00'),
            $oldAttempt->getFinishedAt(),
        );
    }

    /**
     * @return Question[]
     */
    private function createQuestions(int $count): array
    {
        $questions = [];

        for ($i = 1; $i <= $count; $i++) {
            $questions[] = new Question(
                id: $i,
                text: 'Question ' . $i,
                answers: [],
            );
        }

        return $questions;
    }
}
