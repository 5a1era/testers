<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Attempt;

use Elros\Testing\Attempt\Attempt;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use DateTimeImmutable;
use Elros\Testing\Attempt\UserAnswer;
use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use LogicException;
use PHPUnit\Framework\TestCase;

final class AttemptTest extends TestCase
{
    public function testAttemptCanBeCreated(): void
    {
        $configuration = new TestConfiguration(30, 2, 70);

        $test = new Test(1, 'Математика', 'Базовый тест', [], $configuration);

        $startedAt = new DateTimeImmutable('2026-09-30 16:00:00');

        $attempt = new Attempt(1, $test, 100, $startedAt);

        self::assertSame(1, $attempt->getId());
        self::assertSame($test, $attempt->getTest());
        self::assertSame(100, $attempt->getUserId());
        self::assertSame($startedAt, $attempt->getStartedAt());

        self::assertNull($attempt->getFinishedAt());
        self::assertFalse($attempt->isFinished());
    }

    public function testAttemptCanBeFinished(): void
    {
        $configuration = new TestConfiguration(30, 2, 80);

        $test = new Test(1, 'Математика', 'Базовый тест', [], $configuration);

        $startedAt = new DateTimeImmutable('2026-09-30 16:00:00');

        $attempt = new Attempt(1, $test, 100, $startedAt);

        $finishedAt = new DateTimeImmutable('2026-09-30 16:20:00');

        $attempt->finish($finishedAt);

        self::assertSame($finishedAt, $attempt->getFinishedAt());
        self::assertTrue($attempt->isFinished());
    }

    public function testFinishedAttemptCannotBeFinishedAgain(): void
    {
        $configuration = new TestConfiguration(30, 2, 80);

        $test = new Test(1, 'Математика', 'Базовый тест', [], $configuration);

        $attempt = new Attempt(1, $test, 100, new DateTimeImmutable('2026-09-30 16:00:00'));

        $attempt->finish(new DateTimeImmutable('2026-09-30 16:20:00'));

        $this->expectException(\LogicException::class);

        $attempt->finish(new DateTimeImmutable('2026-09-30 16:25:00'));
    }

    public function testAnswerCanBeAddedToAttempt(): void
    {
        $answer1 = new AnswerOption(1, '3', false);

        $answer2 = new AnswerOption(2, '4', true);

        $question = new Question(1, 'Сколько будет 2 + 2?', [$answer1, $answer2]);

        $configuration = new TestConfiguration(30, 2, 80);

        $test = new Test(1, 'Математика', 'Базовый тест', [$question], $configuration);

        $attempt = new Attempt(1, $test, 100, new DateTimeImmutable('2026-09-30 16:00:00'));

        $userAnswer = new UserAnswer($question, [$answer2]);

        $attempt->answerQuestion($userAnswer);

        self::assertCount(1, $attempt->getAnswers());
        self::assertSame($userAnswer, $attempt->getAnswers()[0]);
    }

    public function testUserCanAnswerQuestion(): void
    {
        $question = new Question(
            1,
            'Сколько будет 2 + 2?',
            [
                new AnswerOption(1, '3', false),
                new AnswerOption(2, '4', true),
            ],
        );

        $test = new Test(
            1,
            'Математика',
            'Базовый тест',
            [$question],
            new TestConfiguration(30, 2, 70),
        );

        $attempt = new Attempt(1, $test, 100, new DateTimeImmutable());

        $userAnswer = new UserAnswer($question, [$question->getAnswers()[1]]);

        $attempt->answerQuestion($userAnswer);

        self::assertCount(1, $attempt->getAnswers());

        self::assertSame($userAnswer, $attempt->getAnswers()[0]);
    }

    public function testAnswerToQuestionCanBeChanged(): void
    {
        $answer1 = new AnswerOption(1, '3', false);

        $answer2 = new AnswerOption(2, '4', true);

        $question = new Question(
            1,
            'Сколько будет 2 + 2?',
            [
                $answer1,
                $answer2,
            ],
        );

        $test = new Test(
            1,
            'Математика',
            'Базовый тест',
            [$question],
            new TestConfiguration(30, 2, 70),
        );

        $attempt = new Attempt(
            1,
            $test,
            100,
            new \DateTimeImmutable(),
        );

        // Сначала пользователь выбрал "3".
        $attempt->answerQuestion(
            new UserAnswer(
                $question,
                [$answer1],
            )
        );

        // Потом изменил ответ на "4".
        $attempt->answerQuestion(
            new UserAnswer(
                $question,
                [$answer2],
            )
        );

        self::assertCount(1, $attempt->getAnswers());

        self::assertSame($answer2, $attempt->getAnswers()[0]->getAnswers()[0]);
    }

    public function testCannotAnswerFinishedAttempt(): void
    {
        $answer = new AnswerOption(1, '4', true);

        $question = new Question(1, 'Сколько будет 2 + 2?', [$answer]);

        $test = new Test(
            1,
            'Математика',
            'Базовый тест',
            [$question],
            new TestConfiguration(30, 2, 70),
        );

        $attempt = new Attempt(1, $test, 100, new DateTimeImmutable());

        $attempt->finish(new DateTimeImmutable());

        $this->expectException(LogicException::class);

        $attempt->answerQuestion(new UserAnswer($question, [$answer]));
    }

    public function testCannotAnswerQuestionFromAnotherTest(): void
    {
        $questionFromTestA = new Question(
            1,
            'Вопрос теста A',
            [
                new AnswerOption(1, 'Ответ', true),
            ],
        );

        $questionFromTestB = new Question(
            2,
            'Вопрос теста B',
            [
                new AnswerOption(2, 'Ответ', true),
            ],
        );

        $testA = new Test(
            1,
            'Тест A',
            'Описание',
            [$questionFromTestA],
            new TestConfiguration(30, 2, 70),
        );

        $testB = new Test(
            2,
            'Тест B',
            'Описание',
            [$questionFromTestB],
            new TestConfiguration(30, 2, 70),
        );

        $attempt = new Attempt(
            1,
            $testA,
            100,
            new DateTimeImmutable(),
        );

        $this->expectException(LogicException::class);

        $attempt->answerQuestion(
            new UserAnswer(
                $questionFromTestB,
                [$questionFromTestB->getAnswers()[0]],
            )
        );
    }
}
