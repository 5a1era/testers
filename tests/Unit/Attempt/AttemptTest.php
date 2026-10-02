<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Attempt;

use Elros\Testing\Attempt\Attempt;
use Elros\Testing\Attempt\UserAnswer;
use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class AttemptTest extends TestCase
{
    public function testAttemptCanBeCreated(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest([$question]);

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
            questions: [$question],
        );

        self::assertSame(1, $attempt->getId());
        self::assertSame($test, $attempt->getTest());
        self::assertSame(100, $attempt->getUserId());
        self::assertSame($startedAt, $attempt->getStartedAt());
        self::assertSame([$question], $attempt->getQuestions());
        self::assertNull($attempt->getFinishedAt());
        self::assertFalse($attempt->isFinished());
        self::assertSame([], $attempt->getAnswers());
    }

    public function testAttemptCanBeFinished(): void
    {
        $question = $this->createQuestion(1);
        $test = $this->createTest([$question]);

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');
        $finishedAt = new DateTimeImmutable('2026-10-02 10:10:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
            questions: [$question],
        );

        $attempt->finish($finishedAt);

        self::assertTrue($attempt->isFinished());
        self::assertSame($finishedAt, $attempt->getFinishedAt());
    }

    public function testFinishedAttemptCannotBeFinishedAgain(): void
    {
        $question = $this->createQuestion(1);
        $test = $this->createTest([$question]);

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');
        $finishedAt = new DateTimeImmutable('2026-10-02 10:10:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
            questions: [$question],
        );

        $attempt->finish($finishedAt);

        $this->expectException(LogicException::class);

        $attempt->finish(
            new DateTimeImmutable('2026-10-02 10:20:00'),
        );
    }

    public function testAttemptIsNotExpiredBeforeTimeLimit(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest(
            questions: [$question],
            timeLimit: 1800,
        );

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
            questions: [$question],
        );

        $now = new DateTimeImmutable('2026-10-02 10:29:59');

        self::assertFalse($attempt->isExpired($now));
    }

    public function testAttemptIsExpiredAtTimeLimit(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest(
            questions: [$question],
            timeLimit: 1800,
        );

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
            questions: [$question],
        );

        $now = new DateTimeImmutable('2026-10-02 10:30:00');

        self::assertTrue($attempt->isExpired($now));
    }

    public function testFinishIfExpiredFinishesExpiredAttempt(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest(
            questions: [$question],
            timeLimit: 1800,
        );

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
            questions: [$question],
        );

        $now = new DateTimeImmutable('2026-10-02 10:30:00');

        self::assertTrue(
            $attempt->finishIfExpired($now)
        );

        self::assertTrue($attempt->isFinished());

        self::assertEquals(
            new DateTimeImmutable('2026-10-02 10:30:00'),
            $attempt->getFinishedAt(),
        );
    }

    public function testFinishIfExpiredDoesNothingBeforeExpiration(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest(
            questions: [$question],
            timeLimit: 1800,
        );

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
            questions: [$question],
        );

        $now = new DateTimeImmutable('2026-10-02 10:29:59');

        self::assertFalse(
            $attempt->finishIfExpired($now)
        );

        self::assertFalse($attempt->isFinished());
        self::assertNull($attempt->getFinishedAt());
    }

    public function testFinishIfExpiredDoesNothingForAlreadyFinishedAttempt(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest([$question]);

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');
        $finishedAt = new DateTimeImmutable('2026-10-02 10:10:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
            questions: [$question],
            finishedAt: $finishedAt,
        );

        self::assertFalse(
            $attempt->finishIfExpired(
                new DateTimeImmutable('2026-10-02 11:00:00'),
            )
        );

        self::assertSame(
            $finishedAt,
            $attempt->getFinishedAt(),
        );
    }

    public function testCanAnswerQuestion(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest([$question]);

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: [$question],
        );

        $userAnswer = new UserAnswer(
            question: $question,
            answers: [$question->getAnswers()[1]],
        );

        $attempt->answerQuestion(
            userAnswer: $userAnswer,
            now: new DateTimeImmutable('2026-10-02 10:05:00'),
        );

        self::assertCount(1, $attempt->getAnswers());
        self::assertSame($userAnswer, $attempt->getAnswers()[0]);
    }

    public function testAnswerToSameQuestionReplacesPreviousAnswer(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest([$question]);

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: [$question],
        );

        $firstAnswer = new UserAnswer(
            question: $question,
            answers: [$question->getAnswers()[0]],
        );

        $secondAnswer = new UserAnswer(
            question: $question,
            answers: [$question->getAnswers()[1]],
        );

        $attempt->answerQuestion(
            userAnswer: $firstAnswer,
            now: new DateTimeImmutable('2026-10-02 10:05:00'),
        );

        $attempt->answerQuestion(
            userAnswer: $secondAnswer,
            now: new DateTimeImmutable('2026-10-02 10:06:00'),
        );

        self::assertCount(1, $attempt->getAnswers());
        self::assertSame($secondAnswer, $attempt->getAnswers()[0]);
    }

    public function testCannotAnswerQuestionThatDoesNotBelongToAttempt(): void
    {
        $question = $this->createQuestion(1);
        $anotherQuestion = $this->createQuestion(2);

        $test = $this->createTest([$question]);

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: [$question],
        );

        $userAnswer = new UserAnswer(
            question: $anotherQuestion,
            answers: [$anotherQuestion->getAnswers()[0]],
        );

        $this->expectException(LogicException::class);

        $attempt->answerQuestion(
            userAnswer: $userAnswer,
            now: new DateTimeImmutable('2026-10-02 10:05:00'),
        );
    }

    public function testCannotAnswerFinishedAttempt(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest([$question]);

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: [$question],
        );

        $attempt->finish(
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $userAnswer = new UserAnswer(
            question: $question,
            answers: [$question->getAnswers()[0]],
        );

        $this->expectException(LogicException::class);

        $attempt->answerQuestion(
            userAnswer: $userAnswer,
            now: new DateTimeImmutable('2026-10-02 10:05:00'),
        );
    }

    public function testCannotAnswerExpiredAttempt(): void
    {
        $question = $this->createQuestion(1);

        $test = $this->createTest(
            questions: [$question],
            timeLimit: 1800,
        );

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: [$question],
        );

        $userAnswer = new UserAnswer(
            question: $question,
            answers: [$question->getAnswers()[0]],
        );

        $this->expectException(LogicException::class);

        $attempt->answerQuestion(
            userAnswer: $userAnswer,
            now: new DateTimeImmutable('2026-10-02 10:30:00'),
        );
    }

    /**
     * @param Question[] $questions
     */
    private function createTest(
        array $questions,
        int $timeLimit = 1800,
    ): Test {
        return new Test(
            id: 1,
            title: 'Test',
            description: 'Test description',
            questions: $questions,
            configuration: new TestConfiguration(
                timeLimit: $timeLimit,
                attemptsLimit: 2,
                passingScore: 70,
                questionsCount: count($questions),
            ),
        );
    }

    private function createQuestion(int $id): Question
    {
        return new Question(
            id: $id,
            text: 'Question ' . $id,
            answers: [
                new AnswerOption(
                    id: $id * 10 + 1,
                    text: 'Wrong answer',
                    correct: false,
                ),
                new AnswerOption(
                    id: $id * 10 + 2,
                    text: 'Correct answer',
                    correct: true,
                ),
            ],
        );
    }
}
